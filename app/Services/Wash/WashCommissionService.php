<?php

namespace App\Services\Wash;

use App\Models\Setting;
use App\Models\WashCommissionEarning;
use App\Models\WashEmployee;
use App\Models\WashService;
use App\Models\WashTransaction;
use App\Models\WashTransactionItem;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WashCommissionService
{
    public function __construct(
        protected AuditLogService $auditLog,
    ) {}

    protected function hasCommissionTable(): bool
    {
        static $cached = null;
        if ($cached !== null) {
            return $cached;
        }
        try {
            $cached = Schema::hasTable('wash_commission_earnings');
        } catch (\Throwable) {
            $cached = false;
        }
        return (bool) $cached;
    }

    public function setting(string $key, $default = null)
    {
        try {
            $val = Setting::where('key', $key)->value('value');
        } catch (\Throwable) {
            $val = null;
        }
        if ($val === null || $val === '') {
            return $default;
        }
        return $val;
    }

    public function defaultRates(): array
    {
        return [
            'car_small_medium'    => 0,
            'car_large_xlarge'    => 0,
            'motor_small_medium'  => 0,
            'motor_large_xlarge'  => 0,
        ];
    }

    public function isExcludeFreeWash(): bool
    {
        return (bool) ((int) $this->setting('wash_commission_exclude_free_wash', 1) ?: 0);
    }

    public function isOnlyMainServices(): bool
    {
        return (bool) ((int) $this->setting('wash_commission_only_main_services', 1) ?: 0);
    }

    public function isRequireEmployee(): bool
    {
        return (bool) ((int) $this->setting('wash_commission_require_employee', 0) ?: 0);
    }

    public function resolveRateForService(WashService $service = null, ?string $vehicleType = null, ?string $sizeTier = null): int
    {
        return 0;
    }

    public function shouldSkipItem(WashTransaction $transaction, WashTransactionItem $item, ?WashService $service = null): bool
    {
        return true;
    }

    public function calculateAndStoreForTransaction(WashTransaction $transaction): array
    {
        return [
            'success' => true,
            'total_commission' => 0,
            'created_count' => 0,
            'skipped_count' => 0,
            'missing_employee_count' => 0,
            'message' => 'Wash commission disabled',
        ];
    }

    public function voidForTransaction(WashTransaction $transaction, string $reason = 'transaction_updated'): int
    {
        if (! $this->hasCommissionTable()) {
            return 0;
        }
        $txId = $transaction->id;
        if (! $txId) {
            return 0;
        }

        $affected = WashCommissionEarning::query()
            ->where('wash_transaction_id', $txId)
            ->whereIn('status', [WashCommissionEarning::STATUS_EARNED])
            ->update([
                'status' => WashCommissionEarning::STATUS_VOIDED,
                'notes' => (string) $reason . (empty($transaction->status) ? '' : ' status=' . $transaction->status),
            ]);

        if ($affected > 0) {
            try {
                $this->auditLog->logAction('wash_commission.voided', $transaction, [
                    'transaction_id' => $txId,
                    'reason' => $reason,
                    'voided_count' => $affected,
                ]);
            } catch (\Throwable) {}
        }

        return $affected;
    }

    public function recalcForTransaction(WashTransaction $transaction): array
    {
        return $this->calculateAndStoreForTransaction($transaction);
    }

    public function summaryForEmployee(WashEmployee|int $employee, ?string $startDate = null, ?string $endDate = null, array $statusFilter = null): array
    {
        return [
            'rows' => collect(),
            'count' => 0,
            'total' => 0,
        ];
    }
}
