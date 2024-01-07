@php
    $tenant      = tenant();
    $tenant_name = $tenant->name ?? 'Nexus Tenant';
    $brand       = $tenant?->brands->where('panel','manage')->first();
    $logo        = $brand?->logo();
@endphp

@if($logo)
    <img src="{{ $logo }}" alt="{{ $tenant_name }} Logo" style="height: 3em; width: 100%; padding: 0.3em"/>
@else
    <div class="fi-logo inline-flex text-xl font-bold leading-5 tracking-tight text-gray-950 dark:text-white mb-4">
        {{ $tenant_name }}
    </div>
@endif
