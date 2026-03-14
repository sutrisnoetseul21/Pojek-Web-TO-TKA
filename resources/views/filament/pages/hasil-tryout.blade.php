<x-filament-panels::page>
    <form wire:submit="submit">
        {{ $this->form }}
    </form>

    @php
        $stats = $this->getStats();
    @endphp

    @if($stats)
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-6">
            <x-filament::card class="p-4 border-l-4 border-primary-500">
                <div class="text-sm font-medium text-gray-500">Total Selesai</div>
                <div class="text-2xl font-bold">{{ $stats['count'] }}</div>
            </x-filament::card>

            <x-filament::card class="p-4 border-l-4 border-info-500">
                <div class="text-sm font-medium text-gray-500">Rata-rata Nilai</div>
                <div class="text-2xl font-bold text-info-600">{{ $stats['avg'] }}</div>
            </x-filament::card>

            <x-filament::card class="p-4 border-l-4 border-success-500">
                <div class="text-sm font-medium text-gray-500">Nilai Tertinggi</div>
                <div class="text-2xl font-bold text-success-600">{{ $stats['max'] }}</div>
            </x-filament::card>

            <x-filament::card class="p-4 border-l-4 border-danger-500">
                <div class="text-sm font-medium text-gray-500">Nilai Terendah</div>
                <div class="text-2xl font-bold text-danger-600">{{ $stats['min'] }}</div>
            </x-filament::card>
        </div>
    @endif

    <div class="mt-8">
        {{ $this->table }}
    </div>
</x-filament-panels::page>
