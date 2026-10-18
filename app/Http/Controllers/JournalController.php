<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Company;
use App\Models\Journal;
use App\Models\JournalEntry;
use App\Models\Period;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class JournalController extends Controller
{
    public function index(Request $request)
    {
        $start = $request->input('start_date');
        $end = $request->input('end_date');
        $search = $request->input('search');

        $query = Journal::query()->with(['entries.account']);

        if ($start) {
            $query->whereDate('date', '>=', $start);
        }
        if ($end) {
            $query->whereDate('date', '<=', $end);
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('journal_no', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $journals = $query->orderBy('date', 'desc')->orderBy('id', 'desc')->paginate(15)->withQueryString();

        return view('accounting.journals.index', compact('journals', 'start', 'end', 'search'));
    }

    public function create()
    {
        $accounts = Account::where('is_active', true)->orderBy('code')->get();
        $openPeriod = Period::where('status', 'open')->latest('start_date')->first();
        $defaultNo = 'JU-' . date('Ymd') . '-' . strtoupper(Str::random(4));

        return view('accounting.journals.create', compact('accounts', 'openPeriod', 'defaultNo'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => ['required', 'date'],
            'journal_no' => ['required', 'string', 'max:50', 'unique:journals,journal_no'],
            'description' => ['required', 'string', 'max:500'],
            'entries' => ['required', 'array', 'min:2'],
            'entries.*.account_id' => ['required', 'exists:accounts,id'],
            'entries.*.debit' => ['required', 'numeric', 'min:0'],
            'entries.*.credit' => ['required', 'numeric', 'min:0'],
            'entries.*.memo' => ['nullable', 'string', 'max:255'],
        ], [
            'entries.min' => 'Jurnal minimal harus memiliki 2 baris (Debit & Kredit berpasangan).',
            'journal_no.unique' => 'Nomor jurnal sudah digunakan.',
        ]);

        $entries = $request->input('entries', []);
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($entries as $entry) {
            $totalDebit += (float) ($entry['debit'] ?? 0);
            $totalCredit += (float) ($entry['credit'] ?? 0);
        }

        if (abs($totalDebit - $totalCredit) > 0.01) {
            return back()->withInput()->with('error', 'Jurnal tidak seimbang (Unbalanced)! Total Debit: Rp ' . number_format($totalDebit, 0, ',', '.') . ' vs Total Kredit: Rp ' . number_format($totalCredit, 0, ',', '.'));
        }

        if ($totalDebit <= 0) {
            return back()->withInput()->with('error', 'Total jurnal tidak boleh nol.');
        }

        $activePeriod = Period::where('status', 'open')
            ->whereDate('start_date', '<=', $request->input('date'))
            ->whereDate('end_date', '>=', $request->input('date'))
            ->first();

        if (! $activePeriod) {
            $activePeriod = Period::where('status', 'open')->latest('start_date')->first();
        }

        $defaultCompanyId = Company::where('is_active', true)->value('id');

        DB::transaction(function () use ($request, $entries, $activePeriod, $defaultCompanyId) {
            $journal = Journal::create([
                'company_id' => $defaultCompanyId,
                'journal_no' => $request->input('journal_no'),
                'date' => $request->input('date'),
                'description' => $request->input('description'),
                'source_type' => 'manual',
                'period_id' => $activePeriod?->id,
                'posted_by' => Auth::id(),
                'posted_at' => now(),
                'status' => 'posted',
            ]);

            foreach ($entries as $entry) {
                if ((float)$entry['debit'] > 0 || (float)$entry['credit'] > 0) {
                    JournalEntry::create([
                        'company_id' => $defaultCompanyId,
                        'journal_id' => $journal->id,
                        'account_id' => $entry['account_id'],
                        'debit' => (float) $entry['debit'],
                        'credit' => (float) $entry['credit'],
                        'memo' => $entry['memo'] ?? null,
                    ]);
                }
            }
        });

        return redirect()->route('accounting.journals.index')
            ->with('success', 'Jurnal Umum ' . $request->input('journal_no') . ' berhasil diposting.');
    }

    public function show(Journal $journal)
    {
        $journal->load(['entries.account']);
        return view('accounting.journals.show', compact('journal'));
    }
}
