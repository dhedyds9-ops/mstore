<?php

/*
|--------------------------------------------------------------------------
| Module Gating (Single Source of Truth)
|--------------------------------------------------------------------------
| Aplikasi ini difokuskan hanya untuk modul yang terdaftar pada "enabled".
| Modul di luar daftar tersebut (ISP/RADIUS/MikroTik/OLT, Tiket, CCTV,
| Wedding, Messenger/AI, Reporting NOC, Portal Pelanggan) otomatis:
|   1. Disembunyikan dari sidebar (lihat "sidebar_sections" & "sidebar_items").
|   2. Diblokir aksesnya oleh middleware EnsureModuleEnabled (lihat "route_prefixes").
|   3. Permission-nya dicabut dari seluruh role (lihat "permission_prefixes") —
|      dieksekusi via `php artisan modules:prune-isp`.
|
| Untuk mengaktifkan kembali sebuah modul cukup tambahkan namanya ke "enabled".
*/

return [

    // Modul yang aktif dipakai aplikasi.
    'enabled' => [
        'core',       // Dashboard, Pengaturan, User & Role, Audit, Payment Gateway
        'finance',    // Finance & Accounting
        'atk',        // POS ATK & Toko
        'pos',        // POS Retail
        'wash',       // GT Wash
        'wedding',    // Wedding & Event
        'cctv',       // Solusi CCTV
        'hr',         // Manajemen SDM & Karyawan
        'inventory',  // Inventaris & Stok
        'messaging',  // WhatsApp & Telegram Gateway, Bot, Chat
    ],

    // Section sidebar yang dimatikan total bila modulnya tidak aktif.
    'sidebar_sections' => [
        'customer-center'    => 'isp',
        'network-operations' => 'isp',
        'ticketing'          => 'ticketing',
        'communication-ai'   => 'messaging',
        'customer-portal'    => 'customer_portal',
        'hr-asset'           => 'hr',
    ],

    // Item/group di dalam section campuran yang harus disembunyikan.
    'sidebar_items' => [
        'dashboard-noc'       => 'isp',
        'dashboard-hrd'       => 'hr',
        'reporting-center'    => 'reporting',
        'hr'                  => 'hr',
        'employees'           => 'hr',
        'schedule'            => 'hr',
        'leave-my'            => 'hr',
        'leave-manage'        => 'hr',
        'kasbon'              => 'hr',
        'payslip'             => 'hr',
        'asset'               => 'inventory',
        'inventory'           => 'inventory',
        'my-assets'           => 'inventory',
        'pickup'              => 'inventory',
        'bu-cctv'             => 'cctv',
        'sys-whatsapp'        => 'messaging',
        'sys-telegram'        => 'messaging',
    ],

    // Prefix nama route (name) yang diblokir bila modulnya tidak aktif.
    'route_prefixes' => [
        'isp' => [
            'noc', 'olt', 'odcs', 'odps', 'closures', 'htbs', 'routers',
            'vpn', 'hotspot', 'pppoe', 'vouchers', 'voucher', 'map',
            'genieacs', 'network', 'network-analyzer', 'calculator',
            'packages', 'modem-data', 'isp', 'installations', 'client',
            'api.network', 'api.installations',
        ],
        'ticketing' => [
            'tickets', 'sla', 'escalation', 'api.tickets',
        ],
        'messaging' => [
            'chat', 'ai', 'telegram',
        ],
        'wedding' => [
            'wedding', 'reports.wedding',
        ],
        'cctv' => [
            'cctv', 'reports.cctv',
        ],
        'reporting' => [
            'reports',
        ],
        'hr' => [
            'employees', 'schedules', 'employee.leave-requests',
            'admin.leave-requests', 'technicians.kasbon', 'technicians',
            'admin.dashboard',
        ],
        'inventory' => [
            'inventory',
        ],
    ],

    // Prefix path URL (tanpa leading slash) yang diblokir bila modulnya tidak aktif.
    // Dipakai untuk menutup route yang tidak memiliki nama (->name()).
    'path_prefixes' => [
        'isp' => [
            'noc', 'olt', 'odcs', 'odps', 'closures', 'htbs', 'routers',
            'vpn', 'hotspot', 'pppoe', 'vouchers', 'voucher-payment', 'map',
            'genieacs', 'network', 'networks', 'odc', 'odp', 'htb', 'closure',
            'packages', 'modem-data', 'isp', 'installations', 'client',
        ],
        'ticketing' => [
            'tickets', 'sla', 'escalation',
        ],
        'messaging' => [
            'chat', 'ai', 'telegram',
        ],
        'wedding' => [
            'wedding', 'reports/wedding',
        ],
        'cctv' => [
            'cctv', 'reports/cctv',
        ],
        'hr' => [
            'employees', 'schedules', 'employee/leave-requests',
            'admin/leave-requests', 'technicians', 'admin/dashboard',
        ],
        'inventory' => [
            'inventory',
        ],
    ],

    // Prefix nama permission yang dicabut dari semua role saat prune.
    'permission_prefixes' => [
        'isp' => [
            'olt.', 'odc.', 'odp.', 'closure.', 'htb.', 'router.', 'vpn.',
            'hotspot.', 'pppoe.', 'package.', 'voucher.', 'map.',
            'genieacs', 'network-monitor.', 'noc.', 'modem-data.',
            'installation.', 'technician.view',
        ],
        'ticketing' => [
            'ticket.', 'sla.',
        ],
        'messaging' => [
            'chat.', 'ai.', 'whatsapp.', 'telegram.',
        ],
        'reporting' => [
            'report.noc.', 'report.whatsapp.', 'report.sla.',
        ],
        'wedding' => [
            'wedding.',
        ],
        'cctv' => [
            'cctv.',
        ],
        'hr' => [
            'employee.', 'schedule.', 'leave.', 'kasbon.', 'admin.dashboard.view',
        ],
        'inventory' => [
            'inventory.',
        ],
    ],

    // Tabel data yang dibersihkan oleh `php artisan modules:prune-isp --force`.
    // Tabel yang tidak ada akan dilewati. "customers"/"users" sengaja TIDAK
    // dihapus karena dipakai lintas modul (kasir & auth).
    'cleanup_tables' => [
        // RADIUS / MixRadius
        'radacct', 'radcheck', 'radgroupcheck', 'radgroupreply', 'radreply',
        'radusergroup', 'nas',
        // Router / Jaringan
        'routers', 'pppoe_profiles', 'hotspot_profiles', 'vpn_accounts',
        'vpn_servers', 'bandwidths', 'devices', 'polling_logs',
        // OLT / PON
        'olt_ports', 'onts', 'onus', 'odcs', 'odps', 'closures', 'htbs',
        'olt', 'fiber_plans', 'ont_optical_histories', 'ont_traffic_histories',
        // GenieACS / TR-069
        'genieacs_servers', 'genieacs_device_settings', 'genie_device_statuses',
        // Modem & NOC
        'modem_data_records', 'network_incidents', 'network_diagnostics',
        'noc_metric_snapshots',
        // Tiket & SLA
        'ticket_user', 'ticket_logs', 'technician_assignments',
        'tickets', 'sla_rules', 'sla_breaches',
        // Instalasi
        'installations',
        // HR (Leave, Kasbon, etc - technician_attendances & technician_schedules KEPT for retail cashiers & wash employees)
        'leave_requests', 'kasbon_loans', 'kasbon_installments', 'salary_adjustments',
        'schedule_periods', 'public_holidays', 'employees',
        // Inventory & Assets
        'inventory_transactions', 'inventory_items', 'assets', 'asset_categories',
    ],
];
