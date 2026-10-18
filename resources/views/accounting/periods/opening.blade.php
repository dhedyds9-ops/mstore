@extends('layouts.app')
@section('title', 'Saldo Awal - '.$period->name)
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="fas fa-book-open text-primary me-2"></i>Saldo Awal — Periode {{ $period->name }}</h4>
            <p class="text-secondary small mb-0">Input saldo awal akun neraca untuk memulai pencatatan periode pembukuan ini.</p>
        </div>
        <div>
            <a href="{{ route('accounting.periods.index') }}" class="btn btn-outline-secondary btn-sm shadow-sm">
                <i class="fas fa-arrow-left me-1"></i> Kembali ke Periode
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-3 d-flex align-items-center mb-4">
            <i class="fas fa-check-circle me-2"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm rounded-3 d-flex align-items-center mb-4">
            <i class="fas fa-exclamation-circle me-2"></i>
            <div>{{ session('error') }}</div>
        </div>
    @endif

    <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
        <div class="card-header bg-white py-3 border-0">
            <h6 class="m-0 fw-bold text-dark"><i class="fas fa-list-ol text-primary me-2"></i>Entri Akun Saldo Awal</h6>
        </div>
        <div class="card-body p-0">
            <form method="post" action="{{ route('accounting.periods.opening.post', $period) }}">
                @csrf
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4 small text-uppercase text-body-secondary fw-semibold" style="width:45%">Akun Neraca</th>
                                <th class="small text-uppercase text-body-secondary fw-semibold" style="width:25%">Debit (Rp)</th>
                                <th class="pe-4 small text-uppercase text-body-secondary fw-semibold" style="width:25%">Kredit (Rp)</th>
                            </tr>
                        </thead>
                        <tbody id="rows-body">
                            @for($i=0; $i<10; $i++)
                            <tr>
                                <td class="ps-4">
                                    <select name="rows[{{ $i }}][account_id]" class="form-select form-select-sm">
                                        <option value="">-- Pilih Akun --</option>
                                        @foreach($accounts as $a)
                                        <option value="{{ $a->id }}">{{ $a->code }} - {{ $a->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="rows[{{ $i }}][debit]" class="form-control form-control-sm font-monospace" placeholder="0">
                                </td>
                                <td class="pe-4">
                                    <input type="number" step="0.01" name="rows[{{ $i }}][credit]" class="form-control form-control-sm font-monospace" placeholder="0">
                                </td>
                            </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>
                <div class="p-3 bg-light border-top d-flex justify-content-end gap-2">
                    <a href="{{ route('accounting.periods.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-times me-1"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-primary btn-sm shadow-sm">
                        <i class="fas fa-save me-1"></i> Posting Saldo Awal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
