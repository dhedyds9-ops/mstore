<?php

namespace App\Support\Sidebar;

use App\Models\Role;

class SidebarMenu
{
    public static function tree(): array
    {
        return [
            [
                'type' => 'section',
                'id' => 'sec-general',
                'label' => 'General',
                'items' => [
                    self::link('dashboard', 'Dashboard', 'dashboard', permissions: ['dashboard.view']),
                    self::link('dashboard-consolidated', 'Konsolidasi', 'consolidation.index', permissions: ['consolidation.view']),
                ],
            ],
            [
                'type' => 'section',
                'id' => 'sec-pos',
                'label' => 'Point of Sale (POS)',
                'items' => [
                    self::group('bu-atk', 'Toko & Retail', [
                        self::link('atk-dashboard', 'Ringkasan', 'atk.dashboard', permissions: ['atk.view']),
                        self::link('atk-pos', 'Kasir Toko', 'atk.pos', permissions: ['atk.pos']),
                        self::link('atk-transactions', 'Transaksi', 'atk.transactions.index', permissions: ['atk.report']),
                        self::link('atk-cash-registers', 'Shift Kasir', 'atk.cash-registers.index', permissions: ['atk.manage']),
                        self::link('atk-manage', 'Produk & Barcode', 'atk.products.index', permissions: ['atk.manage']),
                        self::link('atk-expenses', 'Pengeluaran', 'atk.expenses.index', permissions: ['atk.report']),
                        self::link('atk-cash-movements', 'Mutasi Kas', 'atk.cash-movements.index', permissions: ['atk.manage']),
                        self::link('atk-owner-funds', 'Dana Talangan', 'atk.owner-funds.index', permissions: ['atk.manage']),
                        self::link('atk-float-accounts', 'Akun Float', 'atk.float-accounts.index', permissions: ['atk.view']),
                        self::link('atk-reports-sales', 'Laporan Penjualan', 'atk.reports.index', permissions: ['atk.report']),
                        self::link('atk-reports-cash', 'Laporan Kas', 'atk.reports.cash', permissions: ['atk.report']),
                    ], permissions: ['atk.view', 'atk.pos', 'atk.manage', 'atk.report']),
                    self::group('bu-wash', 'Jasa & Layanan', [
                        self::link('wash-pos', 'Kasir Layanan', 'wash.pos', permissions: ['wash.pos']),
                        self::link('wash-transactions', 'Transaksi Jasa', 'wash.transactions.index', permissions: ['wash.report']),
                        self::link('wash-services', 'Daftar Layanan', 'wash.services.index', permissions: ['wash.manage']),
                        self::link('wash-member-packages', 'Paket Langganan', 'wash.member-packages.index', permissions: ['wash.package.view']),
                        self::link('wash-shifts', 'Shift Kerja', 'wash.shifts.index', permissions: ['wash.shift.view']),
                        self::link('wash-cash-registers', 'Laci Kasir', 'wash.cash-registers.index', permissions: ['wash.cash.view']),
                        self::link('wash-cash-movements', 'Mutasi Kas', 'wash.cash-movements.index', permissions: ['wash.cash.view']),
                        self::link('wash-expenses', 'Pengeluaran', 'wash.expenses.index', permissions: ['wash.expense.view', 'wash.report', 'wash.manage']),
                        self::link('wash-stock', 'Stok Barang', 'wash.stock.index', permissions: ['wash.view']),
                        self::link('wash-suppliers', 'Supplier', 'wash.suppliers.index', permissions: ['wash.supplier.view']),
                        self::link('wash-members', 'Member', 'wash.members.index', permissions: ['wash.member.view']),
                        self::link('wash-loyalty', 'Loyalty Reward', 'wash.loyalty.index', permissions: ['wash.loyalty.view']),
                        self::link('wash-report', 'Laporan Jasa', 'wash.reports.index', permissions: ['wash.report']),
                    ], permissions: ['wash.view', 'wash.pos', 'wash.manage', 'wash.report']),
                ],
            ],
            [
                'type' => 'section',
                'id' => 'sec-wedding',
                'label' => 'Booking & Reservasi',
                'items' => [
                    self::group('bu-wedding', 'Pemesanan & Reservasi', [
                        self::link('wedding-dashboard', 'Ringkasan', 'wedding.dashboard', permissions: ['wedding.view']),
                        self::link('wedding-packages', 'Paket Layanan', 'wedding.packages.index', permissions: ['wedding.view']),
                        self::link('wedding-bookings', 'Data Booking', 'wedding.bookings.index', permissions: ['wedding.booking']),
                        self::link('wedding-schedule', 'Jadwal Acara', 'wedding.schedule.index', permissions: ['wedding.view']),
                        self::link('wedding-payments', 'Pembayaran', 'wedding.payments.index', permissions: ['wedding.payment']),
                        self::link('wedding-gallery', 'Galeri', 'wedding.gallery.index', permissions: ['wedding.view']),
                        self::link('wedding-reports', 'Laporan', 'reports.wedding', permissions: ['report.wedding.export', 'wedding.report']),
                    ], permissions: ['wedding.view']),
                ],
            ],
            [
                'type' => 'section',
                'id' => 'sec-finance',
                'label' => 'Keuangan & Akuntansi',
                'items' => [
                    self::group('finance-center', 'Keuangan & Kas', [
                        self::link('finance-dashboard', 'Ringkasan Kas', 'finance.index', permissions: ['finance.view']),
                        self::link('finance-profit-loss', 'Laba Rugi', 'finance.profit_loss', permissions: ['finance.view']),
                        self::link('companies', 'Perusahaan', 'companies.index', permissions: ['company.view']),
                        self::link('investors', 'Investor', 'investors.index', permissions: ['investor.view']),
                        self::link('finance-investor-report', 'Bagi Hasil', 'finance.investor_report', permissions: ['finance.view', 'investor.view']),
                    ], permissions: ['finance.view']),
                    self::group('accounting-center', 'Akuntansi (PSAK)', [
                        self::link('accounting-coa', 'Bagan Akun (COA)', 'accounting.accounts.index', permissions: ['accounting.view']),
                        self::link('accounting-journals', 'Jurnal Umum', 'accounting.journals.index', permissions: ['accounting.view']),
                        self::link('accounting-ledger', 'Buku Besar', 'accounting.ledger', permissions: ['accounting.view']),
                        self::link('accounting-trial-balance', 'Neraca Saldo', 'accounting.trial_balance', permissions: ['accounting.view']),
                        self::link('accounting-income-statement', 'Laba Rugi', 'accounting.income_statement', permissions: ['accounting.view']),
                        self::link('accounting-balance-sheet', 'Neraca', 'accounting.balance_sheet', permissions: ['accounting.view']),
                        self::link('accounting-cash-flow', 'Arus Kas', 'accounting.cash_flow', permissions: ['accounting.view']),
                        self::link('accounting-periods', 'Tutup Buku', 'accounting.periods.index', permissions: ['accounting.view']),
                    ], permissions: ['accounting.view', 'finance.view']),
                ],
            ],
            [
                'type' => 'section',
                'id' => 'sec-hr',
                'label' => 'SDM & Presensi',
                'items' => [
                    self::group('hr-staff', 'Kepegawaian', [
                        self::link('employees', 'Data Karyawan', 'employees.index', permissions: ['employee.view']),
                        self::link('employee-leave', 'Pengajuan Cuti', 'employee.leave-requests', permissions: ['leave.view']),
                        self::link('admin-leave', 'Kelola Cuti', 'admin.leave-requests', permissions: ['leave.manage']),
                    ], permissions: ['employee.view', 'leave.view', 'leave.manage']),
                    self::group('hr-attendance', 'Presensi & Payroll', [
                        self::link('attendance-kiosk', 'Kiosk Presensi', 'attendance.kiosk', permissions: ['attendance.create']),
                        self::link('attendance-daily', 'Presensi Harian', 'attendance.daily', permissions: ['attendance.view']),
                        self::link('attendance-payslip', 'Slip Gaji', 'attendance.payslip', permissions: ['attendance.view']),
                        self::link('attendance-recap', 'Rekap Presensi', 'attendance.index', permissions: ['attendance.view']),
                    ], permissions: ['attendance.view', 'attendance.create']),
                ],
            ],
            [
                'type' => 'section',
                'id' => 'sec-system',
                'label' => 'Pengaturan & Sistem',
                'items' => [
                    self::group('sys-settings', 'Pengaturan Usaha', [
                        self::link('settings', 'Profil Usaha', 'settings.index', permissions: ['setting.view']),
                        self::link('subscription', 'Langganan Usaha', 'subscription.index', permissions: ['setting.view'], roles: ['super-admin', 'manager']),
                        self::link('atk-settings', 'Setting Toko', 'settings.atk.index', permissions: ['setting.view']),
                        self::link('wash-settings', 'Setting Layanan', 'settings.wash.index', permissions: ['setting.view']),
                        self::link('attendance-settings', 'Setting Presensi', 'settings.attendance.index', permissions: ['setting.view']),
                        self::link('whatsapp-settings', 'Gateway WhatsApp', 'whatsapp.index', permissions: ['chat.view', 'setting.view']),
                        self::link('telegram-settings', 'Bot Telegram', 'telegram.index', permissions: ['telegram.view', 'setting.view']),
                    ], permissions: ['setting.view']),
                    self::group('sys-payment', 'Payment Gateway', [
                        self::link('payment-dashboard', 'Status Gateway', 'payment.dashboard', permissions: ['payment.view']),
                        self::link('payment-duitku', 'Duitku', 'payment.gateway', ['gateway' => 'duitku'], permissions: ['payment.view']),
                        self::link('payment-midtrans', 'Midtrans', 'payment.gateway', ['gateway' => 'midtrans'], permissions: ['payment.view']),
                    ], permissions: ['payment.view']),
                    self::group('sys-users', 'Akses & Pengguna', [
                        self::link('users', 'Pengguna', 'users.index', permissions: ['user.view']),
                        self::link('roles', 'Role & Izin', 'roles.index', permissions: ['role.view']),
                        self::link('audit-trail', 'Audit Trail', 'admin.audit-trail', permissions: ['audit.view']),
                        self::link('api-keys', 'API Keys', 'apikeys.index', permissions: ['apikey.view']),
                    ], permissions: ['user.view', 'role.view', 'audit.view', 'apikey.view']),
                ],
            ],
        ];
    }

