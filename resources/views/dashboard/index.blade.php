<x-dashboard-layout :title="'Dashboard ' . (auth()->user()->role instanceof \App\Enums\UserRole ? auth()->user()->role->label() : ucfirst((string) (auth()->user()->role ?? 'User')))" breadcrumb="Ringkasan">
    <div class="space-y-8">
        @if($role === 'customer')
            @include('dashboard.partials.customer')
        @elseif($role === 'mekanik')
            @include('dashboard.partials.mekanik')
        @elseif($role === 'sales')
            @include('dashboard.partials.sales')
        @elseif($role === 'super_admin')
            @include('dashboard.partials.super_admin')
        @elseif($role === 'owner')
            @include('dashboard.partials.owner')
        @endif
    </div>
</x-dashboard-layout>
