<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use App\Models\Account;
use App\Models\Asset;
use App\Models\AtkTransaction;
use App\Models\Coordinator;
use App\Models\Customer;
use App\Models\Installation;
use App\Models\InventoryItem;
use App\Models\Journal;
use App\Models\JournalEntry;
use App\Models\Role;
use App\Models\TechnicianAttendance;
use App\Models\TechnicianDailySchedule;
use App\Models\TechnicianSchedule;
use App\Models\Ticket;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WashTransaction;
use App\Models\WeddingPayment;
use App\Services\SystemMetricsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            // Jika user adalah kasir wash / karyawan wash, arahkan ke dashboard wash
            if ($user->hasRole('kasir-wash') || $user->hasRole('wash-cashier') || $user->hasRole('karyawan-wash') || $user->hasRole('wash-operator')) {
                if ($user->hasPermission('wash.view')) {
                    return redirect()->route('wash.dashboard');
                }
            }
            
            // Jika user adalah kasir ATK, arahkan ke dashboard ATK
            if ($user->hasRole('kasir-atk') || $user->hasRole('atk-cashier')) {
                return redirect()->route('atk.dashboard');
            }

            // Jika role lain tapi tidak punya izin melihat dashboard utama
            if (!$user->hasPermission('dashboard.view')) {
                if ($user->hasPermission('atk.pos') || $user->hasPermission('atk.view')) {
                    return redirect()->route('atk.dashboard');
                }
                if ($user->hasPermission('wash.pos') || $user->hasPermission('wash.view')) {
                    return redirect()->route('wash.dashboard');
                }
                if ($user->hasPermission('wedding.view')) {
                    return redirect()->route('wedding.dashboard');
                }
                if ($user->hasPermission('finance.view')) {
                    return redirect()->route('finance.index');
                }
                if ($user->hasPermission('attendance.view') || $user->hasPermission('leave.view')) {
                    return redirect()->route('attendance.daily');
                }
                abort(403);
            }
        }

        // Get User's Attendance for Today
        $todayAttendance = \App\Support\ModuleGate::isEnabled('hr')
            ? TechnicianAttendance::where('user_id', $user->id)
                ->whereDate('clock_in', today())
                ->first()
            : null;

        if (\App\Support\ModuleGate::isEnabled('isp') && $user->hasRole('field-technician')) {
            $attendanceOverview = $this->buildRoleAttendanceOverview($user->role->name);
            $shiftSchedule = $this->getTodayShiftSchedule($user->id);
            $stats = [
                'assigned_tickets' => $user->tickets()->whereIn('status', ['assigned', 'in_progress'])->count(),
                'assigned_installations' => $user->installations()->whereIn('status', ['assigned', 'survey'])->count(),
                'completed_tickets_today' => $user->tickets()->where('status', 'solved')->whereDate('tickets.updated_at', today())->count(),
            ];

            $activeTickets = $user->tickets()
                ->with('customer')
                ->whereIn('status', ['assigned', 'in_progress'])
                ->orderByRaw("CASE priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 WHEN 'low' THEN 3 END")
                ->latest()
                ->take(10)
                ->get();

            $pendingInstallations = $user->installations()
                ->with('customer')
                ->whereIn('status', ['registered', 'survey', 'approved', 'installation'])
                ->orderBy('plan_date', 'asc')
                ->take(10)
                ->get();

            return view('technician.dashboard', compact(
                'stats',
                'activeTickets',
                'pendingInstallations',
                'todayAttendance',
                'attendanceOverview',
                'shiftSchedule'
            ));
        }

        // Base Queries for Dashboard Logic (Coordinator / Admin / Finance)
        $customerQuery = Customer::query();
        $ticketQuery = Ticket::query();
        $installationQuery = Installation::query();
        $currentYear = now()->year;
        $currentMonthStart = now()->copy()->startOfMonth();
        $prevMonthStart = now()->copy()->subMonthNoOverflow()->startOfMonth();
        $prevMonthEnd = now()->copy()->subMonthNoOverflow()->endOfMonth();

        // Filter Logic: Exclude HQ / Management roles from region filtering
        $bypassRegionRoles = [
            'super-admin', 'manager',
            'admin', 'direktur',
            'finance', 'hrd-manager', 'hr',
            'operations', 'leader', 'noc-operator',
            'bu-owner', 'manager',
        ];
        if (! $user->hasAnyRole($bypassRegionRoles)) {
            $coordinator = Coordinator::select('id', 'region_id')->where('user_id', $user->id)->first();
            if ($coordinator && $coordinator->region_id) {
                // Filter Customers by Region
                $customerQuery->whereHas('odp', function ($q) use ($coordinator) {
                    $q->where('region_id', $coordinator->region_id);
                });

                // Filter Tickets (Linked to Customer in Region)
                $ticketQuery->whereHas('customer.odp', function ($q) use ($coordinator) {
                    $q->where('region_id', $coordinator->region_id);
                });

                // Filter Installations (Linked to Customer in Region)
                $installationQuery->whereHas('customer.odp', function ($q) use ($coordinator) {
                    $q->where('region_id', $coordinator->region_id);
                });
            }
        }

        $technicianIds = collect(Cache::remember('dashboard.technician_ids', 300, function () {
            return User::query()
                ->where('is_active', true)
                ->whereHas('role', function ($q) {
                    $q->where('name', 'field-technician');
                })
                ->pluck('id')
                ->all();
        }));
        $washEmployeeIds = collect(Cache::remember('dashboard.wash_employee_ids', 300, function () {
            return User::query()
                ->where('is_active', true)
                ->whereHas('role', function ($q) {
                    $q->where('name', 'wash-operator');
                })
                ->pluck('id')
                ->all();
        }));
        $today = today();
        $tomorrow = today()->addDay();

        $technicianPresentToday = TechnicianAttendance::where('clock_in', '>=', $today)
            ->where('clock_in', '<', $tomorrow)
            ->whereIn('status', ['present', 'late'])
            ->whereIn('user_id', $technicianIds)
            ->distinct('user_id')
            ->count('user_id');
        $washEmployeePresentToday = TechnicianAttendance::where('clock_in', '>=', $today)
            ->where('clock_in', '<', $tomorrow)
            ->whereIn('status', ['present', 'late'])
            ->whereIn('user_id', $washEmployeeIds)
            ->distinct('user_id')
            ->count('user_id');

        $attendanceRole = (string) $request->query('attendance_role', 'field-technician');
        $attendanceState = (string) $request->query('attendance_state', 'present');
        $attendanceDate = now()->toDateString();
        $attendanceDateLabel = now()->translatedFormat('d M Y');
        $attendanceByUser = collect();
        $attendanceEmployees = collect();
        $technicianTaskSummary = collect();

        if (\App\Support\ModuleGate::isEnabled('hr')) {
            if (! in_array($attendanceRole, ['field-technician', 'wash-operator'], true)) {
                $attendanceRole = 'field-technician';
            }
            if (! in_array($attendanceState, ['present', 'not_present'], true)) {
                $attendanceState = 'present';
            }
            $attendanceDateInput = (string) $request->query('attendance_date', now()->toDateString());
            try {
                $attendanceDateObj = Carbon::createFromFormat('Y-m-d', $attendanceDateInput);
                $attendanceDateStart = $attendanceDateObj->copy()->startOfDay();
                $attendanceDateEnd = $attendanceDateObj->copy()->endOfDay();
                $attendanceDate = $attendanceDateObj->toDateString();
            } catch (\Throwable $e) {
                $attendanceDateStart = now()->startOfDay();
                $attendanceDateEnd = now()->endOfDay();
                $attendanceDate = now()->toDateString();
            }
            $attendanceDateLabel = $attendanceDateStart->translatedFormat('d M Y');
            $selectedRoleIds = $attendanceRole === 'wash-operator' ? $washEmployeeIds : $technicianIds;
            $attendanceByUser = TechnicianAttendance::query()
                ->where('clock_in', '>=', $attendanceDateStart)
                ->where('clock_in', '<=', $attendanceDateEnd)
                ->whereIn('user_id', $selectedRoleIds)
                ->orderByDesc('clock_in')
                ->get()
                ->groupBy('user_id')
                ->map(fn ($rows) => $rows->first());
            $presentStatuses = ['present', 'late'];
            $presentUserIds = $attendanceByUser
                ->filter(fn ($attendance) => in_array((string) $attendance->status, $presentStatuses, true))
                ->keys()
                ->values()
                ->all();
            $attendanceEmployeesQuery = User::query()
                ->with('role')
                ->where('is_active', true)
                ->whereHas('role', function ($q) use ($attendanceRole) {
                    $q->where('name', $attendanceRole);
                });
            if ($attendanceState === 'present') {
                if ($presentUserIds === []) {
                    $attendanceEmployees = collect();
                } else {
                    $attendanceEmployees = $attendanceEmployeesQuery
                        ->whereIn('id', $presentUserIds)
                        ->orderBy('name')
                        ->get();
                }
            } else {
                $attendanceEmployees = $attendanceEmployeesQuery
                    ->when($presentUserIds !== [], fn ($q) => $q->whereNotIn('id', $presentUserIds))
                    ->orderBy('name')
                    ->get();
            }
            if ($attendanceRole === 'field-technician' && $attendanceEmployees->isNotEmpty()) {
                $technicianIdsForTable = $attendanceEmployees->pluck('id')->values()->all();
                $activeTicketRows = DB::table('ticket_user')
                    ->join('tickets', 'tickets.id', '=', 'ticket_user.ticket_id')
                    ->whereIn('ticket_user.user_id', $technicianIdsForTable)
                    ->whereIn('tickets.status', ['open', 'assigned', 'in_progress', 'pending'])
                    ->orderByDesc('tickets.updated_at')
                    ->get(['ticket_user.user_id', 'tickets.id']);
                $activeTicketIdsByUser = $activeTicketRows
                    ->groupBy('user_id')
                    ->map(fn (Collection $rows) => (int) optional($rows->first())->id);
                $activeTicketCounts = DB::table('ticket_user')
                    ->join('tickets', 'tickets.id', '=', 'ticket_user.ticket_id')
                    ->select('ticket_user.user_id', DB::raw('COUNT(DISTINCT tickets.id) as total'))
                    ->whereIn('ticket_user.user_id', $technicianIdsForTable)
                    ->whereIn('tickets.status', ['open', 'assigned', 'in_progress', 'pending'])
                    ->groupBy('ticket_user.user_id')
                    ->pluck('total', 'ticket_user.user_id');
                $activeInstallationCounts = Installation::query()
                    ->select('technician_id', DB::raw('COUNT(*) as total'))
                    ->whereIn('technician_id', $technicianIdsForTable)
                    ->whereIn('status', ['registered', 'survey', 'approved', 'installation'])
                    ->groupBy('technician_id')
                    ->pluck('total', 'technician_id');
                $activeInstallationIdsByUser = Installation::query()
                    ->whereIn('technician_id', $technicianIdsForTable)
                    ->whereIn('status', ['registered', 'survey', 'approved', 'installation'])
                    ->orderByDesc('updated_at')
                    ->get(['id', 'technician_id'])
                    ->groupBy('technician_id')
                    ->map(fn ($rows) => (int) optional($rows->first())->id);
                $technicianTaskSummary = $attendanceEmployees->mapWithKeys(function ($employee) use ($activeTicketCounts, $activeInstallationCounts, $activeTicketIdsByUser, $activeInstallationIdsByUser) {
                    $ticketCount = (int) ($activeTicketCounts[$employee->id] ?? 0);
                    $installationCount = (int) ($activeInstallationCounts[$employee->id] ?? 0);
                    $totalTask = $ticketCount + $installationCount;

                    return [
                        $employee->id => [
                            'active_ticket_id' => (int) ($activeTicketIdsByUser[$employee->id] ?? 0),
                            'active_installation_id' => (int) ($activeInstallationIdsByUser[$employee->id] ?? 0),
                            'ticket_active' => $ticketCount,
                            'installation_active' => $installationCount,
                            'total_active' => $totalTask,
                            'label' => $totalTask > 0 ? 'Bertugas' : 'Standby',
                        ],
                    ];
                });
            }
        }

        $regionKey = 'all';
        if (isset($coordinator) && $coordinator && $coordinator->region_id) {
            $regionKey = 'region_'.$coordinator->region_id;
        }
        $dayKey = now()->toDateString();

        $stats = Cache::remember('dashboard.stats.'.$regionKey.'.'.$dayKey, 60, function () use ($customerQuery, $ticketQuery, $installationQuery, $currentMonthStart, $today, $technicianIds, $technicianPresentToday, $washEmployeeIds, $washEmployeePresentToday) {
            $technicianTotal = $technicianIds->count();
            $washTotal = $washEmployeeIds->count();

            return [
                'total_customers' => $customerQuery->count(),
                'new_customers_this_month' => $customerQuery->clone()->where('created_at', '>=', $currentMonthStart)->count(),
                'open_tickets' => $ticketQuery->clone()->where('status', 'open')->count(),
                'tickets_today' => $ticketQuery->clone()->where('created_at', '>=', $today)->count(),
                'pending_installations' => $installationQuery->clone()->whereIn('status', ['registered', 'survey', 'approved'])->count(),
                'atk_today' => AtkTransaction::where('created_at', '>=', $today)->count(),
                'atk_month_revenue' => AtkTransaction::where('created_at', '>=', $currentMonthStart)->sum('total_amount'),
                'wash_today' => WashTransaction::where('created_at', '>=', $today)->count(),
                'wash_month_revenue' => WashTransaction::where('created_at', '>=', $currentMonthStart)->sum('total_amount'),
                'wedding_today' => WeddingPayment::where('status', 'paid')->where(function ($q) use ($today) {
                    $q->where('paid_at', '>=', $today)->orWhere(function ($sq) use ($today) {
                        $sq->whereNull('paid_at')->where('created_at', '>=', $today);
                    });
                })->count(),
                'wedding_month_revenue' => WeddingPayment::where('status', 'paid')->where(function ($q) use ($currentMonthStart) {
                    $q->where('paid_at', '>=', $currentMonthStart)->orWhere(function ($sq) use ($currentMonthStart) {
                        $sq->whereNull('paid_at')->where('created_at', '>=', $currentMonthStart);
                    });
                })->sum('amount'),
                'technician_total' => $technicianTotal,
                'technician_present_today' => $technicianPresentToday,
                'technician_not_present_today' => max($technicianTotal - $technicianPresentToday, 0),
                'wash_employee_total' => $washTotal,
                'wash_employee_present_today' => $washEmployeePresentToday,
                'wash_employee_not_present_today' => max($washTotal - $washEmployeePresentToday, 0),
                'total_olts' => \App\Support\ModuleGate::isEnabled('isp') && class_exists(\App\Models\OLT::class) ? \App\Models\OLT::count() : 0,
                'online_olts' => \App\Support\ModuleGate::isEnabled('isp') && class_exists(\App\Models\OLT::class) ? \App\Models\OLT::where('status', 'online')->count() : 0,
                'total_onts' => \App\Support\ModuleGate::isEnabled('isp') && class_exists(\App\Models\ONT::class) ? \App\Models\ONT::withoutTrashed()->count() : 0,
                'online_onts' => \App\Support\ModuleGate::isEnabled('isp') && class_exists(\App\Models\ONT::class) ? \App\Models\ONT::withoutTrashed()->where('oper_status', 'online')->count() : 0,
            ];
        });

        $presenceCutoff = now()->subSeconds(90);
        $onlineCounts = Cache::remember('dashboard.online_counts.'.$presenceCutoff->format('YmdHi'), 30, function () use ($presenceCutoff) {
            $onlineTechnicians = User::query()
                ->where('is_active', true)
                ->where('last_seen_at', '>=', $presenceCutoff)
                ->whereHas('role', fn ($query) => $query->where('name', 'field-technician'))
                ->count();
            $onlineWashEmployees = User::query()
                ->where('is_active', true)
                ->where('last_seen_at', '>=', $presenceCutoff)
                ->whereHas('role', fn ($query) => $query->where('name', 'wash-operator'))
                ->count();

            return [
                'online_technicians' => $onlineTechnicians,
                'online_wash_employees' => $onlineWashEmployees,
            ];
        });
        $onlineTechnicians = (int) ($onlineCounts['online_technicians'] ?? 0);
        $onlineWashEmployees = (int) ($onlineCounts['online_wash_employees'] ?? 0);

        // Traffic Data (Orders & Tickets per Month)
        $trafficData = [
            'labels' => [],
            'orders' => [],
            'tickets' => [],
        ];

        $atkMonthly = $this->aggregateCountByMonth(AtkTransaction::query(), 'created_at', $currentYear);
        $washMonthly = $this->aggregateCountByMonth(WashTransaction::query(), 'created_at', $currentYear);
        $ticketsMonthly = $this->aggregateCountByMonth($ticketQuery->clone(), 'created_at', $currentYear);

        for ($i = 1; $i <= 12; $i++) {
            $trafficData['labels'][] = \Carbon\Carbon::create(null, $i, 1)->format('F');
            $ordersCount = ($atkMonthly->get($i, 0)) + ($washMonthly->get($i, 0));
            $trafficData['orders'][] = $ordersCount;
            $trafficData['tickets'][] = $ticketsMonthly->get($i, 0);
        }

        // Orders Month-over-Month Growth
        $ordersCurrentMonth = AtkTransaction::where('created_at', '>=', $currentMonthStart)->count()
            + WashTransaction::where('created_at', '>=', $currentMonthStart)->count();
        $ordersPrevMonth = AtkTransaction::whereBetween('created_at', [$prevMonthStart, $prevMonthEnd])->count()
            + WashTransaction::whereBetween('created_at', [$prevMonthStart, $prevMonthEnd])->count();
        $ordersGrowthPercent = $ordersPrevMonth > 0
            ? round((($ordersCurrentMonth - $ordersPrevMonth) / $ordersPrevMonth) * 100, 1)
            : ($ordersCurrentMonth > 0 ? 100.0 : 0.0);
        $trafficStats = [
            'orders_growth_percent' => $ordersGrowthPercent,
        ];

        // System Metrics (CPU/RAM/Swap) to power SupportTracker & RevenueSources charts
        $metricsService = new SystemMetricsService;
        $systemMetrics = $metricsService->getMetrics();

        $recentTickets = \App\Support\ModuleGate::isEnabled('ticketing')
            ? $ticketQuery->clone()
                ->with(['customer', 'technicians'])
                ->where('status', '!=', 'closed')
                ->latest()
                ->take(5)
                ->get()
            : collect();

        $upcomingInstallations = \App\Support\ModuleGate::isEnabled('isp')
            ? $installationQuery->clone()
                ->with(['customer', 'technician'])
                ->whereIn('status', ['registered', 'survey', 'approved', 'installation'])
                ->orderBy('plan_date', 'asc')
                ->take(5)
                ->get()
            : collect();

        // Monthly Ticket Recap (Current Year)
        $ticketRecap = [];
        if (\App\Support\ModuleGate::isEnabled('ticketing')) {
            $monthlyTicketTotals = $this->aggregateCountByMonth($ticketQuery->clone(), 'created_at', $currentYear);
            $monthlyTicketResolved = $this->aggregateCountByMonth(
                $ticketQuery->clone()->whereIn('status', ['solved', 'closed']),
                'created_at',
                $currentYear
            );
            $monthlyTicketOpen = $this->aggregateCountByMonth(
                $ticketQuery->clone()->whereIn('status', ['open', 'assigned', 'in_progress', 'pending']),
                'created_at',
                $currentYear
            );

            for ($i = 1; $i <= 12; $i++) {
                $monthName = \Carbon\Carbon::create(null, $i, 1)->format('F');

                $ticketRecap[] = [
                    'month' => $monthName,
                    'total' => (int) ($monthlyTicketTotals->get($i, 0)),
                    'resolved' => (int) ($monthlyTicketResolved->get($i, 0)),
                    'open' => (int) ($monthlyTicketOpen->get($i, 0)),
                ];
            }
        }

        // Inventory & Assets Data
        $inventoryItems = \App\Support\ModuleGate::isEnabled('inventory')
            ? InventoryItem::orderBy('stock', 'asc')->take(5)->get()
            : collect();
        $totalInventoryValue = \App\Support\ModuleGate::isEnabled('inventory')
            ? (float) InventoryItem::sum(DB::raw('stock * price'))
            : 0;

        $deployedAssets = \App\Support\ModuleGate::isEnabled('inventory')
            ? Asset::with(['item', 'holder'])
                ->where('status', 'deployed')
                ->whereIn('holder_type', ['App\Models\User', 'App\Models\Coordinator'])
                ->latest()
                ->take(10)
                ->get()
            : collect();

        // Executive Financial KPIs (PSAK & General Ledger)
        $cashAccountIds = Account::where('code', '1001')->pluck('id');
        $bankAccountIds = Account::where('code', '1002')->pluck('id');
        $cashTotal = (float) JournalEntry::whereIn('account_id', $cashAccountIds)->sum(DB::raw('debit - credit'));
        $bankTotal = (float) JournalEntry::whereIn('account_id', $bankAccountIds)->sum(DB::raw('debit - credit'));
        $totalCashAndBank = $cashTotal + $bankTotal;

        $tradeReceivableIds = Account::where('code', '1101')->pluck('id');
        $staffReceivableIds = Account::where('code', '1103')->pluck('id');
        $tradeReceivableTotal = (float) JournalEntry::whereIn('account_id', $tradeReceivableIds)->sum(DB::raw('debit - credit'));
        $staffReceivableTotal = (float) JournalEntry::whereIn('account_id', $staffReceivableIds)->sum(DB::raw('debit - credit'));
        $totalReceivables = $tradeReceivableTotal + $staffReceivableTotal;

        $weddingMonthRevenue = (float) ($stats['wedding_month_revenue'] ?? 0);
        $atkMonthRevenue = (float) ($stats['atk_month_revenue'] ?? 0);
        $washMonthRevenue = (float) ($stats['wash_month_revenue'] ?? 0);
        $consolidatedMonthRevenue = $atkMonthRevenue + $washMonthRevenue + $weddingMonthRevenue;

        $monthRevenueGL = (float) JournalEntry::join('journals', 'journal_entries.journal_id', '=', 'journals.id')
            ->join('accounts', 'journal_entries.account_id', '=', 'accounts.id')
            ->where('accounts.type', 'revenue')
            ->where('journals.date', '>=', $currentMonthStart->toDateString())
            ->sum(DB::raw('journal_entries.credit - journal_entries.debit'));

        $monthExpenseGL = (float) JournalEntry::join('journals', 'journal_entries.journal_id', '=', 'journals.id')
            ->join('accounts', 'journal_entries.account_id', '=', 'accounts.id')
            ->where('accounts.type', 'expense')
            ->where('journals.date', '>=', $currentMonthStart->toDateString())
            ->sum(DB::raw('journal_entries.debit - journal_entries.credit'));

        $monthNetProfit = $monthRevenueGL - $monthExpenseGL;
        $netProfitMargin = $monthRevenueGL > 0 ? round(($monthNetProfit / $monthRevenueGL) * 100, 1) : 0;

        $executiveMetrics = [
            'total_cash_and_bank' => $totalCashAndBank,
            'cash_total' => $cashTotal,
            'bank_total' => $bankTotal,
            'total_receivables' => $totalReceivables,
            'trade_receivables' => $tradeReceivableTotal,
            'staff_receivables' => $staffReceivableTotal,
            'consolidated_month_revenue' => $consolidatedMonthRevenue,
            'atk_month_revenue' => $atkMonthRevenue,
            'wash_month_revenue' => $washMonthRevenue,
            'wedding_month_revenue' => $weddingMonthRevenue,
            'month_net_profit' => $monthNetProfit,
            'month_revenue_gl' => $monthRevenueGL,
            'month_expense_gl' => $monthExpenseGL,
            'net_profit_margin' => $netProfitMargin,
        ];

        // Unit Revenue Breakdown
        $unitBreakdown = [
            'atk' => [
                'label' => 'Toko & Retail',
                'revenue' => $atkMonthRevenue,
                'percentage' => $consolidatedMonthRevenue > 0 ? round(($atkMonthRevenue / $consolidatedMonthRevenue) * 100, 1) : 0,
                'badge' => 'primary',
                'icon' => 'fa-store',
            ],
            'wash' => [
                'label' => 'Jasa & Layanan',
                'revenue' => $washMonthRevenue,
                'percentage' => $consolidatedMonthRevenue > 0 ? round(($washMonthRevenue / $consolidatedMonthRevenue) * 100, 1) : 0,
                'badge' => 'info',
                'icon' => 'fa-hand-sparkles',
            ],
            'wedding' => [
                'label' => 'Booking & Reservasi',
                'revenue' => $weddingMonthRevenue,
                'percentage' => $consolidatedMonthRevenue > 0 ? round(($weddingMonthRevenue / $consolidatedMonthRevenue) * 100, 1) : 0,
                'badge' => 'danger',
                'icon' => 'fa-calendar-check',
            ],
        ];

        // Latest General Ledger Feed
        $latestJournalEntries = JournalEntry::with(['journal', 'account'])
            ->latest('id')
            ->take(6)
            ->get();

        $glTotalDebit = (float) JournalEntry::sum('debit');
        $glTotalCredit = (float) JournalEntry::sum('credit');
        $glIsBalanced = abs($glTotalDebit - $glTotalCredit) < 0.01;

        // Financial Chart Data (General Ledger + Fallback to Transactions)
        $monthExpr = $this->monthExpression('journals.date');
        $glIncomeByMonth = JournalEntry::join('journals', 'journal_entries.journal_id', '=', 'journals.id')
            ->join('accounts', 'journal_entries.account_id', '=', 'accounts.id')
            ->where('accounts.type', 'revenue')
            ->whereYear('journals.date', $currentYear)
            ->selectRaw($monthExpr.' as month_key, SUM(journal_entries.credit - journal_entries.debit) as total')
            ->groupBy('month_key')
            ->pluck('total', 'month_key');

        $glExpenseByMonth = JournalEntry::join('journals', 'journal_entries.journal_id', '=', 'journals.id')
            ->join('accounts', 'journal_entries.account_id', '=', 'accounts.id')
            ->where('accounts.type', 'expense')
            ->whereYear('journals.date', $currentYear)
            ->selectRaw($monthExpr.' as month_key, SUM(journal_entries.debit - journal_entries.credit) as total')
            ->groupBy('month_key')
            ->pluck('total', 'month_key');

        $incomeData = $this->aggregateSumByMonth(
            Transaction::query()->where('type', 'income'),
            'transaction_date',
            'amount',
            $currentYear
        );
        $expenseData = $this->aggregateSumByMonth(
            Transaction::query()->where('type', 'expense'),
            'transaction_date',
            'amount',
            $currentYear
        );

        $financialData = [
            'labels' => [],
            'income' => [],
            'expense' => [],
        ];

        for ($i = 1; $i <= 12; $i++) {
            $financialData['labels'][] = \Carbon\Carbon::create(null, $i, 1)->format('F');
            $inc = (float) ($glIncomeByMonth->get($i, null) ?? $incomeData->get($i, 0));
            $exp = (float) ($glExpenseByMonth->get($i, null) ?? $expenseData->get($i, 0));
            $financialData['income'][] = $inc;
            $financialData['expense'][] = $exp;
        }

        return view('dashboard', compact(
            'stats',
            'executiveMetrics',
            'unitBreakdown',
            'latestJournalEntries',
            'glIsBalanced',
            'glTotalDebit',
            'glTotalCredit',
            'recentTickets',
            'upcomingInstallations',
            'ticketRecap',
            'todayAttendance',
            'inventoryItems',
            'totalInventoryValue',
            'financialData',
            'deployedAssets',
            'trafficData',
            'trafficStats',
            'systemMetrics',
            'attendanceRole',
            'attendanceState',
            'attendanceDate',
            'attendanceDateLabel',
            'attendanceEmployees',
            'attendanceByUser',
            'technicianTaskSummary',
            'onlineTechnicians',
            'onlineWashEmployees'
        ));
    }

    protected function aggregateCountByMonth(Builder $query, string $dateColumn, int $year): Collection
    {
        return $this->aggregateByMonth($query, $dateColumn, 'COUNT(*)', 'total', $year);
    }

    protected function aggregateSumByMonth(Builder $query, string $dateColumn, string $sumColumn, int $year): Collection
    {
        $qualifiedColumn = $query->getModel()->qualifyColumn($sumColumn);

        return $this->aggregateByMonth($query, $dateColumn, 'COALESCE(SUM('.$qualifiedColumn.'), 0)', 'total', $year);
    }

    protected function aggregateByMonth(Builder $query, string $dateColumn, string $aggregateExpression, string $alias, int $year): Collection
    {
        $qualifiedDateColumn = $query->getModel()->qualifyColumn($dateColumn);
        $monthExpression = $this->monthExpression($qualifiedDateColumn);

        return $query
            ->whereYear($dateColumn, $year)
            ->selectRaw($monthExpression.' as month_key, '.$aggregateExpression.' as '.$alias)
            ->groupBy('month_key')
            ->pluck($alias, 'month_key')
            ->mapWithKeys(fn ($value, $month) => [(int) $month => $value]);
    }

    protected function monthExpression(string $qualifiedDateColumn): string
    {
        return match (DB::connection()->getDriverName()) {
            'sqlite' => "CAST(strftime('%m', {$qualifiedDateColumn}) AS INTEGER)",
            'pgsql' => "CAST(EXTRACT(MONTH FROM {$qualifiedDateColumn}) AS INTEGER)",
            default => "MONTH({$qualifiedDateColumn})",
        };
    }

    public function buildRoleAttendanceOverview(string $roleName): array
    {
        $roleUserIds = User::query()
            ->where('is_active', true)
            ->whereHas('role', function ($query) use ($roleName) {
                $query->where('name', $roleName);
            })
            ->pluck('id');

        $presentCount = 0;
        if ($roleUserIds->isNotEmpty()) {
            $presentCount = TechnicianAttendance::query()
                ->whereDate('clock_in', today())
                ->whereIn('status', ['present', 'late'])
                ->whereIn('user_id', $roleUserIds)
                ->distinct('user_id')
                ->count('user_id');
        }

        $totalEmployees = $roleUserIds->count();

        return [
            'role' => $roleName,
            'total' => $totalEmployees,
            'present' => $presentCount,
            'not_present' => max($totalEmployees - $presentCount, 0),
        ];
    }

    public function getTodayShiftSchedule(int $userId): ?TechnicianDailySchedule
    {
        $daily = TechnicianDailySchedule::query()
            ->where('user_id', $userId)
            ->whereDate('date', today())
            ->first();
        if ($daily) {
            return $daily;
        }

        $weekly = TechnicianSchedule::query()
            ->where('user_id', $userId)
            ->where('year', now()->weekYear)
            ->where('week_number', now()->weekOfYear)
            ->first();
        if (! $weekly) {
            return null;
        }

        $fallback = new TechnicianDailySchedule;
        $fallback->user_id = $userId;
        $fallback->date = today();
        $fallback->status = (string) ($weekly->status ?: TechnicianSchedule::STATUS_OFF);
        $fallback->notes = $weekly->notes;

        return $fallback;
    }
}
