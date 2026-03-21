<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <!-- Load MathQuill CSS & JS dari CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/mathquill/0.10.1/mathquill.min.css">

    <style>
        .mathquill-editor {
            font-size: 1.4rem;
            padding: 0.75rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            width: 100%;
            min-height: 60px;
            background-color: #fff;
            box-shadow: inset 0 1px 2px rgba(0,0,0,0.05);
        }
        .mathquill-editor .mq-editable-field { min-height: 40px; }
        .dark .mathquill-editor {
            background-color: #27272a;
            border-color: #3f3f46;
            color: #f4f4f5;
        }
        .mq-toolbar { 
            display: flex; 
            flex-wrap: wrap; 
            gap: 4px; 
            margin-bottom: 8px; 
            padding: 6px;
            background: #f3f4f6;
            border-radius: 0.5rem;
            border: 1px solid #e5e7eb;
        }
        .dark .mq-toolbar {
            background: #1f2937;
            border-color: #374151;
        }
        .mq-toolbar button {
            padding: 6px 10px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            background: #fff;
            cursor: pointer;
            font-size: 0.9rem;
            min-width: 36px;
            transition: all 0.15s;
        }
        .dark .mq-toolbar button {
            background: #374151;
            border-color: #4b5563;
            color: #f4f4f5;
        }
        .mq-toolbar button:hover {
            background: #f59e0b;
            color: #fff;
            border-color: #f59e0b;
        }
        .mq-toolbar .mq-sep {
            width: 1px;
            background: #d1d5db;
            margin: 0 4px;
        }
    </style>

    <div x-data="{
            state: $wire.$entangle('{{ $getStatePath() }}', true),
            mqField: null,
            init() {
                // Load jQuery if not available (MathQuill requires it)
                const loadScript = (src, id) => {
                    return new Promise((resolve) => {
                        if (document.getElementById(id)) { resolve(); return; }
                        let s = document.createElement('script');
                        s.id = id;
                        s.src = src;
                        s.onload = resolve;
                        document.head.appendChild(s);
                    });
                };

                const setup = async () => {
                    if (!window.jQuery) {
                        await loadScript('https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js', 'jquery-script');
                    }
                    if (!window.MathQuill) {
                        await loadScript('https://cdnjs.cloudflare.com/ajax/libs/mathquill/0.10.1/mathquill.min.js', 'mathquill-script');
                    }
                    // Wait for MathQuill to be ready
                    let attempts = 0;
                    let check = setInterval(() => {
                        attempts++;
                        if (window.MathQuill || attempts > 50) {
                            clearInterval(check);
                            if (window.MathQuill) this.setupMathQuill();
                        }
                    }, 100);
                };
                setup();
            },
            setupMathQuill() {
                this.$nextTick(() => {
                    const MQ = MathQuill.getInterface(2);
                    const el = this.$refs.mathEditor;
                    if (!el) return;

                    this.mqField = MQ.MathField(el, {
                        spaceBehavesLikeTab: true,
                        handlers: {
                            edit: () => {
                                if (this.mqField) {
                                    this.state = this.mqField.latex();
                                }
                            }
                        }
                    });

                    // Set initial value
                    if (this.state) {
                        this.mqField.latex(this.state);
                    }

                    // Sync from textarea -> MathQuill
                    this.$watch('state', (value) => {
                        if (this.mqField && this.mqField.latex() !== value) {
                            this.mqField.latex(value || '');
                        }
                    });

                    // Auto-focus
                    setTimeout(() => this.mqField.focus(), 200);
                });
            },
            insertCmd(cmd) {
                if (this.mqField) {
                    this.mqField.cmd(cmd);
                    this.mqField.focus();
                }
            },
            insertLatex(latex) {
                if (this.mqField) {
                    this.mqField.write(latex);
                    this.mqField.focus();
                }
            }
        }">
        
        <!-- Toolbar Simbol Matematika -->
        <div class="mq-toolbar">
            <button type="button" @click="insertCmd('\\frac')" title="Pecahan">½</button>
            <button type="button" @click="insertCmd('\\sqrt')" title="Akar Kuadrat">√</button>
            <button type="button" @click="insertLatex('\\sqrt[n]{}')" title="Akar ke-n">ⁿ√</button>
            <button type="button" @click="insertCmd('^')" title="Pangkat">x²</button>
            <button type="button" @click="insertCmd('_')" title="Subscript">x₂</button>
            <div class="mq-sep"></div>
            <button type="button" @click="insertLatex('\\pi')" title="Pi">π</button>
            <button type="button" @click="insertLatex('\\alpha')" title="Alpha">α</button>
            <button type="button" @click="insertLatex('\\beta')" title="Beta">β</button>
            <button type="button" @click="insertLatex('\\theta')" title="Theta">θ</button>
            <button type="button" @click="insertLatex('\\sigma')" title="Sigma">σ</button>
            <button type="button" @click="insertLatex('\\delta')" title="Delta">δ</button>
            <button type="button" @click="insertLatex('\\lambda')" title="Lambda">λ</button>
            <div class="mq-sep"></div>
            <button type="button" @click="insertLatex('\\leq')" title="Kurang dari sama dengan">≤</button>
            <button type="button" @click="insertLatex('\\geq')" title="Lebih dari sama dengan">≥</button>
            <button type="button" @click="insertLatex('\\neq')" title="Tidak sama dengan">≠</button>
            <button type="button" @click="insertLatex('\\pm')" title="Plus minus">±</button>
            <button type="button" @click="insertLatex('\\times')" title="Kali">×</button>
            <button type="button" @click="insertLatex('\\div')" title="Bagi">÷</button>
            <button type="button" @click="insertLatex('\\infty')" title="Infinity">∞</button>
            <div class="mq-sep"></div>
            <button type="button" @click="insertLatex('\\sum_{i=1}^{n}')" title="Sigma">Σ</button>
            <button type="button" @click="insertLatex('\\int_{a}^{b}')" title="Integral">∫</button>
            <button type="button" @click="insertLatex('\\lim_{x \\to \\infty}')" title="Limit">lim</button>
            <button type="button" @click="insertLatex('\\log')" title="Logaritma">log</button>
            <button type="button" @click="insertLatex('\\sin')" title="Sinus">sin</button>
            <button type="button" @click="insertLatex('\\cos')" title="Cosinus">cos</button>
            <button type="button" @click="insertLatex('\\tan')" title="Tangen">tan</button>
        </div>

        <!-- MathQuill Visual Editor -->
        <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
            <span>Editor Visual Matematika (Ketik langsung dengan keyboard)</span>
        </label>
        <div wire:ignore class="mathquill-editor mb-3">
            <span x-ref="mathEditor"></span>
        </div>
        
        <!-- Textarea LaTeX (Fallback / Advanced) -->
        <details class="mt-2">
            <summary class="text-xs text-gray-500 dark:text-gray-400 cursor-pointer hover:text-amber-500">
                ⌨️ Tampilkan/sembunyikan mode LaTeX mentah
            </summary>
            <div class="mt-2">
                <textarea 
                    x-model="state" 
                    @keydown.stop=""
                    @input.stop=""
                    rows="3" 
                    class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-amber-500 focus:ring-amber-500 sm:text-sm p-3 font-mono"
                    placeholder="Kode LaTeX mentah (sinkron otomatis)"
                ></textarea>
            </div>
        </details>
    </div>
</x-dynamic-component>
