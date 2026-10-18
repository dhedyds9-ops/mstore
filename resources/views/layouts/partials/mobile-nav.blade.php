<div class="mobile-bottom-nav d-lg-none d-print-none">
    {{-- Dashboard / Home --}}
    <a href="{{ route('dashboard') }}" class="mbn-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <div class="mbn-icon"><i class="fa-solid fa-chart-line"></i></div>
        <span class="mbn-label">{{ __('Dash') }}</span>
    </a>

    @if(\App\Support\ModuleGate::isEnabled('atk') && (Auth::user()->isSuperAdmin() || Auth::user()->hasPermission('atk.pos')))
    <a href="{{ route('atk.pos') }}" class="mbn-item {{ request()->routeIs('atk.pos') ? 'active' : '' }}">
        <div class="mbn-icon"><i class="fa-solid fa-store"></i></div>
        <span class="mbn-label">{{ __('Toko') }}</span>
    </a>
    @endif

    @if(\App\Support\ModuleGate::isEnabled('wash') && (Auth::user()->isSuperAdmin() || Auth::user()->hasPermission('wash.pos')))
    <a href="{{ route('wash.pos') }}" class="mbn-item {{ request()->routeIs('wash.pos') ? 'active' : '' }}">
        <div class="mbn-icon"><i class="fa-solid fa-hand-holding-heart"></i></div>
        <span class="mbn-label">{{ __('Jasa') }}</span>
    </a>
    @endif

    @if(\App\Support\ModuleGate::isEnabled('finance') && (Auth::user()->isSuperAdmin() || Auth::user()->hasPermission('finance.view')))
    <a href="{{ route('finance.index') }}" class="mbn-item {{ request()->routeIs('finance.*') ? 'active' : '' }}">
        <div class="mbn-icon"><i class="fa-solid fa-file-invoice-dollar"></i></div>
        <span class="mbn-label">{{ __('Keu') }}</span>
    </a>
    @endif

    {{-- More Menu Toggle (Sidebar) --}}
    <a href="#" class="mbn-item" id="mobileMenuToggle">
        <div class="mbn-icon"><i class="fa-solid fa-bars"></i></div>
        <span class="mbn-label">{{ __('Menu') }}</span>
    </a>
</div>
