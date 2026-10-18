<?php

namespace App\Http\Controllers;

use App\Models\WeddingBooking;
use App\Models\WeddingPayment;
use App\Services\AuditLogService;
use App\Services\Wedding\WeddingPaymentService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class WeddingPaymentController extends Controller implements HasMiddleware
{
    public function __construct(
        private readonly WeddingPaymentService $paymentService,
        private readonly AuditLogService $auditLogService,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:wedding.payment'),
        ];
    }

    public function index(Request $request)
    {
        $payments = WeddingPayment::query()
            ->with(['booking.package', 'paymentTransaction'])
            ->latest()
            ->customPaginate();

        return view('wedding.payments.index', compact('payments'));
    }

    public function create(WeddingBooking $booking)
    {
        $booking->loadMissing('package');
        return view('wedding.payments.create', compact('booking'));
    }

    public function store(Request $request, WeddingBooking $booking)
    {
        $booking->loadMissing('package');

        $validated = $request->validate([
            'type' => ['required', 'in:dp,final'],
            'amount' => ['nullable', 'integer', 'min:1000'],
            'email' => ['nullable', 'email', 'max:255'],
        ]);

        $type = (string) $validated['type'];
        $amount = $validated['amount'] ?? null;

        if ($amount === null) {
            if ($type === 'dp') {
                $base = (int) ($booking->quotation_amount ?? $booking->package?->price ?? 0);
                $amount = (int) round($base * 0.3);
            } else {
                $amount = (int) ($booking->quotation_amount ?? $booking->package?->price ?? 0);
            }
        }

        $transaction = $this->paymentService->createQris(
            booking: $booking,
            type: $type,
            amount: (int) $amount,
            customerName: $booking->customer_name,
            phoneNumber: $booking->customer_whatsapp,
            email: $validated['email'] ?? null,
        );

        $this->auditLogService->logAction('wedding.payment.created', $transaction->paymentable, [], $transaction->paymentable->toArray());

        return redirect()->route('wedding.payments.show', $transaction->paymentable)->with('success', 'QRIS berhasil dibuat.');
    }

    public function show(WeddingPayment $payment)
    {
        $payment->loadMissing(['booking.package', 'paymentTransaction']);
        return view('wedding.payments.show', compact('payment'));
    }

    public function markPaidManual(Request $request, WeddingPayment $payment)
    {
        if ($payment->status === 'paid') {
            return back()->with('info', 'Pembayaran sudah lunas sebelumnya.');
        }

        $method = $request->input('payment_method', 'cash'); // cash or bank
        $payment->loadMissing('booking');

        $old = $payment->toArray();
        $payment->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);
        $this->auditLogService->logAction('wedding.payment.paid_manual', $payment, $old, $payment->toArray());

        $booking = $payment->booking;
        if ($booking) {
            $oldBooking = $booking->toArray();
            if ($payment->type === 'dp') {
                $booking->update([
                    'status' => 'dp',
                    'dp_amount' => $booking->dp_amount ?? $payment->amount,
                ]);
                $this->auditLogService->logAction('wedding.booking.dp_paid', $booking, $oldBooking, $booking->toArray());
            } else {
                $booking->update([
                    'status' => 'confirmed',
                    'confirmed_at' => $booking->confirmed_at ?? now(),
                ]);
                $this->auditLogService->logAction('wedding.booking.confirmed', $booking, $oldBooking, $booking->toArray());
            }

            \App\Models\Transaction::create([
                'user_id' => auth()->id(),
                'type' => 'income',
                'category' => 'Wedding & Event',
                'amount' => (string) $payment->amount,
                'description' => 'Wedding payment '.$payment->type.' - '.$booking->booking_number,
                'transaction_date' => now()->toDateString(),
                'reference_number' => 'WEDPAY-'.$payment->id,
            ]);

            // Auto-journal to General Ledger
            try {
                $debitCode = $method === 'bank' ? '1002' : '1001';
                $debitAccId = \App\Models\Account::where('code', $debitCode)->value('id');
                $revAccId = \App\Models\Account::where('code', '4006')->value('id');
                if ($debitAccId && $revAccId && (float) $payment->amount > 0) {
                    $poster = app(\App\Services\AccountingPoster::class);
                    $poster->post(
                        'WED-' . $payment->id . '-' . date('Ymd'),
                        now()->toDateString(),
                        'Wedding Payment Manual (' . strtoupper($method) . ') ' . strtoupper($payment->type) . ' - ' . $booking->booking_number,
                        [
                            ['account_id' => $debitAccId, 'debit' => (float) $payment->amount, 'credit' => 0, 'unit' => 'EVENT'],
                            ['account_id' => $revAccId, 'debit' => 0, 'credit' => (float) $payment->amount, 'unit' => 'EVENT'],
                        ],
                        null,
                        WeddingPayment::class,
                        $payment->id
                    );

                    if ($method === 'cash') {
                        $cash = \App\Models\Cash::firstOrCreate(['name' => 'Kas Utama'], ['balance' => 0]);
                        $cash->balance = (float) $cash->balance + (float) $payment->amount;
                        $cash->save();
                    }
                }
            } catch (\Throwable $jEx) {
                \Illuminate\Support\Facades\Log::warning('Manual wedding payment journal error: ' . $jEx->getMessage());
            }
        }

        return back()->with('success', 'Pembayaran berhasil dikonfirmasi lunas dan dibukukan ke Akuntansi.');
    }
}

