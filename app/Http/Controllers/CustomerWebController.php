<?php

namespace App\Http\Controllers;

use App\Models\Coordinator;
use App\Models\Customer;
use App\Models\Htb;
use App\Models\Odp;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Reader\CSV\Reader as CSVReader;
use OpenSpout\Reader\XLSX\Reader as XLSXReader;
use OpenSpout\Writer\XLSX\Writer;

class CustomerWebController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:customer.view', only: ['index', 'show', 'export']),
            new Middleware('permission:customer.create', only: ['create', 'store', 'importFile']),
            new Middleware('permission:customer.edit', only: ['edit', 'update']),
            new Middleware('permission:customer.delete', only: ['destroy', 'bulkDestroy']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Customer::with('user');

        // Filter for Coordinator (Pengurus)
        if (! Auth::user()->hasPermission('customer.view')) {
            $coordinator = Coordinator::where('user_id', Auth::id())->first();
            if ($coordinator && $coordinator->region_id) {
                $query->whereHas('odp', function ($q) use ($coordinator) {
                    $q->where('region_id', $coordinator->region_id);
                });
            }
        }

        if ($request->has('search') && $request->input('search') != '') {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhere('onu_serial', 'like', "%{$search}%")
                    ->orWhere('wan_mac', 'like', "%{$search}%");
            });
        }

        if ($request->has('status') && $request->input('status') != '') {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('htb_id') && $request->input('htb_id') != '') {
            $query->where('htb_id', $request->input('htb_id'));
        }

        // Filter by linked user
        if ($request->filled('linked')) {
            if ($request->input('linked') === 'yes') {
                $query->whereNotNull('user_id');
            } elseif ($request->input('linked') === 'no') {
                $query->whereNull('user_id');
            }
        }

        $perPage = $request->input('per_page', 10);
        if ($perPage === 'all') {
            $customers = $query->latest()->customPaginate()->withQueryString();
        } else {
            $customers = $query->latest()->paginate((int) $perPage)->withQueryString();
        }

        $htbs = Htb::orderBy('name')->get();

        return view('customers.index', compact('customers', 'htbs'));
    }

    public function export(Request $request)
    {
        if (! Auth::user()->hasPermission('customer.view')) {
            abort(403);
        }

        $query = Customer::query();

        if ($request->has('search') && $request->input('search') != '') {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhere('onu_serial', 'like', "%{$search}%")
                    ->orWhere('wan_mac', 'like', "%{$search}%");
            });
        }

        if ($request->has('status') && $request->input('status') != '') {
            $query->where('status', $request->input('status'));
        }

        $customers = $query->orderBy('name')->get();

        return response()->streamDownload(function () use ($customers) {
            $writer = new Writer;
            $writer->openToFile('php://output');

            $writer->addRow(Row::fromValues([
                'id',
                'name',
                'address',
                'phone',
                'package',
                'ip_address',
                'vlan',
                'odp',
                'htb',
                'status',
                'pppoe_user',
                'pppoe_password',
                'onu_serial',
                'device_model',
                'ssid_name',
                'ssid_password',
                'latitude',
                'longitude',
            ]));

            foreach ($customers as $customer) {
                $writer->addRow(Row::fromValues([
                    $customer->id,
                    $customer->name,
                    $customer->address,
                    $customer->phone,
                    $customer->package,
                    $customer->ip_address,
                    $customer->vlan,
                    $customer->odp,
                    $customer->htb->name ?? null,
                    $customer->status,
                    $customer->pppoe_user,
                    $customer->pppoe_password,
                    $customer->onu_serial,
                    $customer->device_model,
                    $customer->ssid_name,
                    $customer->ssid_password,
                    $customer->latitude,
                    $customer->longitude,
                ]));
            }

            $writer->close();
        }, 'customers_'.now()->format('Y-m-d_His').'.xlsx');
    }

    public function importFile(Request $request)
    {
        if (! Auth::user()->hasPermission('customer.view')) {
            abort(403);
        }

        // Increase limits for large imports
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        try {
            $request->validate([
                'file' => 'required|file|mimes:xlsx,csv|max:20480',
            ]);

            $file = $request->file('file');
            $extension = strtolower($file->getClientOriginalExtension());

            if ($extension === 'csv') {
                $reader = new CSVReader;
            } else {
                $reader = new XLSXReader;
            }

            $reader->open($file->getRealPath());

            $header = null;
            $created = 0;
            $updated = 0;
            $skipped = 0;
            $failed = 0;
            $errors = [];
            $rowNumber = 0;

            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {
                    $rowNumber++;
                    try {
                        $cells = $row->getCells();
                        $values = [];

                        foreach ($cells as $cell) {
                            $values[] = $cell->getValue();
                        }

                        if ($header === null) {
                            $header = array_map(function ($value) {
                                return strtolower(trim((string) $value));
                            }, $values);
                            if (! in_array('name', $header, true)) {
                                throw new \Exception("Missing 'name' column in header.");
                            }

                            continue;
                        }

                        // Adjust values array to match header length
                        if (count($values) > count($header)) {
                            $values = array_slice($values, 0, count($header));
                        } elseif (count($values) < count($header)) {
                            $values = array_pad($values, count($header), null);
                        }

                        $rowMap = array_combine($header, $values);

                        if (empty($rowMap['name'])) {
                            $skipped++;

                            continue;
                        }

                        // Helper to get value or null
                        $getValue = function ($key) use ($rowMap) {
                            $val = $rowMap[$key] ?? null;
                            if (is_string($val)) {
                                $val = trim($val);
                            }

                            return $val === '' ? null : $val;
                        };

                        // Check for existing customer by ID, Name, or Phone to update
                        $existingCustomer = null;
                        if (! empty($rowMap['id'])) {
                            $existingCustomer = Customer::find($rowMap['id']);
                        } elseif (! empty($rowMap['name'])) {
                            $existingCustomer = Customer::where('name', $rowMap['name'])->first();
                        }

                        // Resolve Relations
                        $odpId = null;
                        $odpName = $getValue('odp');
                        if ($odpName) {
                            $foundOdp = Odp::where('name', $odpName)->first();
                            if ($foundOdp) {
                                $odpId = $foundOdp->id;
                            }
                        }

                        $htbId = null;
                        $htbName = $getValue('htb');
                        if ($htbName) {
                            $foundHtb = Htb::with('odp')->where('name', $htbName)->first();
                            if ($foundHtb) {
                                $htbId = $foundHtb->id;
                                // If HTB is found, it overrides ODP selection for connection logic
                                $odpId = $foundHtb->odp_id;
                                $odpName = $foundHtb->odp->name ?? $odpName;
                            }
                        }

                        $data = [
                            'name' => $rowMap['name'], // Name is required, checked above
                            'address' => $getValue('address'),
                            'phone' => $getValue('phone'),
                            'package' => $getValue('package'),
                            'ip_address' => $getValue('ip_address'),
                            'genieacs_device_id' => $getValue('genieacs_device_id'),
                            'vlan' => $getValue('vlan'),
                            'odp' => $odpName,
                            'odp_id' => $odpId,
                            'htb_id' => $htbId,
                            'status' => strtolower($getValue('status') ?? 'active'),
                            'pppoe_user' => $getValue('pppoe_user'),
                            'pppoe_password' => $getValue('pppoe_password'),
                            'onu_serial' => $getValue('onu_serial'),
                            'device_model' => $getValue('device_model'),
                            'ssid_name' => $getValue('ssid_name'),
                            'ssid_password' => $getValue('ssid_password'),
                            'latitude' => $getValue('latitude'),
                            'longitude' => $getValue('longitude'),
                        ];

                        // If status is empty or invalid, default to active
                        if (! in_array($data['status'], ['active', 'suspend', 'terminated'])) {
                            $data['status'] = 'active';
                        }

                        // Check Unique Constraints manually to avoid SQL crash
                        if ($data['pppoe_user']) {
                            $conflict = Customer::where('pppoe_user', $data['pppoe_user'])
                                ->when($existingCustomer, function ($q) use ($existingCustomer) {
                                    $q->where('id', '!=', $existingCustomer->id);
                                })->exists();
                            if ($conflict) {
                                // Option: Skip this field or Skip row?
                                // Let's skip the field but import the user, adding a warning note?
                                // Or better: Fail the row so user knows to fix it.
                                throw new \Exception("PPPoE User '{$data['pppoe_user']}' already exists.");
                            }
                        }

                        if ($existingCustomer) {
                            $existingCustomer->update($data);
                            $updated++;
                        } else {
                            Customer::create($data);
                            $created++;
                        }
                    } catch (\Throwable $e) {
                        $failed++;
                        // Keep only first 10 errors to avoid huge session data
                        if (count($errors) < 10) {
                            $errors[] = "Row $rowNumber: ".$e->getMessage();
                        } elseif (count($errors) == 10) {
                            $errors[] = '... and more errors.';
                        }
                    }
                }
            }

            $reader->close();

            $message = __('Imported: :created created, :updated updated.', ['created' => $created, 'updated' => $updated]);
            if ($skipped > 0) {
                $message .= ' '.__('Skipped :count rows (empty name).', ['count' => $skipped]);
            }
            if ($failed > 0) {
                $message .= ' '.__('Failed :count rows.', ['count' => $failed]);
            }

            $redirect = redirect()->route('customers.index')->with('success', $message);

            if (! empty($errors)) {
                $redirect->withErrors(['import_errors' => $errors]);
            }

            return $redirect;

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Import Failed: '.$e->getMessage());

            return redirect()->route('customers.index')->withErrors([
                'error' => __('Import failed: :message', ['message' => $e->getMessage()]),
            ]);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $prefill = [
            'onu_serial' => $request->query('onu_serial'),
            'ip_address' => $request->query('ip_address'),
            'name' => $request->query('name'),
            'device_model' => $request->query('device_model'),
            'ssid_name' => $request->query('ssid_name'),
            'ssid_password' => $request->query('ssid_password'),
            'pppoe_user' => $request->query('pppoe_user'),
        ];
        $coordinators = \App\Models\Coordinator::with('region')->get();
        $odps = \App\Models\Odp::all();
        $htbs = \App\Models\Htb::with(['parent', 'odp'])->get();
        $olts = \App\Models\OLT::where('is_active', true)->get();
        $closures = \App\Models\Closure::with('odc')->get();

        $packages = \App\Models\Package::where('is_active', true)->orderBy('name')->get();

        return view('customers.create', compact('prefill', 'coordinators', 'odps', 'htbs', 'olts', 'packages', 'closures'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'package_id' => 'nullable|exists:packages,id',
            'user_id' => 'nullable|exists:users,id|unique:customers,user_id',
            // User account creation fields
            'create_user' => 'nullable|boolean',
            'username' => 'nullable|required_if:create_user,1|string|max:255|unique:users,username',
            'email' => 'nullable|required_if:create_user,1|string|email|max:255|unique:users,email',
            'password' => 'nullable|required_if:create_user,1|string|min:6|confirmed',
            'ip_address' => 'nullable|ip',
            'genieacs_device_id' => 'nullable|string|max:255',
            'vlan' => 'nullable|string|max:20',
            'odp' => 'nullable|string|max:50',
            'odp_id' => 'nullable|exists:odps,id',
            'htb_id' => 'nullable|exists:htbs,id',
            'olt_id' => 'nullable|exists:olts,id',
            'status' => 'required|in:active,suspend,terminated',
            'pppoe_user' => 'nullable|string|unique:customers,pppoe_user',
            'pppoe_password' => 'nullable|string',
            'onu_serial' => 'nullable|string',
            'wan_mac' => 'nullable|string|max:20',
            'device_model' => 'nullable|string|max:100',
            'ssid_name' => 'nullable|string|max:100',
            'ssid_password' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ], [
            'username.required_if' => 'Username wajib diisi jika Buat User Portal dicentang.',
            'email.required_if' => 'Email wajib diisi jika Buat User Portal dicentang.',
            'password.required_if' => 'Password wajib diisi jika Buat User Portal dicentang.',
            'password.min' => 'Password portal pelanggan minimal :min karakter (sekarang 6).',
            'password.confirmed' => 'Konfirmasi Password tidak cocok dengan Password.',
        ]);

        if (! empty($validated['package_id'])) {
            $pkg = \App\Models\Package::find($validated['package_id']);
            if ($pkg) {
                $validated['package'] = $pkg->name;
            }
        }

        if (! empty($validated['htb_id'])) {
            $htb = Htb::with('odp')->find($validated['htb_id']);
            if ($htb) {
                if ($htb->isFull()) {
                    return back()->withInput()->withErrors(['htb_id' => __('Selected HTB is full.')]);
                }
                // Auto-assign ODP from HTB
                $validated['odp_id'] = $htb->odp_id;
                $validated['odp'] = $htb->odp->name;
            }
        }

        if (! empty($validated['odp_id'])) {
            $odp = Odp::find($validated['odp_id']);
            if ($odp && $odp->isFull()) {
                return back()->withInput()->withErrors(['odp_id' => __('Selected ODP (or HTB parent ODP) is full.')]);
            }
            if ($odp) {
                $validated['odp'] = $odp->name;
            }
        }

        try {
            DB::beginTransaction();

            // Handle User creation if requested
            if ($request->boolean('create_user') && ! empty($validated['username']) && ! empty($validated['password'])) {
                $customerRoleId = Role::where('name', 'customer')->value('id');
                $user = User::create([
                    'name' => $validated['name'],
                    'username' => $validated['username'],
                    'email' => $validated['email'] ?? null,
                    'password' => Hash::make($validated['password']),
                    'role_id' => $customerRoleId,
                    'phone' => $validated['phone'],
                    'is_active' => true,
                ]);
                $validated['user_id'] = $user->id;
            }

            Customer::create($validated);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Error creating customer: '.$e->getMessage());

            return back()->withInput()->withErrors(['error' => __('Failed to create customer: :message', ['message' => $e->getMessage()])]);
        }

        return redirect()->route('customers.index')->with('success', __('Customer created successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        $customer->load(['tickets', 'installations', 'olt', 'odp', 'htb']);

        return view('customers.show', compact('customer'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        $coordinators = \App\Models\Coordinator::with('region')->get();
        $odps = \App\Models\Odp::all();
        $htbs = \App\Models\Htb::with(['parent', 'odp'])->get();
        $olts = \App\Models\OLT::where('is_active', true)->get();
        $closures = \App\Models\Closure::with('odc')->get();

        $packages = \App\Models\Package::where('is_active', true)->orderBy('name')->get();

        return view('customers.edit', compact('customer', 'coordinators', 'odps', 'htbs', 'olts', 'packages', 'closures'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'package_id' => 'nullable|exists:packages,id',
            'package' => 'nullable|string|max:100',
            'user_id' => 'nullable|exists:users,id|unique:customers,user_id,'.$customer->id,
            // User account update fields
            'create_user' => 'nullable|boolean',
            'username' => 'nullable|string|max:255|unique:users,username,'.($customer->user_id ?? 'NULL'),
            'email' => 'nullable|string|email|max:255|unique:users,email,'.($customer->user_id ?? 'NULL'),
            'password' => 'nullable|string|min:6|confirmed',
            'ip_address' => 'nullable|ip',
            'genieacs_device_id' => 'nullable|string|max:255',
            'vlan' => 'nullable|string|max:20',
            'odp' => 'nullable|string|max:50',
            'odp_id' => 'nullable|exists:odps,id',
            'htb_id' => 'nullable|exists:htbs,id',
            'olt_id' => 'nullable|exists:olts,id',
            'status' => 'sometimes|required|in:active,suspend,terminated',
            'pppoe_user' => 'nullable|string|unique:customers,pppoe_user,'.$customer->id,
            'pppoe_password' => 'nullable|string',
            'onu_serial' => 'nullable|string',
            'wan_mac' => 'nullable|string|max:20',
            'device_model' => 'nullable|string|max:100',
            'ssid_name' => 'nullable|string|max:100',
            'ssid_password' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ], [
            'password.min' => 'Password portal pelanggan minimal :min karakter (sekarang 6).',
            'password.confirmed' => 'Konfirmasi Password tidak cocok dengan Password.',
        ]);

        if (! empty($validated['package_id'])) {
            $pkg = \App\Models\Package::find($validated['package_id']);
            if ($pkg) {
                $validated['package'] = $pkg->name;
            }
        }

        $oldOdpId = $customer->odp_id;
        $oldHtbId = $customer->htb_id;

        // Handle HTB/ODP logic
        $newHtbId = $validated['htb_id'] ?? null;
        $newOdpId = $validated['odp_id'] ?? null;

        if (! empty($newHtbId)) {
            $newHtb = Htb::with('odp')->find($newHtbId);
            if ($newHtb && $newHtb->isFull() && $newHtb->id !== $oldHtbId) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => __('Selected HTB is full.'),
                        'errors' => ['htb_id' => [__('Selected HTB is full.')]],
                    ], 422);
                }
                return back()->withInput()->withErrors(['htb_id' => __('Selected HTB is full.')]);
            }
            if ($newHtb) {
                $validated['odp_id'] = $newHtb->odp_id;
                $validated['odp'] = $newHtb->odp->name ?? null;
            }
        } elseif (! empty($newOdpId)) {
            $validated['htb_id'] = null;
            $newOdp = Odp::find($newOdpId);
            if ($newOdp && $newOdp->isFull() && $newOdp->id !== $oldOdpId) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => __('Selected ODP is full.'),
                        'errors' => ['odp_id' => [__('Selected ODP is full.')]],
                    ], 422);
                }
                return back()->withInput()->withErrors(['odp_id' => __('Selected ODP is full.')]);
            }
            if ($newOdp) {
                $validated['odp'] = $newOdp->name;
            }
        } else {
            $validated['htb_id'] = null;
            $validated['odp_id'] = null;
            $validated['odp'] = null;
        }

        try {
            DB::beginTransaction();

            // Handle User creation/update
            if ($request->boolean('create_user') || $customer->user_id) {
                $customerRoleId = Role::where('name', 'customer')->value('id');
                $userData = [
                    'name' => $validated['name'] ?? $customer->name,
                    'username' => $validated['username'] ?? ($customer->user?->username),
                    'email' => $validated['email'] ?? ($customer->user?->email),
                    'phone' => $validated['phone'] ?? $customer->phone,
                    'is_active' => true,
                ];

                if (! empty($validated['password'])) {
                    $userData['password'] = Hash::make($validated['password']);
                }

                if ($customer->user_id) {
                    // Only sync account data for real customer-role users.
                    if ((int) ($customer->user?->role_id ?? 0) === (int) $customerRoleId) {
                        $userData['role_id'] = $customerRoleId;
                        $customer->user->update($userData);
                    }
                } elseif ($request->boolean('create_user') && ! empty($validated['username']) && ! empty($validated['password'])) {
                    $userData['role_id'] = $customerRoleId;
                    $user = User::create($userData);
                    $validated['user_id'] = $user->id;
                }
            }

            $customer->update($validated);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Error updating customer: '.$e->getMessage());

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => __('Failed to update customer: :message', ['message' => $e->getMessage()]),
                    'errors' => ['error' => [$e->getMessage()]],
                ], 500);
            }
            return back()->withInput()->withErrors(['error' => __('Failed to update customer: :message', ['message' => $e->getMessage()])]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Customer updated successfully.'),
                'data' => $customer,
            ]);
        }

        return redirect()->route('customers.index')->with('success', __('Customer updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        DB::transaction(function () use ($customer) {
            DB::table('modem_data_records')->where('customer_id', $customer->id)->delete();
            DB::table('installations')->where('customer_id', $customer->id)->delete();
            $customer->delete();
        });

        return redirect()->route('customers.index')->with('success', __('Customer deleted successfully.'));
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids');
        if (empty($ids)) {
            return redirect()->back()->with('error', 'No customers selected.');
        }

        DB::transaction(function () use ($ids) {
            DB::table('modem_data_records')->whereIn('customer_id', $ids)->delete();
            DB::table('installations')->whereIn('customer_id', $ids)->delete();
            Customer::whereIn('id', $ids)->delete();
        });

        return redirect()->back()->with('success', count($ids).' customers deleted successfully.');
    }
}
