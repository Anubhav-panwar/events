<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">Admin Dashboard</h2>
    </x-slot>
    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="card p-4">
                    <div class="text-sm text-gray-500">Total Vendors</div>
                    <div class="text-2xl font-bold">{{ $kpis['total_vendors'] }}</div>
                </div>
                <div class="card p-4">
                    <div class="text-sm text-gray-500">Total Events</div>
                    <div class="text-2xl font-bold">{{ $kpis['total_events'] }}</div>
                </div>
                <div class="card p-4">
                    <div class="text-sm text-gray-500">Total Orders</div>
                    <div class="text-2xl font-bold">{{ $kpis['total_orders'] }}</div>
                </div>
                <div class="card p-4">
                    <div class="text-sm text-gray-500">Revenue</div>
                    <div class="text-2xl font-bold">${{ number_format($kpis['revenue'], 2) }}</div>
                </div>
                <div class="card p-4">
                    <div class="text-sm text-gray-500">Active Users</div>
                    <div class="text-2xl font-bold">{{ $kpis['active_users'] }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
