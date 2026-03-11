@php
    $statePath = str_replace('soal_selector_ui', 'soal_ids', $componentStatePath);
@endphp
<div x-data="{
    state: $wire.$entangle('{{ $statePath }}'),
    modalOpen: false,
    activeSoal: null,
    soals: {{ \Illuminate\Support\Js::from($soals) }},
    get groupedSoals() {
        return this.soals.reduce((acc, soal) => {
            const paketName = soal.paket ? soal.paket.nama_paket : 'Tanpa Paket';
            if (!acc[paketName]) acc[paketName] = [];
            acc[paketName].push(soal);
            return acc;
        }, {});
    }
}">
    <div class="space-y-4">
        <template x-for="(group, paketName) in groupedSoals" :key="paketName">
            <div class="border rounded-lg overflow-hidden dark:border-gray-700">
                <div class="bg-gray-100 dark:bg-gray-800 px-4 py-2 font-bold text-sm text-gray-700 dark:text-gray-200 border-b dark:border-gray-700"
                    x-text="paketName"></div>
                <div class="divide-y divide-gray-100 dark:divide-gray-700 bg-white dark:bg-gray-900">
                    <template x-for="soal in group" :key="soal.id">
                        <div
                            class="flex items-start gap-3 p-3 hover:bg-gray-50 dark:hover:bg-gray-800 transition group">
                            <div class="pt-1">
                                <input type="checkbox" :value="soal.id" x-model="state"
                                    class="border-gray-300 dark:border-gray-600 rounded text-primary-600 focus:ring-primary-500 dark:bg-gray-700">
                            </div>
                            <div class="flex-1 min-w-0 grid gap-1">
                                <div class="text-sm text-gray-900 dark:text-gray-100 line-clamp-2"
                                    x-html="soal.pertanyaan"></div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    <span x-text="soal.tipe_soal"></span>
                                </div>
                            </div>
                            <button type="button" @click="activeSoal = soal; modalOpen = true"
                                class="shrink-0 px-2 py-1 text-xs font-medium text-primary-600 border border-primary-200 rounded hover:bg-primary-50 dark:bg-primary-900/20 dark:border-primary-800 dark:text-primary-400">
                                👁️ Lihat
                            </button>
                        </div>
                    </template>
                </div>
            </div>
        </template>

        <div x-show="!soals.length" class="text-sm text-gray-500 text-center py-8 border rounded-lg border-dashed">
            Tidak ada soal tersedia untuk kategori yang dipilih.
        </div>
    </div>

    <!-- Modal (Simple Overlay) -->
    <div x-show="modalOpen" style="display: none;"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-2xl w-full max-h-[85vh] flex flex-col"
            @click.outside="modalOpen = false" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">

            <!-- Header -->
            <div class="flex items-center justify-between p-4 border-b dark:border-gray-700 shrink-0">
                <h3 class="font-bold text-lg text-gray-900 dark:text-white">Preview Soal</h3>
                <button @click="modalOpen = false"
                    class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 p-1 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <!-- Content -->
            <div class="p-6 overflow-y-auto flex-1">
                <template x-if="activeSoal">
                    <div class="space-y-6">
                        <!-- Stimulus -->
                        <template x-if="activeSoal.stimulus">
                            <div
                                class="p-4 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
                                <strong
                                    class="block text-xs uppercase tracking-wider text-gray-500 mb-2">Stimulus</strong>
                                <div x-html="activeSoal.stimulus.konten"
                                    class="prose prose-sm dark:prose-invert max-w-none"></div>
                                <template x-if="activeSoal.stimulus.gambar">
                                    <img :src="'/storage/' + activeSoal.stimulus.gambar"
                                        class="mt-3 rounded max-h-60 object-contain bg-white">
                                </template>
                            </div>
                        </template>

                        <!-- Pertanyaan -->
                        <div>
                            <strong
                                class="block text-xs uppercase tracking-wider text-gray-500 mb-2">Pertanyaan</strong>
                            <div class="text-base text-gray-900 dark:text-white leading-relaxed"
                                x-html="activeSoal.pertanyaan"></div>
                        </div>

                        <!-- Jawaban -->
                        <div>
                            <strong class="block text-xs uppercase tracking-wider text-gray-500 mb-2">Opsi
                                Jawaban</strong>
                            <div class="space-y-2">
                                <template x-for="(jawaban, idx) in activeSoal.jawaban" :key="jawaban.id">
                                    <div class="flex items-start gap-3 p-3 rounded-lg border text-sm transition-colors"
                                        :class="jawaban.is_benar ? 'bg-green-50 border-green-200 dark:bg-green-900/10 dark:border-green-800' : 'bg-white border-gray-200 dark:bg-gray-800 dark:border-gray-700'">
                                        <span
                                            class="flex items-center justify-center w-6 h-6 rounded-full text-xs font-bold shrink-0"
                                            :class="jawaban.is_benar ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300'"
                                            x-text="String.fromCharCode(65 + idx)"></span>

                                        <div class="flex-1 pt-0.5">
                                            <div x-html="jawaban.teks_jawaban"
                                                class="prose prose-sm dark:prose-invert max-w-none"></div>
                                        </div>

                                        <template x-if="jawaban.is_benar">
                                            <div
                                                class="flex items-center text-green-600 dark:text-green-400 font-bold text-xs bg-green-100 dark:bg-green-900/30 px-2 py-1 rounded">
                                                ✓ Kunci
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <div class="p-4 border-t dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 shrink-0 text-right">
                <button @click="modalOpen = false"
                    class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm font-medium transition">Tutup</button>
            </div>
        </div>
    </div>
</div>