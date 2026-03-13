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
    protected static ?string $navigationGroup = 'Bank Soal';

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
                                    ->options(\App\Models\RefMapel::all()->mapWithKeys(fn($m) => [$m->id => "{$m->nama_mapel} - {$m->jenjang}"]))
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function (Forms\Set $set) {
                                        $set('paket_id', null);
                                        $set('stimulus_id', null);
                                    })
                                    ->label('Mata Pelajaran'),
                                Forms\Components\Select::make('paket_id')
                                    ->relationship('paket', 'nama_paket', modifyQueryUsing: fn(Builder $query, Forms\Get $get) => $query->where('mapel_id', $get('mapel_id')))
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->disabled(fn(Forms\Get $get) => !$get('mapel_id'))
                                    ->afterStateUpdated(fn(Forms\Set $set) => $set('stimulus_id', null))
                                    ->label('Kategori / Paket'),
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
                        Forms\Components\RichEditor::make('pertanyaan')
                            ->label('')
                            ->required()
                            ->fileAttachmentsDisk('public')
                            ->fileAttachmentsDirectory('soal-images')
                            ->fileAttachmentsVisibility('public')
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('pembahasan')
                            ->label('Pembahasan (Opsional)')
                            ->rows(2)
                            ->columnSpanFull(),
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
                                            ->afterStateUpdated(fn($state, Forms\Set $set) => $set('skor', $state === 'BENAR' ? 1 : 0))
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
                    ->label('Paket')
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
                Tables\Filters\SelectFilter::make('mapel_id')
                    ->relationship('mapel', 'nama_mapel')
                    ->label('Mata Pelajaran')
                    ->preload(),
                Tables\Filters\SelectFilter::make('paket_id')
                    ->relationship('paket', 'nama_paket')
                    ->label('Paket')
                    ->preload(),
                Tables\Filters\SelectFilter::make('tipe_soal')
                    ->options([
                        'PG_TUNGGAL' => 'PG Tunggal',
                        'PG_KOMPLEKS' => 'PG Kompleks',
                        'BENAR_SALAH' => 'Benar/Salah',
                        'MENJODOHKAN' => 'Menjodohkan',
                        'ISIAN' => 'Isian',
                    ])
                    ->label('Tipe Soal'),
            ])
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
