<x-filament-panels::page>
    <div class="p-6 bg-white border border-gray-200 rounded-xl shadow-sm dark:bg-gray-800 dark:border-gray-700">
        <div class="mb-4">
            <h3 class="text-lg font-bold text-gray-800 dark:text-white">Filter</h3>
        </div>
        
        <form wire:submit.prevent="applyFilter" class="flex items-end gap-x-4">
            <div class="w-full max-w-sm">
                {{ $this->form }}
            </div>
            
            <x-filament::button type="submit" size="md">
                Apply
            </x-filament::button>
        </form>
    </div>

    <div class="mt-6">
        {{ $this->table }}
    </div>
</x-filament-panels::page>
