<?php

namespace App\Http\Controllers;

use App\Models\WashMember;
use App\Models\WashMemberCard;
use App\Models\WashMemberLevel;
use App\Models\WashMemberVehicle;
use App\Services\Wash\WashMembershipService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WashMemberController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:wash.member.view', only: ['index', 'show', 'levels', 'cardPdf']),
            new Middleware('permission:wash.member.manage', only: ['store', 'update']),
        ];
    }

    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        $status = trim((string) $request->get('status', ''));
        $level = trim((string) $request->get('level', ''));

        $members = WashMember::query()
            ->with(['level', 'vehicles', 'card'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where('member_number', 'like', '%'.$q.'%')
                    ->orWhere('name', 'like', '%'.$q.'%')
                    ->orWhere('whatsapp', 'like', '%'.$q.'%')
                    ->orWhereHas('vehicles', function ($vehicleQuery) use ($q) {
                        $vehicleQuery->where('vehicle_plate', 'like', '%'.strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $q)).'%');
                    });
            })
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($level !== '', function ($query) use ($level) {
                $query->whereHas('level', fn ($levelQuery) => $levelQuery->where('code', $level));
            })
            ->orderByDesc('last_transaction_at')
            ->orderByDesc('id')
            ->customPaginate()
            ->appends($request->query());

        $levels = WashMemberLevel::query()->where('is_active', true)->orderBy('min_transactions')->get();

        return view('wash.members.index', compact('members', 'levels', 'q', 'status', 'level'));
    }

    public function store(Request $request, WashMembershipService $membershipService)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'whatsapp' => 'required|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:2000',
            'vehicle_plate' => 'nullable|string|max:20',
            'vehicle_brand' => 'nullable|string|max:100',
            'vehicle_type' => 'nullable|string|in:motor,mobil',
            'wash_member_level_id' => 'nullable|exists:wash_member_levels,id',
            'status' => 'nullable|in:active,inactive,blacklist',
        ]);

        $normalizedPhone = $membershipService->normalizePhone($validated['whatsapp']);
        if ($normalizedPhone === '') {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Nomor WhatsApp tidak valid.'], 422);
            }
            return back()->withInput()->with('error', 'Nomor WhatsApp tidak valid.');
        }

        // Check if phone already registered
        $existing = WashMember::where('whatsapp', $normalizedPhone)->first();
        if ($existing) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Nomor WhatsApp ini sudah terdaftar sebagai member: '.$existing->member_number.' ('.$existing->name.').'], 422);
            }
            return back()->withInput()->with('error', 'Nomor WhatsApp ini sudah terdaftar sebagai member: '.$existing->member_number.' ('.$existing->name.').');
        }

        $defaultLevelId = WashMemberLevel::where('is_active', true)->orderBy('min_transactions')->value('id');

        $member = DB::transaction(function () use ($validated, $normalizedPhone, $defaultLevelId, $membershipService) {
            $member = WashMember::create([
                'member_number' => 'TEMP-' . Str::upper(Str::random(10)),
                'name' => trim($validated['name']),
                'whatsapp' => $normalizedPhone,
                'email' => $validated['email'] ?? null,
                'address' => $validated['address'] ?? null,
                'joined_at' => now(),
                'wash_member_level_id' => $validated['wash_member_level_id'] ?? $defaultLevelId,
                'status' => $validated['status'] ?? 'active',
                'total_transactions' => 0,
                'total_visits' => 0,
                'total_spending' => 0,
            ]);

            $member->member_number = sprintf('MBR-%04d', $member->id);
            $member->save();

            if (!empty($validated['vehicle_plate'])) {
                $plate = $membershipService->normalizePlate($validated['vehicle_plate']);
                if ($plate !== '') {
                    WashMemberVehicle::create([
                        'wash_member_id' => $member->id,
                        'vehicle_plate' => $plate,
                        'vehicle_brand' => $validated['vehicle_brand'] ?? null,
                        'vehicle_type' => $validated['vehicle_type'] ?? 'mobil',
                        'is_primary' => true,
                    ]);
                }
            }

            $membershipService->ensureMemberCard($member);

            return $member;
        });

        if ($request->wantsJson()) {
            $member->load(['level', 'vehicles']);
            return response()->json([
                'success' => true,
                'message' => 'Member baru berhasil didaftarkan.',
                'member' => [
                    'id' => $member->id,
                    'name' => $member->name,
                    'member_number' => $member->member_number,
                    'whatsapp' => $member->whatsapp,
                    'level_code' => $member->level?->code ?? 'bronze',
                    'level_name' => $member->level?->name ?? 'Bronze Member',
                    'discount_percent' => (float) ($member->level?->discount_percent ?? 0),
                    'total_visits' => 0,
                    'total_transactions' => 0,
                    'vehicles' => $member->vehicles->map(fn($v) => [
                        'plate' => $v->vehicle_plate,
                        'brand' => $v->vehicle_brand,
                        'type' => $v->vehicle_type,
                    ])->values()->all(),
                ]
            ]);
        }

        return redirect()->route('wash.members.show', $member)->with('success', 'Member baru berhasil didaftarkan: ' . $member->member_number . ' (' . $member->name . ')');
    }

    public function show(WashMember $member, WashMembershipService $membershipService)
    {
        $member->load(['level', 'vehicles', 'card', 'transactions' => fn ($q) => $q->latest()->limit(10)]);
        $card = $membershipService->ensureMemberCard($member);
        $verificationUrl = $membershipService->memberVerificationUrl($card);
        $qrUrl = 'https://quickchart.io/qr?size=220&text='.urlencode($verificationUrl);

        return view('wash.members.show', compact('member', 'card', 'verificationUrl', 'qrUrl'));
    }

    public function update(Request $request, WashMember $member)
    {
        $validated = $request->validate([
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:2000',
            'status' => 'required|in:active,inactive,blacklist',
        ]);

        $member->update($validated);

        return redirect()
            ->route('wash.members.show', $member)
            ->with('success', 'Data member berhasil diperbarui.');
    }

    public function levels()
    {
        $levels = WashMemberLevel::query()->where('is_active', true)->orderBy('min_transactions')->get();

        return view('wash.members.levels', compact('levels'));
    }

    public function cardPdf(WashMember $member, WashMembershipService $membershipService)
    {
        $member->load(['level', 'vehicles', 'card']);
        $card = $membershipService->ensureMemberCard($member);
        $verificationUrl = $membershipService->memberVerificationUrl($card);
        $qrUrl = 'https://quickchart.io/qr?size=220&text='.urlencode($verificationUrl);

        $pdf = Pdf::loadView('wash.members.card-pdf', compact('member', 'card', 'verificationUrl', 'qrUrl'))
            ->setPaper('a4', 'portrait')
            ->setOption(['isRemoteEnabled' => true]);

        return $pdf->download('member-card-'.$member->member_number.'.pdf');
    }

    public function verify(string $token)
    {
        $card = WashMemberCard::query()
            ->with(['member.level', 'member.vehicles'])
            ->where('verification_token', $token)
            ->firstOrFail();

        $member = $card->member;

        return view('wash.members.verify', compact('card', 'member'));
    }
}

