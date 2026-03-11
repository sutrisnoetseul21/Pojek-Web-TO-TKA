<x-filament-panels::page>
    {{-- Header Info --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $record->nama_paket }}</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $record->deskripsi ?? '-' }}</p>
            </div>
            <div class="flex gap-4 text-sm">
                <div class="text-center">
                    <div class="text-2xl font-bold text-primary-600">{{ $record->mapelItems->count() }}</div>
                    <div class="text-gray-500">Mapel</div>
                </div>
                <div class="text-center">
                    @php
                        $totalSoal = $record->mapelItems->sum(function ($item) {
                            if ($item->mode === 'MANUAL' && !empty($item->soal_ids)) {
                                return count($item->soal_ids);
                            }
                            return $item->jumlah_soal;
                        });
                    @endphp
                    <div class="text-2xl font-bold text-success-600">{{ $totalSoal }}</div>
                    <div class="text-gray-500">Soal</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-warning-600">{{ $record->mapelItems->sum('waktu_mapel') }}</div>
                    <div class="text-gray-500">Menit</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Soal per Mapel --}}
    @foreach ($this->getSoalData() as $mapelIndex => $mapel)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 mb-6 overflow-hidden">
            {{-- Mapel Header --}}
            <div class="bg-primary-50 dark:bg-primary-900/20 px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-primary-600 flex items-center justify-center">
                            <span class="text-white font-bold text-lg">{{ $mapelIndex + 1 }}</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $mapel['nama_mapel'] }}</h3>
                            <p class="text-sm text-gray-500">Kategori: {{ $mapel['kategori'] }} • {{ $mapel['jumlah'] }} soal • {{ $mapel['waktu'] }} menit</p>
                        </div>
                    </div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $mapel['mode'] === 'MANUAL' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200' }}">
                        {{ $mapel['mode'] === 'MANUAL' ? '✅ Manual' : '🎲 Acak' }}
                    </span>
                </div>
            </div>

            {{-- Soal List --}}
            <div class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse ($mapel['soal_list'] as $soal)
                    <div class="p-6 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        {{-- Soal Header --}}
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                                <span class="font-bold text-gray-600 dark:text-gray-300">{{ $soal['nomor'] }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                {{-- Tipe & Bobot --}}
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                        @switch($soal['tipe'])
                                            @case('PG_TUNGGAL') bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200 @break
                                            @case('PG_KOMPLEKS') bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200 @break
                                            @case('BENAR_SALAH') bg-teal-100 text-teal-800 dark:bg-teal-900 dark:text-teal-200 @break
                                            @case('MENJODOHKAN') bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200 @break
                                            @case('ISIAN') bg-pink-100 text-pink-800 dark:bg-pink-900 dark:text-pink-200 @break
                                            @default bg-gray-100 text-gray-800
                                        @endswitch
                                    ">
                                        {{ str_replace('_', ' ', $soal['tipe']) }}
                                    </span>
                                    <span class="text-xs text-gray-400">Bobot: {{ $soal['bobot'] }}</span>
                                    <span class="text-xs text-gray-400">ID: #{{ $soal['id'] }}</span>
                                </div>

                                {{-- Pertanyaan --}}
                                <div class="prose prose-sm dark:prose-invert max-w-none text-gray-800 dark:text-gray-200 mb-3">
                                    {!! $soal['pertanyaan'] !!}
                                </div>

                                {{-- Jawaban --}}
                                @if (count($soal['jawaban']) > 0)
                                    <div class="mt-3 space-y-1.5">
                                        @foreach ($soal['jawaban'] as $index => $jawaban)
                                            @php
                                                $label = chr(65 + $index); // A, B, C, D
                                                $isCorrect = ($jawaban['kunci'] === 'BENAR' || $jawaban['skor'] > 0);
                                            @endphp
                                            <div class="flex items-start gap-2 px-3 py-2 rounded-lg text-sm
                                                {{ $isCorrect ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800' : 'bg-gray-50 dark:bg-gray-700/30 border border-gray-100 dark:border-gray-600' }}
                                            ">
                                                <span class="flex-shrink-0 w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold
                                                    {{ $isCorrect ? 'bg-green-500 text-white' : 'bg-gray-200 dark:bg-gray-600 text-gray-600 dark:text-gray-300' }}
                                                ">{{ $label }}</span>
                                                <div class="flex-1 text-gray-700 dark:text-gray-300">{!! $jawaban['teks'] !!}</div>
                                                <span class="flex-shrink-0 text-xs font-mono
                                                    {{ $jawaban['skor'] > 0 ? 'text-green-600' : ($jawaban['skor'] < 0 ? 'text-red-500' : 'text-gray-400') }}
                                                ">{{ $jawaban['skor'] > 0 ? '+' : '' }}{{ $jawaban['skor'] }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-gray-400">
                        @if ($mapel['mode'] === 'ACAK')
                            <p class="text-lg">🎲 Mode Acak</p>
                            <p class="text-sm mt-1">{{ $mapel['jumlah'] }} soal akan diambil secara acak saat tryout dimulai.</p>
                        @else
                            <p>Belum ada soal yang dipilih.</p>
                        @endif
                    </div>
                @endforelse
            </div>
        </div>
    @endforeach
</x-filament-panels::page>
