@extends('layouts.app')

@section('title', __('Detail Pembayaran Wedding'))

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Pembayaran Wedding</h4>
        <a href="{{ route('wedding.bookings.show', $payment->booking) }}" class="btn btn-outline-secondary">Kembali</a>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div><span class="fw-semibold">Booking:</span> {{ $payment->booking?->booking_number }}</div>
                    <div><span class="fw-semibold">Tipe:</span> <span class="badge bg-secondary">{{ strtoupper($payment->type) }}</span></div>
                    <div><span class="fw-semibold">Jumlah:</span> <span class="fs-5 fw-bold text-success">Rp {{ number_format((int) $payment->amount, 0, ',', '.') }}</span></div>
                    <div><span class="fw-semibold">Status:</span> 
                        @if($payment->status === 'paid')
                            <span class="badge bg-success">Lunas (Paid)</span>
                        @else
                            <span class="badge bg-warning text-dark">{{ ucfirst($payment->status) }}</span>
                        @endif
                    </div>
                    <div><span class="fw-semibold">Paid At:</span> {{ $payment->paid_at?->toDateTimeString() ?? '-' }}</div>

                    @if($payment->status !== 'paid')
                        <div class="mt-4 pt-3 border-top">
                            <label class="form-label small fw-bold text-muted mb-2">Konfirmasi Pembayaran Manual</label>
                            <div class="d-flex gap-2">
                                <form action="{{ route('wedding.payments.mark_paid', $payment) }}" method="POST" onsubmit="return confirm('Konfirmasi pembayaran TUNAI/CASH sebesar Rp {{ number_format((int) $payment->amount, 0, \',\', \'.\') }}? Jurnal kas akan otomatis dibuat.')">
                                    @csrf
                                    <input type="hidden" name="payment_method" value="cash">
                                    <button type="submit" class="btn btn-sm btn-success">
                                        <i class="fa-solid fa-money-bill-wave me-1"></i>Bayar Tunai (Kas)
                                    </button>
                                </form>
                                <form action="{{ route('wedding.payments.mark_paid', $payment) }}" method="POST" onsubmit="return confirm('Konfirmasi pembayaran TRANSFER/BANK sebesar Rp {{ number_format((int) $payment->amount, 0, \',\', \'.\') }}? Jurnal bank akan otomatis dibuat.')">
                                    @csrf
                                    <input type="hidden" name="payment_method" value="bank">
                                    <button type="submit" class="btn btn-sm btn-primary">
                                        <i class="fa-solid fa-building-columns me-1"></i>Bayar Transfer (Bank)
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">QRIS</div>
                <div class="card-body">
                    @if($payment->paymentTransaction?->qr_url)
                        <img src="{{ $payment->paymentTransaction->qr_url }}" alt="QRIS" class="img-fluid">
                        <div class="mt-2 text-muted">Ref: {{ $payment->paymentTransaction->reference_id }}</div>
                    @else
                        <div class="text-muted">QRIS belum tersedia.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

