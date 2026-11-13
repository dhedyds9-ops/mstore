<?php

namespace App\Http\Controllers;

use App\Models\OwnerFund;
use App\Services\Atk\AtkSpreadsheetService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class AtkOwnerFundReportController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:atk.report', only: ['index', 'pdf', 'excel']),
        ];
    }

    public function index(Request $request)
    {
        $start = $request->get('start_date', now()->subDays(30));
        $end = $request->get('end_date', now());
        $status = $request->get('status');

        // Normalize dates
        if (is_string($start)) {
            $start = \Illuminate\Support\Carbon::parse($start);
        }
        if (is_string($end)) {
            $end = \Illuminate\Support\Carbon::parse($end);
        }

        $query = OwnerFund::orderBy('created_at', 'desc')->whereDate('created_at', '>=', $start)->whereDate('created_at', '<=', $end);
        if ($status) {
            $query->where('status', $status);
        }

        $funds = $query->customPaginate();

        // Calculate current balance based on the latest transaction before or during the end date
        $latestTransaction = OwnerFund::whereDate('created_at', '<=', $end)->orderBy('created_at', 'desc')->first();
        $currentBalance = $latestTransaction->balance ?? 0;

        $incoming = $funds->where('type', 'loan')->sum('amount');
        $outgoing = $funds->where('type', 'repayment')->sum('amount');

        return view('atk.reports.owner-funds', compact('funds', 'currentBalance', 'incoming', 'outgoing', 'start', 'end', 'status'));
    }

    public function pdf(Request $request)
    {
        $start = $request->get('start_date', now()->subDays(30));
        $end = $request->get('end_date', now());
        $status = $request->get('status');

        if (is_string($start)) {
            $start = \Illuminate\Support\Carbon::parse($start);
        }
        if (is_string($end)) {
            $end = \Illuminate\Support\Carbon::parse($end);
        }

        $query = OwnerFund::orderBy('created_at', 'desc')->whereDate('created_at', '>=', $start)->whereDate('created_at', '<=', $end);
        if ($status) {
            $query->where('status', $status);
        }

        $funds = $query->get(); // Use get() instead of paginate() for PDF

        $latestTransaction = OwnerFund::whereDate('created_at', '<=', $end)->orderBy('created_at', 'desc')->first();
        $currentBalance = $latestTransaction->balance ?? 0;

        $incoming = $funds->where('type', 'loan')->sum('amount');
        $outgoing = $funds->where('type', 'repayment')->sum('amount');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('atk.reports.pdf-owner-funds', compact('funds', 'currentBalance', 'incoming', 'outgoing', 'start', 'end', 'status'))->setPaper('a4', 'portrait');
        return $pdf->download('laporan-dana-talangan-atk.pdf');
    }

    public function excel(Request $request, AtkSpreadsheetService $spreadsheet)
    {
        $start = $request->get('start_date', now()->subDays(30));
        $end = $request->get('end_date', now());
        $status = $request->get('status');
        $format = $request->get('format', 'xlsx');

        if (is_string($start)) {
            $start = Carbon::parse($start);
        }
        if (is_string($end)) {
            $end = Carbon::parse($end);
        }

        $query = OwnerFund::orderBy('created_at', 'desc')->whereDate('created_at', '>=', $start)->whereDate('created_at', '<=', $end);
        if ($status) {
            $query->where('status', $status);
        }

        $funds = $query->get();

        $latestTransaction = OwnerFund::whereDate('created_at', '<=', $end)->orderBy('created_at', 'desc')->first();
        $currentBalance = (float) ($latestTransaction->balance ?? 0);

        $incoming = (float) $funds->where('type', 'loan')->sum('amount');
        $outgoing = (float) $funds->where('type', 'repayment')->sum('amount');

        $headers = ['No', 'Tanggal', 'Kode Transaksi', 'Jenis', 'Jumlah', 'Saldo', 'Keterangan'];
        $rows = [
            ['LAPORAN DANA TALANGAN TOKO ATK', '', '', '', '', '', ''],
            ['Periode', ($start instanceof Carbon ? $start->format('Y-m-d') : $start), 'sampai', ($end instanceof Carbon ? $end->format('Y-m-d') : $end), '', '', ''],
            ['', '', '', '', '', '', ''],
            ['Total Dana Masuk (Loan)', $incoming, '', '', '', '', ''],
            ['Total Pengembalian (Repayment)', $outgoing, '', '', '', '', ''],
            ['Saldo Aktif Saat Ini', $currentBalance, '', '', '', '', ''],
            ['', '', '', '', '', '', ''],
            ['RINCIAN MUTASI DANA TALANGAN', '', '', '', '', '', ''],
            ['No', 'Tanggal', 'Kode Transaksi', 'Jenis', 'Jumlah', 'Saldo', 'Keterangan'],
        ];

        foreach ($funds as $index => $fund) {
            $trxDate = $fund->transaction_date ? Carbon::parse($fund->transaction_date)->format('Y-m-d') : ($fund->created_at?->format('Y-m-d') ?? '-');
            $rows[] = [
                $index + 1,
                $trxDate,
                $fund->transaction_code,
                strtoupper((string) $fund->type),
                (float) $fund->amount,
                (float) $fund->balance,
                $fund->description ?? '',
            ];
        }

        $filename = 'laporan_dana_talangan_atk_' . now()->format('Ymd_His');
        return $spreadsheet->download($filename, $headers, $rows, $format);
    }
}