    private static function link(
        string $id,
        string $label,
        string $route,
        array $params = [],
        array $permissions = [],
        array $roles = [],
        array $routePatterns = [],
    ): array {
        return [
            'type' => 'link',
            'id' => $id,
            'label' => $label,
            'route' => $route,
            'route_params' => $params,
            'icon' => self::inferIcon($id, $label, $route, 'link'),
            'route_patterns' => $routePatterns,
            'permissions' => $permissions,
            'roles' => $roles,
        ];
    }

    private static function group(string $id, string $label, array $children, array $permissions = [], array $roles = []): array
    {
        return [
            'type' => 'group',
            'id' => $id,
            'label' => $label,
            'icon' => self::inferIcon($id, $label, null, 'group'),
            'children' => $children,
            'permissions' => $permissions,
            'roles' => $roles,
        ];
    }

    private static function inferIcon(string $id, string $label, ?string $route = null, string $type = 'link'): string
    {
        $haystack = strtolower(trim($id.' '.$label.' '.($route ?? '')));

        $map = [
            'dashboard' => 'fa-solid fa-gauge-high',
            'audit' => 'fa-solid fa-clipboard-list',
            'report' => 'fa-solid fa-chart-column',
            'customer' => 'fa-solid fa-users',
            'pelanggan' => 'fa-solid fa-users',
            'installation' => 'fa-solid fa-screwdriver-wrench',
            'instalasi' => 'fa-solid fa-screwdriver-wrench',
            'package' => 'fa-solid fa-box-open',
            'paket' => 'fa-solid fa-box-open',
            'pppoe' => 'fa-solid fa-network-wired',
            'hotspot' => 'fa-solid fa-wifi',
            'voucher' => 'fa-solid fa-ticket',
            'monitor' => 'fa-solid fa-desktop',
            'outage' => 'fa-solid fa-triangle-exclamation',
            'incident' => 'fa-solid fa-bolt',
            'diagnostic' => 'fa-solid fa-stethoscope',
            'infra' => 'fa-solid fa-diagram-project',
            'olt' => 'fa-solid fa-server',
            'odc' => 'fa-solid fa-vector-square',
            'odp' => 'fa-solid fa-map-location-dot',
            'closure' => 'fa-solid fa-circle-nodes',
            'router' => 'fa-solid fa-router',
            'vpn' => 'fa-solid fa-shield-halved',
            'ticket' => 'fa-solid fa-headset',
            'sla' => 'fa-solid fa-stopwatch',
            'whatsapp' => 'fa-brands fa-whatsapp',
            'chat' => 'fa-solid fa-comments',
            'ai' => 'fa-solid fa-robot',
            'finance' => 'fa-solid fa-wallet',
            'accounting' => 'fa-solid fa-calculator',
            'coa' => 'fa-solid fa-sitemap',
            'bagan' => 'fa-solid fa-sitemap',
            'journal' => 'fa-solid fa-book-journal-whills',
            'jurnal' => 'fa-solid fa-book-journal-whills',
            'ledger' => 'fa-solid fa-book-open',
            'trial-balance' => 'fa-solid fa-scale-balanced',
            'neraca' => 'fa-solid fa-scale-balanced',
            'income' => 'fa-solid fa-chart-line',
            'laba' => 'fa-solid fa-chart-line',
            'cash-flow' => 'fa-solid fa-money-bill-transfer',
            'arus-kas' => 'fa-solid fa-money-bill-transfer',
            'period' => 'fa-solid fa-calendar-check',
            'periode' => 'fa-solid fa-calendar-check',
            'kiosk' => 'fa-solid fa-tablet-screen-button',
            'investor' => 'fa-solid fa-hand-holding-dollar',
            'employee' => 'fa-solid fa-id-card',
            'karyawan' => 'fa-solid fa-id-card',
            'attendance' => 'fa-solid fa-fingerprint',
            'absensi' => 'fa-solid fa-fingerprint',
            'schedule' => 'fa-solid fa-calendar-days',
            'jadwal' => 'fa-solid fa-calendar-days',
            'leave' => 'fa-solid fa-plane-departure',
            'cuti' => 'fa-solid fa-plane-departure',
            'inventory' => 'fa-solid fa-boxes-stacked',
            'asset' => 'fa-solid fa-toolbox',
            'toko' => 'fa-solid fa-store',
            'retail' => 'fa-solid fa-store',
            'atk' => 'fa-solid fa-store',
            'jasa' => 'fa-solid fa-hand-holding-heart',
            'layanan' => 'fa-solid fa-hand-holding-heart',
            'wash' => 'fa-solid fa-spray-can-sparkles',
            'member' => 'fa-solid fa-id-badge',
            'loyalty' => 'fa-solid fa-gift',
            'reward' => 'fa-solid fa-award',
            'wedding' => 'fa-solid fa-calendar-check',
            'reservasi' => 'fa-solid fa-calendar-check',
            'subscription' => 'fa-solid fa-crown',
            'langganan' => 'fa-solid fa-crown',
            'gallery' => 'fa-solid fa-images',
            'galeri' => 'fa-solid fa-images',
            'booking' => 'fa-solid fa-calendar-check',
            'payment' => 'fa-solid fa-money-check-dollar',
            'pembayaran' => 'fa-solid fa-money-check-dollar',
            'cctv' => 'fa-solid fa-video',
            'survey' => 'fa-solid fa-clipboard-check',
            'setting' => 'fa-solid fa-gear',
            'settings' => 'fa-solid fa-gear',
            'region' => 'fa-solid fa-map',
            'wilayah' => 'fa-solid fa-map',
            'field-leader' => 'fa-solid fa-user-tie',
            'pengurus' => 'fa-solid fa-user-tie',
            'user' => 'fa-solid fa-user',
            'role' => 'fa-solid fa-user-shield',
            'telegram' => 'fa-brands fa-telegram',
            'api' => 'fa-solid fa-key',
            'security' => 'fa-solid fa-lock',
            'portal' => 'fa-solid fa-globe',
            'invoice' => 'fa-solid fa-file-invoice-dollar',
            'profil' => 'fa-solid fa-address-card',
            'profile' => 'fa-solid fa-address-card',
            'network' => 'fa-solid fa-signal',
            'banner' => 'fa-solid fa-bullhorn',
        ];

        foreach ($map as $keyword => $icon) {
            if (str_contains($haystack, $keyword)) {
                return $icon;
            }
        }

        return $type === 'group' ? 'fa-solid fa-folder-tree' : 'fa-regular fa-circle';
    }
}
