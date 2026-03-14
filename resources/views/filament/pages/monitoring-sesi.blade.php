<x-filament-panels::page>
    <form wire:submit="submit">
        {{ $this->form }}
    </form>

    @php
        $stats = $this->getStats();
    @endphp

    @if($stats)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mt-6">
            <x-filament::card class="p-4 border-l-4 border-primary-500">
                <div class="text-sm font-medium text-gray-500">Total Peserta</div>
                <div class="text-2xl font-bold">{{ $stats['total'] }}</div>
            </x-filament::card>

            <x-filament::card class="p-4 border-l-4 border-gray-400">
                <div class="text-sm font-medium text-gray-500">Belum Mulai</div>
                <div class="text-2xl font-bold text-gray-600">{{ $stats['registered'] }}</div>
            </x-filament::card>

            <x-filament::card class="p-4 border-l-4 border-info-500">
                <div class="text-sm font-medium text-gray-500">Sedang Mengerjakan</div>
                <div class="text-2xl font-bold text-info-600">{{ $stats['started'] }}</div>
            </x-filament::card>

            <x-filament::card class="p-4 border-l-4 border-success-500">
                <div class="text-sm font-medium text-gray-500">Selesai</div>
                <div class="text-2xl font-bold text-success-600">{{ $stats['completed'] }}</div>
            </x-filament::card>

            <x-filament::card class="p-4 border-l-4 border-danger-500">
                <div class="text-sm font-medium text-gray-500">Bermasalah</div>
                <div class="text-2xl font-bold text-danger-600">{{ $stats['problem'] }}</div>
            </x-filament::card>
        </div>
    @else
        <div class="mt-8 flex flex-col items-center justify-center p-8 bg-white rounded-xl shadow-sm border border-gray-100 italic text-gray-400">
             <x-heroicon-o-information-circle class="w-12 h-12 mb-2"/>
             Silahkan pilih Sekolah dan Jadwal untuk melihat statistik monitoring.
        </div>
    @endif
</x-filament-panels::page>
