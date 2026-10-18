<?php

namespace App\Services;

use App\Models\AtkProduct;
use App\Models\AtkTransaction;
use App\Models\AtkTransactionItem;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Ticket;
use App\Models\WashService;
use App\Models\WashTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AiService
{
    /**
     * Process Chat Message
     */
    public function processChat($message)
    {
        $message = strtolower($message);

        // Intent Detection
        if (str_contains($message, 'help') || str_contains($message, 'bantuan') || str_contains($message, 'cara') || str_contains($message, 'fitur') || str_contains($message, 'apa itu') || str_contains($message, 'kamu bisa apa')) {
            return $this->getHelp($message);
        }

        // --- PUBLIC INFORMATION (Guest Accessible) ---

        // ATK Promo
        if (str_contains($message, 'atk') || str_contains($message, 'alat tulis') || str_contains($message, 'sekolah') || str_contains($message, 'kantor') || str_contains($message, 'buku') || str_contains($message, 'pena')) {
            return $this->getAtkPromo();
        }

        // Wash Services
        if (str_contains($message, 'wash') || str_contains($message, 'cuci') || str_contains($message, 'steam') || str_contains($message, 'motor') || str_contains($message, 'mobil') || str_contains($message, 'bersih')) {
            return $this->getWashServices();
        }

        // Check authentication for data access (Private Data)
        if (! Auth::check()) {
            return 'Halo! Saya adalah Asisten AI MStore. Saya dapat menjelaskan fitur-fitur dan layanan kami kepada Anda.
            <br><br>
            <b>Layanan Publik (Bisa ditanyakan):</b>
            <ul>
                <li>Produk ATK (Alat Tulis Kantor)</li>
                <li>Jasa Cuci Kendaraan (Auto Wash)</li>
            </ul>
            <br>
            Silakan login untuk mengakses data pribadi seperti Tagihan, Penjualan, dan Stok.';
        }

        if (str_contains($message, 'unpaid') || str_contains($message, 'belum bayar') || str_contains($message, 'tunggakan')) {
            return $this->getUnpaidCustomers();
        }

        if (str_contains($message, 'untung') || str_contains($message, 'profit') || str_contains($message, 'kesehatan bisnis') || str_contains($message, 'laba')) {
            return $this->getBusinessHealth();
        }

        if (str_contains($message, 'summary') || str_contains($message, 'ringkasan') || str_contains($message, 'kondisi') || str_contains($message, 'semua menu')) {
            return $this->getSystemOverview();
        }

        if (str_contains($message, 'tiket') || str_contains($message, 'gangguan') || str_contains($message, 'keluhan')) {
            return $this->getTicketInsights();
        }

        if (str_contains($message, 'pelanggan') || str_contains($message, 'customer') || str_contains($message, 'user')) {
            return $this->getCustomerInsights();
        }

        if (str_contains($message, 'sales') || str_contains($message, 'penjualan')) {
            $data = $this->getSalesForecast();
            $forecast = number_format($data['forecast_next_day'], 0, ',', '.');
            $trend = $data['trend'];
            $confidence = $data['confidence'];

            return "Berdasarkan analisis saya, penjualan untuk besok diproyeksikan sebesar **Rp {$forecast}**.<br>Tren: **{$trend}** (Keyakinan: {$confidence}%).";
        }

        if (str_contains($message, 'stock') || str_contains($message, 'stok')) {
            $suggestions = $this->getRestockSuggestions();
            if ($suggestions->isEmpty()) {
                return 'Stok aman! Tidak ada kebutuhan restock mendesak.';
            }
            $list = [];
            foreach ($suggestions as $item) {
                $list[] = '<b>'.e($item['product_name']).'</b>: Habis dalam '.e($item['days_until_stockout']).' hari. Tambah +'.e($item['recommended_restock']).'.';
            }

            return [
                'type' => 'list',
                'title' => 'Rekomendasi Restock:',
                'items' => $list,
            ];
        }

        return "Maaf, saya tidak mengerti. Anda bisa bertanya tentang 'Penjualan', 'Stok', 'Tagihan Belum Bayar', atau 'Bantuan'.";
    }

    /**
     * Get Usage Instructions (Knowledge Base)
     */
    public function getHelp($query)
    {
        $knowledgeBase = [
            'dashboard' => '**Dashboard** adalah pusat kendali Anda. Fitur utamanya meliputi:
                <ul>
                    <li>**Prediksi Penjualan**: Prediksi pendapatan AI untuk hari berikutnya.</li>
                    <li>**Restock Pintar**: Peringatan untuk produk yang perlu ditambah stoknya berdasarkan kecepatan penjualan.</li>
                    <li>**Wawasan Bisnis**: Indikator kinerja utama seperti pertumbuhan pendapatan dan produk terlaris.</li>
                </ul>',
            'transaction' => "Untuk mengelola **Transaksi**:
                <ol>
                    <li>Buka menu 'Transaksi'.</li>
                    <li>Lihat daftar transaksi pending, disetujui, dan ditolak.</li>
                    <li>Klik transaksi untuk melihat detail.</li>
                    <li>Gunakan tombol 'Setujui' atau 'Tolak' untuk memperbarui status.</li>
                </ol>",
            'inventory' => "Untuk mengelola **Inventaris** (Alat & Aset):
                <ul>
                    <li>**Tambah Barang**: Masuk ke Operasional > Tools & SDM > Inventory.</li>
                    <li>**Aset Saya**: Lihat aset yang ditugaskan kepada Anda di bawah 'Aset Saya'.</li>
                    <li>**Pelacakan**: Pantau penggunaan alat dan penugasan teknisi.</li>
                </ul>",
            'customer' => "Untuk mengelola **Pelanggan**:
                <ul>
                    <li>**Data Pelanggan**: Lihat semua pengguna terdaftar dan detailnya.</li>
                    <li>**Riwayat Transaksi**: Pantau pembelian ATK dan transaksi Wash pelanggan.</li>
                </ul>",
            'finance' => 'Untuk mengelola **Keuangan**:
                <ul>
                    <li>**Dashboard**: Tinjauan kesehatan keuangan.</li>
                    <li>**Akuntansi**: Akses Neraca Saldo, Laporan Laba Rugi, Neraca, Buku Besar, dan Arus Kas.</li>
                    <li>**Investor**: Kelola data investor dan saham.</li>
                </ul>',
            'atk' => 'Untuk mengelola **Toko ATK**:
                <ul>
                    <li>**POS**: Point of Sale untuk tugas kasir.</li>
                    <li>**Produk**: Kelola stok dan harga alat tulis.</li>
                    <li>**Laporan**: Lihat riwayat transaksi dan laporan penjualan.</li>
                </ul>',
            'wash' => 'Untuk mengelola **Cuci Kendaraan**:
                <ul>
                    <li>**POS**: Sistem kasir untuk layanan cuci mobil/motor.</li>
                    <li>**Karyawan**: Kelola staf pencuci dan komisi.</li>
                    <li>**Layanan**: Konfigurasi paket dan harga cuci.</li>
                </ul>',
            'operasional' => 'Untuk mengelola **Operasional**:
                <ul>
                    <li>**Tiket & Gangguan**: Tangani keluhan pelanggan dan masalah teknis.</li>
                    <li>**Teknisi**: Kelola data dan jadwal teknisi.</li>
                    <li>**Absensi**: Lacak kehadiran karyawan.</li>
                </ul>',
            'invoice' => "Untuk mengelola **Tagihan**:
                <ul>
                    <li>**Buat**: Tagihan sering dibuat secara otomatis berdasarkan siklus penagihan.</li>
                    <li>**Status**: Lacak status 'Pending', 'Lunas', atau 'Terlambat'.</li>
                    <li>**Pengingat**: Kirim pengingat pembayaran ke pelanggan dengan tagihan pending.</li>
                </ul>",
            'settings' => 'Di **Pengaturan**, Anda dapat:
                <ul>
                    <li>**Umum**: Konfigurasi nama situs, mata uang, dan zona waktu.</li>
                    <li>**Pengguna**: Kelola akun admin dan peran/izin.</li>
                    <li>**Notifikasi**: Atur peringatan email atau SMS untuk kejadian sistem.</li>
                </ul>',
        ];

        foreach ($knowledgeBase as $key => $answer) {
            if (str_contains($query, $key)) {
                return $answer; // Return HTML directly for rich text rendering
            }
        }

        // Default response if no specific feature mentioned
        return [
            'type' => 'list',
            'title' => 'Saya bisa membantu Anda dengan fitur berikut:',
            'items' => [
                '<b>Dashboard</b>: Ringkasan & Wawasan AI',
                '<b>Pelanggan</b>: Data & Riwayat Transaksi',
                '<b>Keuangan</b>: Akuntansi & Investor',
                '<b>Toko ATK</b>: POS & Inventaris',
                '<b>Cuci Kendaraan</b>: POS & Layanan Cuci',
                '<b>Operasional</b>: Tiket, Teknisi & Alat',
                '<b>Pengaturan</b>: Konfigurasi Sistem',
            ],
        ];
    }

    /**
     * Get Unpaid Customers
     */
    public function getUnpaidCustomers()
    {
        // Assuming 'pending' means unpaid based on migration
        $unpaidInvoices = Invoice::where('status', 'pending')
            ->with(['user.customer'])
            ->orderBy('due_date', 'asc')
            ->get();

        if ($unpaidInvoices->isEmpty()) {
            return 'Kabar baik! Tidak ada pelanggan yang menunggak saat ini.';
        }

        $list = [];
        foreach ($unpaidInvoices as $invoice) {
            $name = $invoice->user ? $invoice->user->name : 'Pengguna Tidak Diketahui';
            $amount = number_format($invoice->amount, 0, ',', '.');
            $dueDate = $invoice->due_date ? $invoice->due_date->format('d M Y') : 'N/A';

            $link = '#';
            if ($invoice->user && $invoice->user->customer) {
                $link = route('customers.show', $invoice->user->customer->id);
                $name = "<a href='".e($link)."' target='_blank' class='text-decoration-none fw-bold'>".e($name).'</a>';
            } else {
                $name = '<b>'.e($name).'</b>';
            }

            // Format: Name - Amount (Due Date)
            $list[] = "{$name} - Rp ".e($amount)." <br><small class='text-muted'>Jatuh Tempo: ".e($dueDate).'</small>';
        }

        return [
            'type' => 'list',
            'title' => 'Tagihan Belum Lunas ('.count($list).'):',
            'items' => $list,
        ];
    }

    /**
     * Analyze sales data to suggest restocking with "Smart Velocity" logic.
     * Uses weighted average (recent sales matter more) and predicts stockout date.
     */
    public function getRestockSuggestions()
    {
        $daysToAnalyze = 30;
        $startDate = Carbon::now()->subDays($daysToAnalyze);

        // Fetch daily sales per product for weighted calculation
        $salesData = AtkTransactionItem::select(
            'product_id',
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(quantity) as daily_qty')
        )
            ->whereHas('product')
            ->where('created_at', '>=', $startDate)
            ->groupBy('product_id', 'date')
            ->get()
            ->groupBy('product_id');

        $suggestions = [];

        // Preload all products in one query to avoid N+1
        $productIds = $salesData->keys()->toArray();
        $products = AtkProduct::whereIn('id', $productIds)->get()->keyBy('id');

        foreach ($salesData as $productId => $dailySales) {
            $product = $products[$productId] ?? null;
            if (! $product) {
                continue;
            }

            // Calculate Weighted Velocity
            // Recent 7 days = weight 2.0, Older days = weight 1.0
            $totalWeightedQty = 0;
            $totalWeight = 0;
            $recentCutoff = Carbon::now()->subDays(7);

            foreach ($dailySales as $record) {
                $recordDate = Carbon::parse($record->date);
                $weight = $recordDate->gte($recentCutoff) ? 2.0 : 1.0;

                $totalWeightedQty += ($record->daily_qty * $weight);
                $totalWeight += $weight;
            }

            // Normalize total weight to cover the full period properly
            // If we have missing days (0 sales), we should account for them in the denominator
            // Effective days = 7 days * 2 + 23 days * 1 = 14 + 23 = 37 "weight units"
            $effectiveWeightUnits = (7 * 2) + ($daysToAnalyze - 7);

            // Average Daily Sales (Weighted)
            $avgDailySales = $totalWeight > 0 ? $totalWeightedQty / $effectiveWeightUnits : 0;

            if ($avgDailySales <= 0) {
                continue;
            }

            $daysUntilStockout = $product->stock / $avgDailySales;
            $recommendedBuffer = $avgDailySales * 14; // 2 weeks buffer

            // Logic: Alert if stock runs out in < 7 days
            if ($daysUntilStockout < 7) {
                $suggestions[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'current_stock' => $product->stock,
                    'avg_daily_sales' => round($avgDailySales, 2),
                    'days_until_stockout' => round($daysUntilStockout, 1),
                    'recommended_restock' => ceil(max(0, $recommendedBuffer - $product->stock)),
                    'confidence' => 'High', // Based on consistent data presence
                    'reason' => 'Prediksi habis dalam '.round($daysUntilStockout, 1).' hari berdasarkan kecepatan penjualan tertimbang.',
                ];
            }
        }

        return collect($suggestions)->sortBy('days_until_stockout')->values();
    }

    /**
     * Predict future sales using Linear Regression (Least Squares).
     * Returns 7-day forecast with confidence score and trend analysis.
     */
    public function getSalesForecast()
    {
        $historyDays = 30;
        $forecastDays = 7;
        $startDate = Carbon::now()->subDays($historyDays);

        $dailySales = AtkTransaction::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(total_amount) as total')
        )
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Prepare data for regression: x = day index (0 to 29), y = total sales
        $points = [];
        $dates = [];
        $lastDate = Carbon::now()->subDays($historyDays);

        // Fill gaps with 0
        for ($i = 0; $i < $historyDays; $i++) {
            $currentDate = $lastDate->copy()->addDays($i + 1)->format('Y-m-d');
            $record = $dailySales->firstWhere('date', $currentDate);
            $y = $record ? $record->total : 0;

            $points[] = ['x' => $i, 'y' => $y];
            $dates[$currentDate] = $y;
        }

        // Calculate Linear Regression
        $regression = $this->calculateLinearRegression($points);
        $slope = $regression['slope'];
        $intercept = $regression['intercept'];
        $r2 = $regression['r2'];

        // Generate Forecast for next 7 days
        $forecast = [];
        for ($i = 0; $i < $forecastDays; $i++) {
            $futureDayIndex = $historyDays + $i;
            $predictedValue = ($slope * $futureDayIndex) + $intercept;

            // Basic Seasonality Adjustment (Weekend Multiplier)
            $futureDate = Carbon::now()->addDays($i + 1);
            if ($futureDate->isWeekend()) {
                $predictedValue *= 1.1; // Assume 10% bump on weekends (mock logic)
            }

            $forecast[] = [
                'date' => $futureDate->format('Y-m-d'),
                'value' => max(0, round($predictedValue)),
            ];
        }

        // Determine Trend and Confidence
        $trend = 'Stabil';
        if ($slope > 5000) {
            $trend = 'Naik 📈';
        } // Threshold: > 5k growth per day
        if ($slope < -5000) {
            $trend = 'Turun 📉';
        }

        $confidenceScore = min(98, max(40, round($r2 * 100))); // Cap between 40% and 98%

        return [
            'history' => $dailySales, // Keep for chart history
            'forecast' => $forecast,
            'forecast_next_day' => $forecast[0]['value'],
            'trend' => $trend,
            'confidence' => $confidenceScore,
            'slope' => $slope,
        ];
    }

    /**
     * Get Business Insights (Revenue Growth, Top Products)
     */
    public function getBusinessInsights()
    {
        // 1. Revenue Growth (Current Month vs Last Month)
        $currentMonthStart = Carbon::now()->startOfMonth();
        $lastMonthStart = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();

        $currentMonthRevenue = AtkTransaction::where('created_at', '>=', $currentMonthStart)->sum('total_amount');
        $lastMonthRevenue = AtkTransaction::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->sum('total_amount');

        $growth = 0;
        if ($lastMonthRevenue > 0) {
            $growth = (($currentMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100;
        } elseif ($currentMonthRevenue > 0) {
            $growth = 100;
        }

        // 2. Top Products (By Quantity Sold in last 30 days)
        $topProducts = AtkTransactionItem::select('product_id', DB::raw('SUM(quantity) as total_qty'))
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->with('product')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->product ? $item->product->name : ($item->product_name ?? 'Produk Tidak Diketahui'),
                    'qty' => $item->total_qty,
                ];
            });

        // 3. Repeat Customers (Customers with > 1 transaction in last 30 days)
        if (Schema::hasColumn('atk_transactions', 'customer_name')) {
            $repeatCustomers = AtkTransaction::select('customer_name')
                ->where('created_at', '>=', Carbon::now()->subDays(30))
                ->whereNotNull('customer_name')
                ->where('customer_name', '!=', '')
                ->groupBy('customer_name')
                ->havingRaw('COUNT(*) > 1')
                ->count();
        } elseif (Schema::hasColumn('atk_transactions', 'user_id')) {
            $repeatCustomers = AtkTransaction::select('user_id')
                ->where('created_at', '>=', Carbon::now()->subDays(30))
                ->whereNotNull('user_id')
                ->groupBy('user_id')
                ->havingRaw('COUNT(*) > 1')
                ->count();
        } else {
            $repeatCustomers = 0;
        }

        // 4. Generate Insight Text
        $topProductName = $topProducts->first() ? $topProducts->first()['name'] : 'N/A';
        $growthText = $growth >= 0 ? 'naik' : 'turun';
        $insightText = "Pendapatan {$growthText} sebesar ".abs(round($growth, 1))."% dibandingkan bulan lalu. Produk terlaris: {$topProductName}.";

        return [
            'revenue_growth' => round($growth, 1),
            'current_month_revenue' => $currentMonthRevenue,
            'top_products' => $topProducts,
            'top_product' => $topProductName,
            'repeat_customers' => $repeatCustomers,
            'insight_text' => $insightText,
        ];
    }

    /**
     * Helper: Calculate Linear Regression
     */
    private function calculateLinearRegression($points)
    {
        $n = count($points);
        if ($n < 2) {
            return ['slope' => 0, 'intercept' => 0, 'r2' => 0];
        }

        $sumX = 0;
        $sumY = 0;
        $sumXY = 0;
        $sumXX = 0;
        $sumYY = 0;

        foreach ($points as $point) {
            $x = $point['x'];
            $y = $point['y'];

            $sumX += $x;
            $sumY += $y;
            $sumXY += ($x * $y);
            $sumXX += ($x * $x);
            $sumYY += ($y * $y);
        }

        // Avoid division by zero
        $denominator = ($n * $sumXX - $sumX * $sumX);
        if ($denominator == 0) {
            return ['slope' => 0, 'intercept' => $sumY / $n, 'r2' => 0];
        }

        $slope = ($n * $sumXY - $sumX * $sumY) / $denominator;
        $intercept = ($sumY - $slope * $sumX) / $n;

        // Calculate R-squared (Coefficient of Determination)
        $ssTot = 0;
        $ssRes = 0;
        $meanY = $sumY / $n;

        foreach ($points as $point) {
            $y = $point['y'];
            $x = $point['x'];
            $predictedY = $slope * $x + $intercept;

            $ssTot += pow($y - $meanY, 2);
            $ssRes += pow($y - $predictedY, 2);
        }

        $r2 = $ssTot != 0 ? 1 - ($ssRes / $ssTot) : 0;

        return [
            'slope' => $slope,
            'intercept' => $intercept,
            'r2' => $r2,
        ];
    }

    /**
     * Get ATK Promo (Dynamic)
     */
    public function getAtkPromo()
    {
        try {
            if (! class_exists(AtkProduct::class) || ! Schema::hasTable('atk_products')) {
                return 'Layanan ATK belum tersedia.';
            }

            $products = AtkProduct::where('stock', '>', 0)->latest()->take(5)->get();
            if ($products->isEmpty()) {
                return 'Stok ATK sedang kosong.';
            }

            $list = [];
            foreach ($products as $item) {
                $price = number_format($item->sell_price_retail, 0, ',', '.');
                $list[] = "<b>".e($item->name)."</b> - Rp {$price}";
            }

            return [
                'type' => 'list',
                'title' => 'Produk Alat Tulis Kantor Terbaru:',
                'items' => $list,
            ];
        } catch (\Exception $e) {
            return 'Maaf, data ATK tidak dapat diakses saat ini.';
        }
    }

    /**
     * Get Wash Services (Dynamic)
     */
    public function getWashServices()
    {
        try {
            if (! class_exists(WashService::class) || ! Schema::hasTable('wash_services')) {
                return 'Layanan Cuci Kendaraan belum tersedia.';
            }

            $services = WashService::all();
            if ($services->isEmpty()) {
                return 'Belum ada layanan cuci yang terdaftar.';
            }

            $list = [];
            foreach ($services as $srv) {
                $price = number_format($srv->price, 0, ',', '.');
                $type = e(ucfirst($srv->vehicle_type ?? 'Kendaraan'));
                $list[] = "<b>".e($srv->name)." ({$type})</b> - Rp {$price}";
            }

            return [
                'type' => 'list',
                'title' => 'Layanan Cuci & Steam:',
                'items' => $list,
            ];
        } catch (\Exception $e) {
            return 'Maaf, info layanan cuci tidak tersedia.';
        }
    }

    /**
     * Get System Overview (Holistic Analysis)
     */
    public function getSystemOverview()
    {
        $today = Carbon::today();

        $stats = [
            'Pelanggan' => Customer::count(),
            'Tiket Terbuka' => Ticket::whereIn('status', ['open', 'assigned', 'in_progress'])->count(),
            'Tagihan Tertunggak' => Invoice::where('status', 'pending')->count(),
            'Transaksi ATK Hari Ini' => AtkTransaction::whereDate('created_at', $today)->count(),
            'Transaksi Wash Hari Ini' => WashTransaction::whereDate('created_at', $today)->count(),
        ];

        $list = [];
        foreach ($stats as $label => $value) {
            $list[] = "<b>".e($label)."</b>: ".e($value);
        }

        // Add some "intelligence" analysis
        $analysis = "Secara keseluruhan, sistem memiliki **{$stats['Pelanggan']}** pelanggan terdaftar. ";
        if ($stats['Tiket Terbuka'] > 5) {
            $analysis .= "Ada beban tiket gangguan yang cukup tinggi (**{$stats['Tiket Terbuka']}** tiket). Perlu perhatian tim operasional.";
        } else {
            $analysis .= "Beban gangguan rendah, operasional berjalan lancar.";
        }

        return [
            'type' => 'list',
            'title' => 'Ringkasan Kondisi Sistem MStore:',
            'items' => $list,
            'footer' => "<br><i>Analisis AI: {$analysis}</i>",
        ];
    }

    /**
     * Get Ticket Insights
     */
    public function getTicketInsights()
    {
        $tickets = Ticket::whereIn('status', ['open', 'assigned', 'in_progress'])
            ->with(['customer', 'assignedUser'])
            ->orderBy('priority', 'desc')
            ->latest()
            ->take(5)
            ->get();

        if ($tickets->isEmpty()) {
            return "Semua tiket gangguan telah teratasi. Kerja bagus!";
        }

        $list = [];
        foreach ($tickets as $ticket) {
            $customer = $ticket->customer ? e($ticket->customer->name) : 'Umum';
            $priority = e(strtoupper((string) $ticket->priority));
            $color = $ticket->priority === 'urgent' ? 'danger' : ($ticket->priority === 'high' ? 'warning' : 'primary');
            $subject = e($ticket->subject);
            $status = e($ticket->status);
            $list[] = "<b>[{$priority}]</b> {$subject} <br><small class='text-muted'>Pelanggan: {$customer} | Status: {$status}</small>";
        }

        return [
            'type' => 'list',
            'title' => 'Tiket Gangguan Terkini:',
            'items' => $list,
        ];
    }

    /**
     * Get Customer Insights
     */
    public function getCustomerInsights()
    {
        $total = Customer::count();
        $active = Customer::where('status', 'active')->count();
        $newThisMonth = Customer::where('created_at', '>=', Carbon::now()->startOfMonth())->count();

        $list = [
            "Total Pelanggan: <b>{$total}</b>",
            "Pelanggan Aktif: <b>{$active}</b>",
            "Pelanggan Baru (Bulan Ini): <b>{$newThisMonth}</b>",
        ];

        return [
            'type' => 'list',
            'title' => 'Statistik Pelanggan:',
            'items' => $list,
        ];
    }

    /**
     * Get Business Health (Revenue from all sources)
     */
    public function getBusinessHealth()
    {
        $thisMonth = Carbon::now()->startOfMonth();

        $atkRevenue = AtkTransaction::where('created_at', '>=', $thisMonth)->sum('total_amount');
        $washRevenue = WashTransaction::where('created_at', '>=', $thisMonth)->sum('total_amount');

        $total = $atkRevenue + $washRevenue;

        $list = [
            "Pendapatan Toko ATK: <b>Rp ".number_format($atkRevenue, 0, ',', '.')."</b>",
            "Pendapatan Cuci Kendaraan: <b>Rp ".number_format($washRevenue, 0, ',', '.')."</b>",
            "Total Estimasi (Bulan Ini): <b>Rp ".number_format($total, 0, ',', '.')."</b>",
        ];

        $analysis = "Kontribusi terbesar bulan ini berasal dari **".
                   ($atkRevenue > $washRevenue ? 'Toko ATK' : 'Cuci Kendaraan')."**. ";

        return [
            'type' => 'list',
            'title' => 'Kesehatan Bisnis (Bulan Ini):',
            'items' => $list,
            'footer' => "<br><i>Analisis AI: {$analysis}</i>",
        ];
    }
}
