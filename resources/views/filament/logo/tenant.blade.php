@php
    /**
     * @var \App\Models\Tenant $tenant
     * @var \App\Models\Brand|null $brand
     */
    $logo = $brand?->logo();
@endphp

@if($logo)
    <img src="{{ $logo }}" alt="{{ $tenant->name }} Logo" style="height: 3em; width: 100%; padding: 0.3em"/>
@else
    <div class="fi-logo inline-flex text-xl font-bold leading-5 tracking-tight text-gray-950 dark:text-white mb-4">
        {{ $tenant->name }}
    </div>
@endif
