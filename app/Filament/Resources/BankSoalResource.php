<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BankSoalResource\Pages;
use App\Filament\Resources\BankSoalResource\RelationManagers;
use App\Models\BankSoal;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BankSoalResource extends Resource
{
    protected static ?string $model = BankSoal::class;

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        return $user->hasRole('super_admin') || $user->hasPermissionTo('manage_soal');
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if ($user->isAdmin() && $user->jenjang) {
            $query->whereHas('mapel', function ($q) use ($user) {
                $q->where('jenjang', '=', $user->jenjang);
            });
        }

        return $query;
    }

    protected static ?string $navigationLabel = 'Bank Soal';
    protected static ?string $modelLabel = 'Bank Soal';
    protected static ?string $pluralModelLabel = 'Bank Soal';
    protected static ?string $navigationGroup = 'Bank Soal & Mata Pelajaran';
    protected static ?int $navigationSort = 2;

    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // BAGIAN 1: PENGATURAN SOAL
                Forms\Components\Section::make('Identitas & Pengaturan')
                    ->schema([
                        // Baris 1: Mata Pelajaran & Paket (harus dipilih dulu)
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('mapel_id')
                                    ->options(function () {
                                        $user = auth()->user();
                                        $query = \App\Models\RefMapel::query();

                                        if ($user->isAdmin() && $user->jenjang) {
                                            $query->where('jenjang', $user->jenjang);
                                        }

                                        return $query->get()->mapWithKeys(fn($m) => [$m->id => "{$m->nama_mapel} - {$m->jenjang}"]);
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function (Forms\Set $set) {
                                        $set('paket_id', null);
                                        $set('stimulus_id', null);
                                    })
                                    ->rules([
                                        fn() => function (string $attribute, $value, $fail) {
                                            $user = auth()->user();
                                            if ($user->isAdmin() && $user->jenjang) {
                                                $mapel = \App\Models\RefMapel::find($value);
                                                if ($mapel && $mapel->jenjang !== $user->jenjang) {
                                                    $fail("Mata Pelajaran ini dari Jenjang {$mapel->jenjang}, sedangkan akun Anda dialokasikan untuk Jenjang {$user->jenjang}. Tidak boleh menyimpan.");
                                                }
                                            }
                                        }
                                    ])
                                    ->label('Mata Pelajaran'),
                                Forms\Components\Select::make('paket_id')
                                    ->relationship('paket', 'nama_paket', modifyQueryUsing: fn(Builder $query, Forms\Get $get) => $query->where('mapel_id', $get('mapel_id')))
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->disabled(fn(Forms\Get $get) => !$get('mapel_id'))
                                    ->afterStateUpdated(fn(Forms\Set $set) => $set('stimulus_id', null))
                                    ->label('Paket Soal'),
                            ]),

                        // Baris 2: Tipe Soal, Stimulus, Bobot
                        Forms\Components\Grid::make(12)
                            ->schema([
                                Forms\Components\Select::make('tipe_soal')
                                    ->label('Tipe Soal')
                                    ->options([
                                        'PG_TUNGGAL' => 'Pilihan Ganda Tunggal',
                                        'PG_KOMPLEKS' => 'Pilihan Ganda Kompleks',
                                        'BENAR_SALAH' => 'Benar / Salah (Model Tabel)',
                                        'MENJODOHKAN' => 'Menjodohkan',
                                        'ISIAN' => 'Isian Singkat',
                                    ])
                                    ->required()
                                    ->live()
                                    ->disabled(fn(Forms\Get $get) => !$get('mapel_id'))
                                    ->afterStateUpdated(fn(Forms\Set $set) => $set('jawaban', []))
                                    ->helperText(fn(?string $state): string => match ($state) {
                                        'PG_TUNGGAL' => 'Siswa memilih SATU jawaban benar (Radio Button).',
                                        'PG_KOMPLEKS' => 'Siswa memilih LEBIH DARI SATU jawaban (Checkbox).',
                                        'BENAR_SALAH' => 'Siswa menentukan Benar/Salah untuk setiap pernyataan.',
                                        'MENJODOHKAN' => 'Siswa mencocokkan premis dengan pasangan yang tepat.',
                                        'ISIAN' => 'Siswa mengisi jawaban singkat.',
                                        default => 'Pilih tipe soal untuk melihat deskripsi.',
                                    })
                                    ->columnSpan(5),

                                // Stimulus: filtered by mapel_id AND paket_id
                                Forms\Components\Select::make('stimulus_id')
                                    ->relationship(
                                        'stimulus',
                                        'judul',
                                        modifyQueryUsing: fn(Builder $query, Forms\Get $get) => $query
                                            ->where('mapel_id', $get('mapel_id'))
                                            ->when($get('paket_id'), fn($q, $paketId) => $q->where('paket_id', $paketId))
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->label('Stimulus (Induk Soal)')
                                    ->placeholder('-- Soal Berdiri Sendiri --')
                                    ->disabled(fn(Forms\Get $get) => !$get('mapel_id'))
                                    ->columnSpan(5),

                                // Bobot Global (2/12)
                                Forms\Components\TextInput::make('bobot')
                                    ->label('Bobot Nilai')
                                    ->numeric()
                                    ->default(1)
                                    ->required()
                                    ->disabled(fn(Forms\Get $get) => !$get('mapel_id'))
                                    ->columnSpan(2),
                            ]),
                    ]),

                // BAGIAN 2: KONTEN SOAL
                Forms\Components\Section::make('Konten Pertanyaan')
                    ->description('Gunakan toolbar untuk memasukkan gambar, rumus, atau format teks.')
                    ->schema([
                        \FilamentTiptapEditor\TiptapEditor::make('pertanyaan')
                            ->profile('default')
                            ->label('')
                            ->required()
                            ->disk('public')
                            ->directory('soal-images')
                            ->extraInputAttributes(['style' => 'min-height: 200px;'])
                            ->columnSpanFull()
                            ->hintAction(
                                \Filament\Forms\Components\Actions\Action::make('insert_math')
                                    ->label('Insert Math (MathLive)')
                                    ->icon('heroicon-m-calculator')
                                    ->form([
                                        \Filament\Forms\Components\ViewField::make('latex_code')
                                            ->view('filament.forms.components.mathlive-modal-input')
                                            ->label('Persamaan Matematika')
                                    ])
                                    ->action(function (array $data, $state, \Filament\Forms\Set $set) {
                                        $newLatex = $data['latex_code'] ?? '';
                                        if ($newLatex) {
                                            $set('pertanyaan', $state . ' $$' . $newLatex . '$$ ');
                                        }
                                    })
                            ),
                        \FilamentTiptapEditor\TiptapEditor::make('pembahasan')
                            ->profile('default')
                            ->label('Pembahasan (Opsional)')
                            ->extraInputAttributes(['style' => 'min-height: 200px;'])
                            ->columnSpanFull()
                            ->hintAction(
                                \Filament\Forms\Components\Actions\Action::make('insert_math_pembahasan')
                                    ->label('Insert Math (MathLive)')
                                    ->icon('heroicon-m-calculator')
                                    ->form([
                                        \Filament\Forms\Components\ViewField::make('latex_code')
                                            ->view('filament.forms.components.mathlive-modal-input')
                                            ->label('Persamaan Matematika')
                                    ])
                                    ->action(function (array $data, $state, \Filament\Forms\Set $set) {
                                        $newLatex = $data['latex_code'] ?? '';
                                        if ($newLatex) {
                                            $set('pembahasan', $state . ' $$' . $newLatex . '$$ ');
                                        }
                                    })
                            ),
                        Forms\Components\Hidden::make('nomor_urut')
                            ->default(0),
                    ]),

                // BAGIAN 3: JAWABAN (Repeater Grid 12)
                Forms\Components\Section::make('Opsi Jawaban & Poin')
                    ->description('Atur opsi jawaban beserta poin dan kunci jawaban yang benar. Drag untuk mengubah urutan.')
                    ->schema([
                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('atur_jumlah')
                                ->label('Atur Jumlah Opsi')
                                ->icon('heroicon-m-adjustments-horizontal')
                                ->form([
                                    Forms\Components\TextInput::make('jumlah')
                                        ->label('Jumlah Opsi')
                                        ->numeric()
                                        ->default(4)
                                        ->minValue(1)
                                        ->maxValue(10)
                                        ->required(),
                                ])
                                ->action(function (array $data, Forms\Get $get, Forms\Set $set) {
                                    $currentItems = $get('jawaban') ?? [];
                                    $newCount = (int) $data['jumlah'];
                                    $currentCount = count($currentItems);

                                    if ($newCount > $currentCount) {
                                        for ($i = $currentCount; $i < $newCount; $i++) {
                                            $currentItems[] = [
                                                'teks_jawaban' => '',
                                                'skor' => 0,
                                                'kunci_jawaban' => null,
                                            ];
                                        }
                                    } elseif ($newCount < $currentCount) {
                                        $currentItems = array_slice($currentItems, 0, $newCount);
                                    }

                                    $set('jawaban', $currentItems);
                                }),
                        ]),
                        Forms\Components\Repeater::make('jawaban')
                            ->relationship()
                            ->schema([
                                Forms\Components\Grid::make(12)
                                    ->schema([
                                        // 1. INPUT TEKS (6/12)
                                        Forms\Components\TextInput::make('teks_jawaban')
                                            ->label(fn(Forms\Get $get) => match ($get('../../tipe_soal')) {
                                                'BENAR_SALAH' => 'Pernyataan',
                                                'MENJODOHKAN' => 'Premis Kiri',
                                                default => 'Teks Jawaban',
                                            })
                                            ->required()
                                            ->placeholder('Ketik isi jawaban/pernyataan...')
                                            ->columnSpan(6),

                                        // 2. INPUT POIN (2/12)
                                        Forms\Components\TextInput::make('skor')
                                            ->label('Poin +/-')
                                            ->numeric()
                                            ->default(0)
                                            ->columnSpan(2),

                                        // 3. KUNCI JAWABAN
                                        // A. Toggle Benar/Salah (DIHAPUS/DISEMBUNYIKAN untuk PG & PG KOMPLEKS karena pakai Skor Manual)
                                        // Sesuai request: "skor diberi manual cuma di hiden opsi benar salahnya"

                                        // B. Select untuk Benar/Salah (SMART SCORING)
                                        Forms\Components\Select::make('kunci_jawaban')
                                            ->label('Kunci')
                                            ->options([
                                                'BENAR' => 'Benar',
                                                'SALAH' => 'Salah',
                                            ])
                                            ->live()
                                            ->afterStateUpdated(fn($state, Forms\Set $set) => $set('skor', 1))
                                            ->visible(fn(Forms\Get $get) => $get('../../tipe_soal') === 'BENAR_SALAH')
                                            ->columnSpan(3),

                                        // C. Text untuk Menjodohkan
                                        Forms\Components\TextInput::make('kunci_jawaban')
                                            ->label('Pasangan (Kanan)')
                                            ->placeholder('Pasangan...')
                                            ->visible(fn(Forms\Get $get) => $get('../../tipe_soal') === 'MENJODOHKAN')
                                            ->columnSpan(3),
                                    ]),
                            ])
                            ->defaultItems(fn(Forms\Get $get) => match ($get('tipe_soal')) {
                                'BENAR_SALAH' => 4,
                                default => 4,
                            })

                            ->reorderable()
                            ->collapsible()
                            ->cloneable()
                            ->grid(1)
                            ->itemLabel(fn(array $state): ?string => $state['teks_jawaban'] ?? 'Opsi Jawaban Baru')
                            ->columnSpanFull()
                            ->addActionLabel('Tambah Baris'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('paket.nama_paket')
                    ->label('Paket Soal')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('mapel.nama_mapel')
                    ->label('Mata Pelajaran')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('stimulus.judul')
                    ->label('Stimulus')
                    ->limit(30)
                    ->tooltip(fn($record) => $record->stimulus?->judul)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('pertanyaan')
                    ->label('Soal')
                    ->html()
                    ->limit(50)
                    ->tooltip(fn($record) => strip_tags($record->pertanyaan))
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('tipe_soal')
                    ->label('Tipe')
                    ->colors([
                        'primary' => 'PG_TUNGGAL',
                        'success' => 'PG_KOMPLEKS',
                        'warning' => 'BENAR_SALAH',
                        'info' => 'MENJODOHKAN',
                        'gray' => 'ISIAN',
                    ]),
                Tables\Columns\TextColumn::make('bobot')
                    ->label('Bobot')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('advanced_filters')
                    ->columnSpanFull()
                    ->form([
                        Forms\Components\Grid::make(4)
                            ->schema([
                                Forms\Components\Select::make('jenjang')
                                    ->label('Jenjang')
                                    ->options([
                                        'SD' => 'SD',
                                        'SMP' => 'SMP',
                                        'SMA' => 'SMA',
                                        'SMK' => 'SMK',
                                        'UMUM' => 'UMUM',
                                    ])
                                    ->placeholder('All')
                                    ->visible(fn () => ! (auth()->user()->isAdmin() && auth()->user()->jenjang))
                                    ->live(),
                                
                                Forms\Components\Select::make('mapel_id')
                                    ->label('Mata Pelajaran')
                                    ->placeholder('All')
                                    ->options(function (Forms\Get $get) {
                                        $user = auth()->user();
                                        $jenjang = $get('jenjang') ?? ($user->isAdmin() ? $user->jenjang : null);
                                        
                                        return \App\Models\RefMapel::when($jenjang, fn($q) => $q->where('jenjang', $jenjang))
                                            ->get()
                                            ->mapWithKeys(fn($m) => [$m->id => "{$m->nama_mapel} - {$m->jenjang}"]);
                                    })
                                    ->live()
                                    ->searchable()
                                    ->preload(),

                                Forms\Components\Select::make('paket_id')
                                    ->label('Paket Soal')
                                    ->placeholder('All')
                                    ->options(function (Forms\Get $get) {
                                        $mapelId = $get('mapel_id');
                                        if (!$mapelId) return [];
                                        
                                        return \App\Models\RefPaketSoal::where('mapel_id', $mapelId)
                                            ->pluck('nama_paket', 'id');
                                    })
                                    ->disabled(fn (Forms\Get $get) => !$get('mapel_id'))
                                    ->searchable()
                                    ->preload(),
                                
                                Forms\Components\Select::make('tipe_soal')
                                    ->label('Tipe Soal')
                                    ->placeholder('All')
                                    ->options([
                                        'PG_TUNGGAL' => 'PG Tunggal',
                                        'PG_KOMPLEKS' => 'PG Kompleks',
                                        'BENAR_SALAH' => 'Benar/Salah',
                                        'MENJODOHKAN' => 'Menjodohkan',
                                        'ISIAN' => 'Isian',
                                    ]),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['mapel_id'], fn ($q, $mId) => $q->where('mapel_id', $mId))
                            ->when($data['paket_id'], fn ($q, $pId) => $q->where('paket_id', $pId))
                            ->when($data['tipe_soal'], fn ($q, $type) => $q->where('tipe_soal', $type))
                            ->when($data['jenjang'] && empty($data['mapel_id']), function ($q) use ($data) {
                                $q->whereHas('mapel', fn ($m) => $m->where('jenjang', $data['jenjang']));
                            });
                    })
            ], layout: \Filament\Tables\Enums\FiltersLayout::AboveContent)
            ->filtersFormColumns(4)
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBankSoals::route('/'),
            'create' => Pages\CreateBankSoal::route('/create'),
            'edit' => Pages\EditBankSoal::route('/{record}/edit'),
        ];
    }
}
