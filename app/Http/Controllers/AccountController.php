<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\JournalEntry;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index(?Request $request = null)
    {
        $request = $request ?? request();
        $type = $request->input('type');
        $search = $request->input('search');

        $query = Account::query();

        if ($type) {
            $query->where('type', $type);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $accounts = $query->orderBy('code')->paginate(20)->withQueryString();

        $accountTypes = [
            'asset' => 'Aset / Aktiva (1-xxxx)',
            'liability' => 'Kewajiban / Hutang (2-xxxx)',
            'equity' => 'Modal / Ekuitas (3-xxxx)',
            'revenue' => 'Pendapatan (4-xxxx)',
            'expense' => 'Beban / Biaya (5-xxxx)',
        ];

        return view('accounting.accounts.index', compact('accounts', 'accountTypes', 'type', 'search'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:accounts,code'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:asset,liability,equity,revenue,expense'],
            'parent_id' => ['nullable', 'exists:accounts,id'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'code.required' => 'Kode akun wajib diisi.',
            'code.unique' => 'Kode akun sudah digunakan.',
            'name.required' => 'Nama akun wajib diisi.',
            'type.required' => 'Tipe akun wajib dipilih.',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        Account::create($validated);

        return redirect()->route('accounting.accounts.index')
            ->with('success', 'Akun berhasil ditambahkan ke Bagan Akun (COA).');
    }

    public function update(Request $request, Account $account)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:accounts,code,' . $account->id],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:asset,liability,equity,revenue,expense'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $account->update($validated);

        return redirect()->route('accounting.accounts.index')
            ->with('success', 'Akun ' . $account->code . ' berhasil diperbarui.');
    }

    public function destroy(Account $account)
    {
        $isUsed = JournalEntry::where('account_id', $account->id)->exists();

        if ($isUsed) {
            return redirect()->route('accounting.accounts.index')
                ->with('error', 'Akun tidak dapat dihapus karena sudah memiliki transaksi jurnal. Anda dapat menonaktifkannya.');
        }

        $account->delete();

        return redirect()->route('accounting.accounts.index')
            ->with('success', 'Akun berhasil dihapus.');
    }
}
