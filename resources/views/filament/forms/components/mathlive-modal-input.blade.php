<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <!-- CSS untuk Virtual Keyboard MathLive -->
    <style>
        body { --keyboard-zindex: 9999 !important; }
        math-virtual-keyboard { z-index: 9999 !important; }
        math-field {
            font-size: 1.5rem; 
            padding: 1rem; 
            border: 1px solid #d1d5db; 
            border-radius: 0.5rem; 
            width: 100%; 
            min-height: 80px; 
            background-color: #fff; 
            box-shadow: inset 0 1px 2px rgba(0,0,0,0.05);
        }
        .dark math-field {
            background-color: #27272a;
            border-color: #3f3f46;
            color: #f4f4f5;
        }
    </style>

    <div x-data="{ 
            state: $wire.$entangle('{{ $getStatePath() }}'),
            mathField: null,
            init() {
                // [FIX] Filament Modals via AJAX seringkali tidak mengeksekusi directive pushOnce('scripts'). 
                // Menyuntikkan script JS MathLive secara manual ke DOM jika belum ada:
                if (!window.MathfieldElement && !document.getElementById('mathlive-script')) {
                    let script = document.createElement('script');
                    script.id = 'mathlive-script';
                    script.src = 'https://unpkg.com/mathlive';
                    document.head.appendChild(script);
                }

                // Menunggu script selesai dimuat sebelum inisialisasi custom element
                let checkInterval = setInterval(() => {
                    if (window.MathfieldElement) {
                        clearInterval(checkInterval);
                        this.setupMathField();
                    }
                }, 100);
            },
            setupMathField() {
                this.$nextTick(() => {
                    this.mathField = this.$refs.mathInput;
                    if(this.mathField) {
                        if(this.state) {
                            this.mathField.value = this.state;
                        }
                        
                        // Memastikan layout keyboard desktop ditampilkan saat fokus
                        if(window.mathVirtualKeyboard) {
                            window.mathVirtualKeyboard.layouts = ['default'];
                        }
                        
                        this.mathField.addEventListener('focus', () => {
                            if(window.mathVirtualKeyboard) {
                                window.mathVirtualKeyboard.show();
                            }
                        });
                        
                        this.mathField.addEventListener('input', (e) => {
                            this.state = e.target.value;
                        });
                    }
                });

                // Sinkronisasi data kembali dari textarea jika terjadi ngetik di sana
                this.$watch('state', (value) => {
                    if(this.mathField && this.mathField.value !== value) {
                        this.mathField.value = value;
                    }
                });
            }
        }">
        
        <!-- Editor Persamaan MathLive -->
        <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 block">Visual Editor Matematika:</label>
        <div wire:ignore class="mb-5">
            <math-field 
                x-ref="mathInput"
                tabindex="0"
                placeholder="Ketik angka & rumus di sini..."
            ></math-field>
        </div>
        
        <!-- Textarea Code Murni (Fallback Input Luas Setara 3 Paragraf) -->
        <div>
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 flex items-center justify-between">
                <span>Atau Mode Penulisan LaTeX Alternatif (Tinggi 3 Paragraf)</span>
            </label>
            <textarea 
                x-model="state" 
                rows="6" 
                style="min-height: 15rem;" 
                class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-amber-500 focus:ring-amber-500 sm:text-base p-4"
                placeholder="Ketik kode LaTeX murni di sini..."
            ></textarea>
            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                Kolom teks di atas disediakan khusus agar jarak form luas & selesaikan kebutuhan ketikan/tempel paragraf jika virtual editor MathLive dirasa belum optimal. (Keduanya sinkron seketika)
            </p>
        </div>
    </div>
</x-dynamic-component>
