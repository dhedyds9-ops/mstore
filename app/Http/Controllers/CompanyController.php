<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\CompanyBranch;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class CompanyController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:company.view', only: ['index', 'show']),
            new Middleware('permission:company.manage', only: ['create', 'store', 'edit', 'update', 'destroy']),
        ];
    }

    public function index()
    {
        $companies = Company::with('branches')->get();
        return view('company.index', compact('companies'));
    }

    public function create()
    {
        return view('company.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:companies,code',
            'tax_id' => 'nullable|string|max:100',
            'currency' => 'nullable|string|max:10',
            'country' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'subscription_plan' => 'nullable|string|in:starter,service,pro,enterprise',
            'subscription_status' => 'nullable|string|in:trial,active,expired',
            'trial_ends_at' => 'nullable|date',
            'subscription_ends_at' => 'nullable|date',
            'enabled_modules' => 'nullable|array',
            'max_branches' => 'nullable|integer|min:1',
            'max_users' => 'nullable|integer|min:1',
        ]);

        // Set default values
        $validated['currency'] = $validated['currency'] ?? 'IDR';
        $validated['country'] = $validated['country'] ?? 'ID';
        $validated['is_active'] = true;
        $validated['subscription_plan'] = $validated['subscription_plan'] ?? Company::PLAN_STARTER;
        $validated['subscription_status'] = $validated['subscription_status'] ?? Company::STATUS_TRIAL;
        $validated['trial_ends_at'] = $validated['trial_ends_at'] ?? now()->addDays(14);
        $validated['max_branches'] = $validated['max_branches'] ?? 1;
        $validated['max_users'] = $validated['max_users'] ?? 5;

        Company::create($validated);

        return redirect()->route('companies.index')->with('success', 'Perusahaan berhasil dibuat');
    }

    public function show(Company $company)
    {
        return view('company.show', compact('company'));
    }

    public function edit(Company $company)
    {
        return view('company.edit', compact('company'));
    }

    public function update(Request $request, Company $company)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:companies,code,'.$company->id,
            'tax_id' => 'nullable|string|max:100',
            'currency' => 'nullable|string|max:10',
            'country' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'subscription_plan' => 'nullable|string|in:starter,service,pro,enterprise',
            'subscription_status' => 'nullable|string|in:trial,active,expired',
            'trial_ends_at' => 'nullable|date',
            'subscription_ends_at' => 'nullable|date',
            'enabled_modules' => 'nullable|array',
            'max_branches' => 'nullable|integer|min:1',
            'max_users' => 'nullable|integer|min:1',
        ]);

        // Set is_active to false if not present
        $validated['is_active'] = $request->has('is_active');

        // Jika modul custom tidak diisi/dikosongkan, set null agar mengikuti paket
        if (!isset($validated['enabled_modules']) || empty($validated['enabled_modules'])) {
            $validated['enabled_modules'] = null;
        }

        $company->update($validated);

        return redirect()->route('companies.index')->with('success', 'Perusahaan & paket langganan berhasil diperbarui');
    }

    public function destroy(Company $company)
    {
        $company->delete();
        return redirect()->route('companies.index')->with('success', 'Perusahaan berhasil dihapus');
    }
}