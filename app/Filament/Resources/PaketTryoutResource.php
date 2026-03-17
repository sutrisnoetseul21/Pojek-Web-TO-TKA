<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaketTryoutResource\Pages;
use App\Filament\Resources\PaketTryoutResource\RelationManagers;
use App\Models\PaketTryout;
use App\Models\RefMapel;
use App\Models\RefPaketSoal;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PaketTryoutResource extends Resource
{
    protected static ?string $model = PaketTryout::class;

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
            $query->where(function (Builder $query) use ($user) {
                $query->where('jenjang', '=', $user->jenjang)
                    ->where(function ($q) use ($user) {
                        $q->whereNull('sekolah_id') // Global
                          ->orWhere('sekolah_id', $user->sekolah_id); // Specific to school
                    });
            });
        }

        return $query;
    }

    protected static ?string $navigationLabel = 'Paket Tryout';
    protected static ?string $modelLabel = 'Paket Tryout';
    protected static ?string $pluralModelLabel = 'Paket Tryout';
    protected static ?string $navigationGroup = 'Tryout';
    protected static ?int $navigationSort = 1;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Keterangan Paket & Sekolah')
                    ->description('Tentukan sekolah dan informasi dasar paket tryout')
                    ->schema([
                        Forms\Components\Select::make('sekolah_id')
                            ->label('Sekolah (Opsional)')
                            ->relationship('sekolah', 'nama_sekolah')
                            ->searchable()
                            ->preload()
                            ->hint('Kosongkan jika paket bersifat GLOBAL/Nasional')
                            ->disabled(fn () => auth()->user()->isAdmin())
                            ->dehydrated()
                            ->default(fn () => auth()->user()->sekolah_id),
                        Forms\Components\TextInput::make('nama_paket')
                            ->label('Nama Paket Tryout')
                            ->placeholder('Contoh: TRYOUT AKBAR TKA 1')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Forms\Set $set, ?string $state) {
                                if (!$state) return;
                                $words = explode(' ', preg_replace('/[^a-zA-Z0-9 ]/', '', $state));
                                $initials = '';
                                foreach ($words as $w) {
                                    if (!empty($w)) {
                                        $initials .= strtoupper($w[0]);
                                    }
                                }
                                $set('kode', $initials);
                            }),
                        Forms\Components\TextInput::make('kode')
                            ->label('Kode Tes')
                            ->placeholder('Contoh: TKA1')
                            ->required()
                            ->maxLength(50)
                            ->hint('Otomatis terisi dari inisial nama, bisa Anda ubah.'),
                        Forms\Components\Textarea::make('deskripsi')
                            ->label('Deskripsi')
                            ->placeholder('Deskripsi singkat tentang paket tryout ini...')
                            ->rows(2)
                            ->columnSpanFull(),
                        Forms\Components\Select::make('jenjang')
                            ->options([
                                'SD' => 'SD',
                                'SMP' => 'SMP',
                                'SMA' => 'SMA',
                                'SMK' => 'SMK',
                                'UMUM' => 'UMUM',
                            ])
                            ->required()
                            ->default(fn () => auth()->user()->jenjang ?? 'UMUM')
                            ->disabled(fn () => auth()->user()->isAdmin() && auth()->user()->jenjang !== null)
                            ->dehydrated(),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktif')
                            ->helperText('Paket aktif bisa dijadwalkan'),
                    ])->columns(2),

                Forms\Components\Section::make('Daftar Mata Pelajaran')
                    ->description('Tentukan mapel dan sumber soal untuk paket ini. Drag untuk mengubah urutan.')
                    ->schema([
                        Forms\Components\Repeater::make('mapelItems')
                            ->relationship()
                            ->label('')
                            ->schema([
                                Forms\Components\Grid::make(4)
                                    ->schema([
                                        Forms\Components\Select::make('mapel_id')
                                            ->label('Mata Pelajaran')
                                            ->options(function (callable $get) {
                                                $user = auth()->user();
                                                $query = \App\Models\RefMapel::query();

                                                if ($user->isAdmin() && $user->jenjang) {
                                                    $query->where('jenjang', $user->jenjang);
                                                }

                                                $allMapel = $query->get()->mapWithKeys(fn($m) => [$m->id => "{$m->nama_mapel} - {$m->jenjang}"]);

                                                $selectedMapelIds = collect($get('../../mapelItems'))
                                                    ->pluck('mapel_id')
                                                    ->filter(fn($id) => $id !== $get('mapel_id'))
                                                    ->toArray();

                                                return $allMapel->filter(fn($name, $id) => !in_array($id, $selectedMapelIds));
                                            })
                                            ->required()
                                            ->reactive()
                                            ->disableOptionWhen(
                                                fn($value, $state, callable $get) =>
                                                in_array($value, collect($get('../../mapelItems'))->pluck('mapel_id')->toArray()) && $value !== $state
                                            )
                                            ->afterStateUpdated(function (callable $set) {
                                                $set('kategori_ids', []);
                                                $set('soal_ids', []);
                                            }),
                                    ]),

                                Forms\Components\Grid::make(4)
                                    ->schema([
                                        Forms\Components\TextInput::make('waktu_mapel')
                                            ->label('Waktu (menit)')
                                            ->numeric()
                                            ->default(30)
                                            ->required()
                                            ->minValue(1),
                                    ]),

                                Forms\Components\Section::make('Kategori & Pengaturan Soal')
                                    ->schema([
                                        Forms\Components\Repeater::make('kategori_settings')
                                            ->label('')
                                            ->schema([
                                                Forms\Components\Grid::make(4)
                                                    ->schema([
                                                        Forms\Components\Select::make('kategori_id')
                                                            ->label('Kategori')
                                                            ->options(function (callable $get) {
                                                                $mapelId = $get('../../mapel_id'); 
                                                                if (!$mapelId) return [];
                                                                return \App\Models\RefPaketSoal::where('mapel_id', $mapelId)->pluck('nama_paket', 'id');
                                                            })
                                                            ->required()
                                                            ->live()
                                                            ->afterStateUpdated(fn (callable $set) => $set('soal_ids', [])),

                                                        Forms\Components\Select::make('mode')
                                                            ->label('Mode')
                                                            ->options([
                                                                'ACAK' => '🎲 Acak',
                                                                'MANUAL' => '✅ Manual',
                                                                'SEMUA' => '📋 Semua Soal',
                                                            ])
                                                            ->default('ACAK')
                                                            ->required()
                                                            ->live(),

                                                        Forms\Components\TextInput::make('jumlah_soal')
                                                            ->label('Jumlah Soal')
                                                            ->numeric()
                                                            ->default(10)
                                                            ->required(fn(callable $get) => $get('mode') === 'ACAK')
                                                            ->visible(fn(callable $get) => $get('mode') === 'ACAK')
                                                            ->helperText(function(callable $get) {
                                                                $katId = $get('kategori_id');
                                                                $mapelId = $get('../../mapel_id');
                                                                if ($katId && $mapelId) {
                                                                    $count = \App\Models\BankSoal::where('paket_id', $katId)->where('mapel_id', $mapelId)->count();
                                                                    return "Tersedia: {$count} soal";
                                                                }
                                                                return 'Pilih kategori dulu';
                                                            }),
                                                    ]),

                                                Forms\Components\Section::make('Pilih Soal Manual')
                                                    ->visible(fn(callable $get) => $get('mode') === 'MANUAL')
                                                    ->schema([
                                                        Forms\Components\Hidden::make('soal_ids')
                                                            ->default([])
                                                            ->required(fn(callable $get) => $get('mode') === 'MANUAL'),

                                                        Forms\Components\Placeholder::make('soal_selector_ui')
                                                            ->label('Daftar Soal')
                                                            ->content(function (callable $get, \Filament\Forms\Components\Placeholder $component) {
                                                                $katId = $get('kategori_id');
                                                                $mapelId = $get('../../mapel_id');
                                                                if (!$katId || !$mapelId) return 'Pilih Kategori terlebih dahulu.';

                                                                $soals = \App\Models\BankSoal::with(['paket', 'stimulus', 'jawaban'])
                                                                    ->where('paket_id', $katId)
                                                                    ->where('mapel_id', $mapelId)
                                                                    ->get();

                                                                return view('filament.forms.components.soal-selector', [
                                                                    'soals' => $soals,
                                                                    'componentStatePath' => $component->getStatePath(),
                                                                ]);
                                                            }),
                                                    ]),
                                            ])
                                            ->columnSpanFull()
                                            ->defaultItems(1)
                                            ->addActionLabel('+ Tambah Kategori')
                                            ->itemLabel(
                                                fn(array $state): ?string =>
                                                \App\Models\RefPaketSoal::find($state['kategori_id'] ?? null)?->nama_paket ?? 'Kategori Baru'
                                            ),
                                    ]),
                            ])
                            ->reorderable()
                            ->collapsible()
                            ->cloneable()
                            ->defaultItems(1)
                            ->itemLabel(
                                fn(array $state): ?string =>
                                RefMapel::find($state['mapel_id'])?->nama_mapel ?? 'Mapel Baru'
                            )
                            ->addActionLabel('+ Tambah Mapel')
                            ->deleteAction(
                                fn(Forms\Components\Actions\Action $action) => $action->requiresConfirmation(),
                            ),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode')
                    ->label('Kode Paket')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nama_paket')
                    ->label('Nama Paket')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('jenjang')
                    ->colors([
                        'info' => 'SD',
                        'success' => 'SMP',
                        'warning' => 'SMA',
                        'primary' => 'UMUM',
                    ]),
                Tables\Columns\TextColumn::make('sekolah.nama_sekolah')
                    ->label('Sekolah')
                    ->placeholder('GLOBAL')
                    ->sortable()
                    ->toggleable()
                    ->visible(fn () => auth()->user()->isSuperAdmin()),
                Tables\Columns\TextColumn::make('mapel_items_count')
                    ->label('Mapel')
                    ->counts('mapelItems')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_soal')
                    ->label('Total Soal')
                    ->getStateUsing(fn($record) => $record->mapelItems->sum(function ($item) {
                        return $item->jumlah_soal; // Untuk ACAK dan MANUAL (jika logic manual disimpan di jumlah_soal juga, atau hitung soal_ids)
                        // Note: Di Model PaketTryoutMapel, kita perlu pastikan saat save Mode MANUAL, jumlah_soal diupdate sesuai count(soal_ids).
                        // Atau hitung manual disini:
                        if ($item->mode === 'MANUAL' && !empty($item->soal_ids)) {
                            return count($item->soal_ids);
                        }
                        return $item->jumlah_soal;
                    })),
                Tables\Columns\TextColumn::make('total_waktu_calculated')
                    ->label('Total Waktu')
                    ->getStateUsing(fn($record) => $record->mapelItems->sum('waktu_mapel') . ' menit'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                Tables\Columns\TextColumn::make('jadwal_count')
                    ->label('Jadwal')
                    ->counts('jadwal')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('jenjang')
                    ->options([
                        'SD' => 'SD',
                        'SMP' => 'SMP',
                        'SMA' => 'SMA',
                        'UMUM' => 'UMUM',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
            ])
            ->actions([
                Tables\Actions\Action::make('lihat_soal')
                    ->label('Lihat Soal')
                    ->icon('heroicon-o-document-magnifying-glass')
                    ->color('info')
                    ->url(fn($record) => static::getUrl('lihat-soal', ['record' => $record])),
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
            'index' => Pages\ListPaketTryouts::route('/'),
            'create' => Pages\CreatePaketTryout::route('/create'),
            'edit' => Pages\EditPaketTryout::route('/{record}/edit'),
            'lihat-soal' => Pages\LihatSoal::route('/{record}/lihat-soal'),
        ];
    }
}

