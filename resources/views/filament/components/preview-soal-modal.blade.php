<div class="space-y-4">
    @if($soals->isEmpty())
        <div class="text-center text-gray-500">
            Tidak ada soal yang ditemukan untuk kriteria ini.
        </div>
    @else
        @foreach($soals as $index => $soal)
            <div class="p-4 border rounded-lg bg-white shadow-sm dark:bg-gray-800 dark:border-gray-700">
                <div class="flex items-start gap-3">
                    <span class="font-bold text-gray-700 dark:text-gray-300">#{{ $index + 1 }}</span>
                    <div class="flex-1 overflow-hidden">
                        <div class="prose dark:prose-invert max-w-none text-sm">
                            {{-- Tampilkan Stimulus jika ada --}}
                            @if($soal->stimulus)
                                <div
                                    class="mb-2 p-2 bg-gray-50 dark:bg-gray-900 rounded border border-gray-200 dark:border-gray-700">
                                    <strong>Stimulus:</strong> {!! $soal->stimulus->konten !!}
                                    @if($soal->stimulus->gambar)
                                        <img src="{{ asset('storage/' . $soal->stimulus->gambar) }}"
                                            class="mt-2 max-h-40 object-contain">
                                    @endif
                                </div>
                            @endif

                            {{-- Pertanyaan --}}
                            <div class="font-medium text-gray-900 dark:text-white mb-2">
                                {!! $soal->pertanyaan !!}
                            </div>

                            {{-- Jawaban --}}
                            <div class="grid grid-cols-1 gap-2 mt-2">
                                @foreach($soal->jawaban as $jawaban)
                                    <div
                                        class="@if($jawaban->is_benar) bg-green-50 text-green-700 border-green-200 dark:bg-green-900/20 dark:text-green-400 dark:border-green-800 @else bg-gray-50 text-gray-700 border-gray-200 dark:bg-gray-700 dark:border-gray-600 @endif border p-2 rounded text-xs flex items-center">
                                        <span
                                            class="w-4 h-4 flex items-center justify-center border rounded-full mr-2 text-[10px] font-bold">
                                            {{ chr(65 + $loop->index) }}
                                        </span>
                                        <div class="flex-1">
                                            {!! $jawaban->teks_jawaban !!}
                                        </div>
                                        @if($jawaban->is_benar)
                                            <span class="ml-2 text-xs font-bold">✓ Benar</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @endif
</div>