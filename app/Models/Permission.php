<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    protected $fillable = ['name', 'label', 'group'];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * Group permissions by their logical tabs for UI display.
     */
    public static function getGroupedPermissions()
    {
        $disabledPrefixes = collect(config('module_gating.permission_prefixes', []))
            ->filter(fn ($p, $module) => ! \App\Support\ModuleGate::isEnabled($module))
            ->flatten()
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (! \App\Support\ModuleGate::isEnabled('isp')) {
            $disabledPrefixes[] = 'customer.';
            $disabledPrefixes[] = 'installation.';
            $disabledPrefixes[] = 'hotspot.';
            $disabledPrefixes[] = 'pppoe.';
            $disabledPrefixes[] = 'modem-data.';
            $disabledPrefixes[] = 'genieacs.';
            $disabledPrefixes[] = 'network-monitor.';
        }
        if (! \App\Support\ModuleGate::isEnabled('ticketing')) {
            $disabledPrefixes[] = 'ticket.';
            $disabledPrefixes[] = 'sla.';
        }
        $disabledPrefixes = array_unique(array_filter($disabledPrefixes));

        $query = self::orderBy('group');
        if (! empty($disabledPrefixes)) {
            $query->where(function ($q) use ($disabledPrefixes) {
                foreach ($disabledPrefixes as $prefix) {
                    $q->where('name', 'not like', $prefix . '%');
                }
            });
        }

        $allPermissions = $query->get()->groupBy('group');

        $tabMap = [
            'Cuci Kendaraan' => ['Car Wash'],
            'Toko ATK' => ['ATK Store'],
            'Keuangan' => ['Finance', 'Investor Management', 'Accounting'],
            'Operasional & SDM' => [
                'Technician Management', 'Attendance', 'Leave Management', 'Schedule Management',
                'Inventory (Alat & Material)', 'Employee Management'
            ],
            'Wedding & Event' => ['Wedding & Event'],
            'CCTV Installation' => ['CCTV Installation'],
            'Sistem' => [
                'User Management', 'Role Management', 'Settings', 'Coordinator Management',
                'Region Management', 'Package Management', 'WhatsApp', 'Telegram', 'Notification', 'Integrasi',
                'Security'
            ],
            'Reporting' => ['Reporting'],
            'Umum' => ['Dashboard', 'Profile'],
        ];

        if (\App\Support\ModuleGate::isEnabled('isp')) {
            $tabMap['Pelanggan & Layanan'] = ['Customer Management', 'Ticket Management', 'Installation Management', 'Service Management'];
            $tabMap['Jaringan'] = [
                'ODC Management', 'ODP Management', 'HTB Management', 'OLT Management', 'Router Management',
                'Closure Management', 'PPPoE Management', 'Hotspot Management', 'Radius', 'Map', 'Network Monitor', 'Utilities',
                'NOC Center'
            ];
        }

        $grouped = [];
        $tabsOrder = [
            'Cuci Kendaraan',
            'Toko ATK',
            'Keuangan',
            'Operasional & SDM',
            'Sistem',
            'Umum',
            'Reporting',
            'Wedding & Event',
            'CCTV Installation',
            'Pelanggan & Layanan',
            'Jaringan',
            'Lainnya',
        ];

        foreach ($allPermissions as $group => $perms) {
            $foundTab = 'Lainnya';
            foreach ($tabMap as $tabName => $groups) {
                if (in_array($group, $groups)) {
                    $foundTab = $tabName;
                    break;
                }
            }
            $grouped[$foundTab][$group] = $perms;
        }

        // Sort by predefined tab order
        uksort($grouped, function($a, $b) use ($tabsOrder) {
            $posA = array_search($a, $tabsOrder);
            $posB = array_search($b, $tabsOrder);
            return ($posA === false ? 999 : $posA) <=> ($posB === false ? 999 : $posB);
        });

        return $grouped;
    }
}
