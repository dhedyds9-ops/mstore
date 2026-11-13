<?php

namespace App\Http\Controllers;

use App\Models\AtkTransaction;
use App\Models\Transaction;
use App\Services\Atk\AtkSpreadsheetService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

class AtkReportController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:atk.report'),
        ];
    }

    private function buildData(Request $request)
    {
        $date = $request->input('date', now()->format('Y-m-d'));
        $month = $request->input('month', now()->format('Y-m'));

        $baseTrx = AtkTransaction::whereNull('reversed_at')
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', '!=', 'reversed');
            })
            ->whereDoesntHave('items', function ($q) {
                $q->where('item_type', 'customer_payment');
            });

        $dailyIncome = (clone $baseTrx)->whereDate('created_at', $date)->sum('total_amount');
        $dailyExpense = Transaction::where('type', 'expense')
            ->where('reference_number', 'like', 'ATK-EXP-%')
            ->whereDate('transaction_date', $date)->sum('amount');

        $monthlyIncome = (clone $baseTrx)->where('created_at', 'like', "$month%")->sum('total_amount');
        $monthlyExpense = Transaction::where('type', 'expense')
            ->where('reference_number', 'like', 'ATK-EXP-%')
            ->whereMonth('transaction_date', substr($month, 5, 2))
            ->whereYear('transaction_date', substr($month, 0, 4))->sum('amount');

        $dailyIncomeRows = (clone $baseTrx)->with('items')
            ->whereDate('created_at', $date)
            ->select(['id', 'transaction_number', 'total_amount', 'payment_method', 'created_at'])
            ->orderByDesc('created_at')
            ->get();

        $dailyExpenseRows = Transaction::where('type', 'expense')
            ->where('reference_number', 'like', 'ATK-EXP-%')
            ->whereDate('transaction_date', $date)
            ->select(['id', 'description', 'amount', 'transaction_date'])
            ->orderByDesc('transaction_date')->get();

        $monthlyDailyIncome = (clone $baseTrx)->where('created_at', 'like', "$month%")
            ->select(DB::raw('DATE(created_at) as d'), DB::raw('SUM(total_amount) as total'))
            ->groupBy(DB::raw('DATE(created_at)'))->orderBy('d', 'asc')->get();

        $monthlyDailyExpense = Transaction::where('type', 'expense')
            ->where('reference_number', 'like', 'ATK-EXP-%')
            ->whereMonth('transaction_date', substr($month, 5, 2))
            ->whereYear('transaction_date', substr($month, 0, 4))
            ->select(DB::raw('DATE(transaction_date) as d'), DB::raw('SUM(amount) as total'))
            ->groupBy(DB::raw('DATE(transaction_date)'))->orderBy('d', 'asc')->get();

        $dailyByPayment = (clone $baseTrx)->whereDate('created_at', $date)
            ->select('payment_method', DB::raw('SUM(total_amount) as amount'))
            ->groupBy('payment_method')->orderByDesc('amount')->get();

        $monthlyByPayment = (clone $baseTrx)->where('created_at', 'like', "$month%")
            ->select('payment_method', DB::raw('SUM(total_amount) as amount'))
            ->groupBy('payment_method')->orderByDesc('amount')->get();

        return compact('date', 'month', 'dailyIncome', 'dailyExpense', 'monthlyIncome', 'monthlyExpense', 'dailyIncomeRows', 'dailyExpenseRows', 'monthlyDailyIncome', 'monthlyDailyExpense', 'dailyByPayment', 'monthlyByPayment');
    }

    public function index(Request $request)
    {
        $data = $this->buildData($request);

        return view('atk.reports.index', $data);
    }

    public function pdf(Request $request)
    {
        $data = $this->buildData($request);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('atk.reports.pdf', $data)->setPaper('a4', 'portrait');

        return $pdf->download('laporan_atk.pdf');
    }

    public function excel(Request $request, AtkSpreadsheetService $spreadsheet)
    {
        $data = $this->buildData($request);
        $format = $request->get('format', 'xlsx');

        $headers = ['Kategori / Keterangan', 'Kolom 2', 'Kolom 3', 'Kolom 4', 'Kolom 5'];
        $rows = [
            ['LAPORAN KEUANGAN TOKO ATK', '', '', '', ''],
            ['Tanggal', $data['date'], 'Bulan', $data['month'], ''],
            ['', '', '', '', ''],
            ['RINGKASAN HARIAN', '', '', '', ''],
            ['Pemasukan', (float) $data['dailyIncome'], 'Pengeluaran', (float) $data['dailyExpense'], 'Laba Bersih', (float) ($data['dailyIncome'] - $data['dailyExpense'])],
            ['', '', '', '', ''],
            ['RINGKASAN BULANAN', '', '', '', ''],
            ['Pemasukan', (float) $data['monthlyIncome'], 'Pengeluaran', (float) $data['monthlyExpense'], 'Laba Bersih', (float) ($data['monthlyIncome'] - $data['monthlyExpense'])],
            ['', '', '', '', ''],
            ['RINCIAN PEMASUKAN HARIAN', '', '', '', ''],
            ['Waktu', 'No Transaksi', 'Item Produk', 'Metode', 'Total'],
        ];

        foreach ($data['dailyIncomeRows'] as $r) {
            $rows[] = [
                $r->created_at->format('H:i'),
                $r->transaction_number,
                $r->product_names['display'],
                strtoupper((string) $r->payment_method),
                (float) $r->total_amount,
            ];
        }

        $rows[] = ['', '', '', '', ''];
        $rows[] = ['RINCIAN PENGELUARAN HARIAN', '', '', '', ''];
        $rows[] = ['Tanggal', 'Deskripsi', 'Nominal', '', ''];

        foreach ($data['dailyExpenseRows'] as $r) {
            $rows[] = [
                Carbon::parse($r->transaction_date)->format('Y-m-d'),
                $r->description,
                (float) $r->amount,
                '',
                '',
            ];
        }

        return $spreadsheet->download('laporan_atk_' . now()->format('Ymd_His'), $headers, $rows, $format);
    }
}
