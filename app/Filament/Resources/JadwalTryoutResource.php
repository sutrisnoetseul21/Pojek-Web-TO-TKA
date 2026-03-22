<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JadwalTryoutResource\Pages;
use App\Filament\Resources\JadwalTryoutResource\RelationManagers;
use App\Models\JadwalTryout;
use App\Models\PaketTryout;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class JadwalTryoutResource extends Resource
{
    protected static ?string $model = JadwalTryout::class;

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        return $user->hasRole('super_admin') || $user->hasPermissionTo('manage_soal');
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if ($user->isAdmin() && $user->sekolah_id) {
            $query->where('sekolah_id', $user->sekolah_id);
        }

        return $query;
    }

    protected static ?string $navigationLabel = 'Jadwal Ujian';
    protected static ?string $modelLabel = 'Jadwal Ujian';
    protected static ?string $pluralModelLabel = 'Jadwal Ujian';
    protected static ?string $navigationGroup = 'Ujian';
    protected static ?int $navigationSort = 2;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Jadwal')
                    ->description('Tentukan waktu pelaksanaan tryout')
                    ->schema([
                        Forms\Components\Select::make('sekolah_id')
                            ->label('Sekolah')
                            ->relationship('sekolah', 'nama_sekolah')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->disabled(fn () => auth()->user()->isAdmin())
                            ->dehydrated()
                            ->default(fn () => auth()->user()->sekolah_id)
                            ->afterStateUpdated(fn (Forms\Set $set) => $set('paket_tryout_id', null))
                            ->afterStateUpdated(fn (Forms\Set $set) => $set('kelases', [])),

                        Forms\Components\Select::make('jenjang')
                            ->label('Jenjang')
                            ->options([
                                'SD' => 'SD',
                                'SMP' => 'SMP',
                                'SMA' => 'SMA',
                                'SMK' => 'SMK',
                                'UMUM' => 'UMUM',
                            ])
                            ->live()
                            ->dehydrated(false)
                            ->visible(fn () => ! (auth()->user()->isAdmin() && auth()->user()->jenjang))
                            ->afterStateHydrated(function ($set, $record) {
                                if ($record && $record->paketTryout) {
                                    $set('jenjang', $record->paketTryout->jenjang);
                                }
                            }),

                        Forms\Components\Select::make('tingkat')
                            ->label('Tingkat')
                            ->options(function (Forms\Get $get) {
                                $jenjang = $get('jenjang') ?? (auth()->user()->isAdmin() ? auth()->user()->jenjang : null);
                                return match ($jenjang) {
                                    'SD' => ['1'=>'1', '2'=>'2', '3'=>'3', '4'=>'4', '5'=>'5', '6'=>'6'],
                                    'SMP' => ['7'=>'7', '8'=>'8', '9'=>'9'],
                                    'SMA', 'SMK' => ['10'=>'10', '11'=>'11', '12'=>'12'],
                                    default => []
                                };
                            })
                            ->live()
                            ->dehydrated(false)
                            ->placeholder('-- Pilih Tingkat --')
                            ->afterStateHydrated(function ($set, $record) {
                                if ($record && $record->paketTryout) {
                                    $set('tingkat', $record->paketTryout->tingkat);
                                }
                            }),

                        Forms\Components\Select::make('paket_tryout_id')
                            ->label('Paket Ujian')
                            ->options(function (Forms\Get $get) {
                                $sekolahId = $get('sekolah_id');
                                $jenjang = $get('jenjang') ?? (auth()->user()->isAdmin() ? auth()->user()->jenjang : null);
                                $tingkat = $get('tingkat');

                                return PaketTryout::where('is_active', true)
                                    ->where(function ($q) use ($sekolahId) {
                                        $q->whereNull('sekolah_id')
                                          ->when($sekolahId, fn ($query) => $query->orWhere('sekolah_id', $sekolahId));
                                    })
                                    ->when($jenjang, fn ($q) => $q->where('jenjang', $jenjang))
                                    ->when($tingkat, fn ($q) => $q->where('tingkat', $tingkat))
                                    ->get()
                                    ->mapWithKeys(fn ($p) => [$p->id => ($p->kode ? "[{$p->kode}] " : "") . $p->nama_paket]);
                            })
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $paket = \App\Models\PaketTryout::find($state);
                                    if ($paket) {
                                        $set('jenjang', $paket->jenjang);
                                        $set('tingkat', $paket->tingkat);
                                    }
                                }
                            })
                            ->helperText('Pilih paket ujian yang akan dijadwalkan'),
                        Forms\Components\Checkbox::make('pilih_semua_kelas')
                            ->label('Pilih Semua Kelas di Tingkat Ini')
                            ->live()
                            ->dehydrated(false)
                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                if ($state) {
                                    $sekolahId = $get('sekolah_id') ?? auth()->user()->sekolah_id;
                                    $tingkat = $get('tingkat');
                                    
                                    $ids = \App\Models\Kelas::where('sekolah_id', $sekolahId)
                                        ->when($tingkat, fn($q) => $q->where('tingkat', $tingkat))
                                        ->pluck('id')
                                        ->toArray();
                                        
                                    $set('kelases', $ids);
                                } else {
                                    $set('kelases', []);
                                }
                            }),

                        Forms\Components\Select::make('kelases')
                            ->label('Target Kelas')
                            ->relationship('kelases', 'nama_kelas', modifyQueryUsing: function (Builder $query, Forms\Get $get) {
                                $query->where('sekolah_id', $get('sekolah_id'));
                                if ($get('tingkat')) {
                                    $query->where('tingkat', $get('tingkat'));
                                }
                                return $query;
                            })
                            ->multiple()
                            ->required()
                            ->preload()
                            ->searchable()
                            ->live()
                            ->helperText('Pilih satu atau lebih kelas yang wajib mengikuti jadwal ini'),
                        Forms\Components\TextInput::make('nama_sesi')
                            ->label('Nama Sesi')
                            ->placeholder('Contoh: Sesi 1 - Pagi')
                            ->required()
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('Waktu Pelaksanaan')
                    ->schema([
                        Forms\Components\DateTimePicker::make('tgl_mulai')
                            ->label('Tanggal & Jam Mulai')
                            ->required()
                            ->native(false)
                            ->displayFormat('d M Y, H:i'),
                        Forms\Components\DateTimePicker::make('tgl_selesai')
                            ->label('Tanggal & Jam Selesai')
                            ->required()
                            ->native(false)
                            ->displayFormat('d M Y, H:i')
                            ->afterOrEqual('tgl_mulai'),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Jadwal Aktif')
                            ->default(true)
                            ->helperText('Jadwal aktif akan tampil untuk siswa'),
                        Forms\Components\Toggle::make('is_token_active')
                            ->label('Izinkan Rilis Token')
                            ->default(true)
                            ->helperText('Jika non-aktif, proktor tidak bisa merilis token di monitoring.'),
                    ])->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('10s') // 🔄 Auto-Refresh setiap 10 detik agar sinkron dengan status tes
            ->columns([
                Tables\Columns\TextColumn::make('index')
                    ->label('No')
                    ->rowIndex(),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Status Tes'),
                Tables\Columns\ToggleColumn::make('is_token_active')
                    ->label('Toggle Token')
                    ->disabled(fn (JadwalTryout $record): bool => !$record->is_active),
                Tables\Columns\TextColumn::make('paketTryout.kode')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('paketTryout.nama_paket')
                    ->label('Paket TO')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sekolah.nama_sekolah')
                    ->label('Sekolah')
                    ->searchable()
                    ->sortable()
                    ->visible(fn () => auth()->user()->isSuperAdmin()),
                Tables\Columns\TextColumn::make('nama_sesi')
                    ->label('Sesi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('kelases.nama_kelas')
                    ->label('Kelas')
                    ->badge()
                    ->separator(', '),
                Tables\Columns\TextColumn::make('token')
                    ->label('Token')
                    ->badge()
                    ->color('warning')
                    ->copyable()
                    ->searchable()
                    ->fontFamily('mono')
                    ->state(function (JadwalTryout $record) {
                        return $record->is_token_active ? $record->token : '-';
                    }),
                Tables\Columns\TextColumn::make('tgl_mulai')
                    ->label('Mulai')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tgl_selesai')
                    ->label('Selesai')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->getStateUsing(fn($record) => $record->status)
                    ->colors([
                        'info' => 'AKAN_DATANG',
                        'success' => 'BERLANGSUNG',
                        'gray' => 'SELESAI',
                    ])
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'AKAN_DATANG' => 'Akan Datang',
                        'BERLANGSUNG' => 'Berlangsung',
                        'SELESAI' => 'Selesai',
                        default => $state,
                    }),
            ])
            ->defaultSort('tgl_mulai', 'desc')
            ->filters([
                Tables\Filters\Filter::make('advanced_filters')
                    ->columnSpanFull()
                    ->form([
                        Forms\Components\Grid::make(5)
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
                                
                                Forms\Components\Select::make('tingkat')
                                    ->label('Tingkat')
                                    ->placeholder('All')
                                    ->options(function (Forms\Get $get) {
                                        $jenjang = $get('jenjang') ?? (auth()->user()->isAdmin() ? auth()->user()->jenjang : null);
                                        return match ($jenjang) {
                                            'SD' => ['1'=>'1', '2'=>'2', '3'=>'3', '4'=>'4', '5'=>'5', '6'=>'6'],
                                            'SMP' => ['7'=>'7', '8'=>'8', '9'=>'9'],
                                            'SMA', 'SMK' => ['10'=>'10', '11'=>'11', '12'=>'12'],
                                            default => [
                                                '1'=>'1', '2'=>'2', '3'=>'3', '4'=>'4', '5'=>'5', '6'=>'6',
                                                '7'=>'7', '8'=>'8', '9'=>'9', '10'=>'10', '11'=>'11', '12'=>'12'
                                            ]
                                        };
                                    })
                                    ->live(),

                                Forms\Components\Select::make('paket_tryout_id')
                                    ->label('Paket Ujian')
                                    ->placeholder('All')
                                    ->options(function (Forms\Get $get) {
                                        $user = auth()->user();
                                        $jenjang = $get('jenjang') ?? ($user->isAdmin() ? $user->jenjang : null);
                                        $tingkat = $get('tingkat');
                                        
                                        return \App\Models\PaketTryout::where('is_active', true)
                                            ->when($jenjang, fn($q) => $q->where('jenjang', $jenjang))
                                            ->when($tingkat, fn($q) => $q->where('tingkat', $tingkat))
                                            ->get()
                                            ->mapWithKeys(fn($p) => [$p->id => ($p->kode ? "[{$p->kode}] " : "") . $p->nama_paket]);
                                    })
                                    ->live()
                                    ->searchable()
                                    ->preload(),

                                Forms\Components\Select::make('is_active')
                                    ->label('Status')
                                    ->placeholder('All')
                                    ->options([
                                        '1' => 'Aktif',
                                        '0' => 'Tidak Aktif',
                                    ]),

                                Forms\Components\Select::make('sekolah_id')
                                    ->label('Sekolah')
                                    ->placeholder('All')
                                    ->options(fn() => \App\Models\Sekolah::pluck('nama_sekolah', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->visible(fn () => auth()->user()->isSuperAdmin()),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['jenjang'], fn ($q, $j) => $q->whereHas('paketTryout', fn($pq) => $pq->where('jenjang', $j)))
                            ->when($data['tingkat'], fn ($q, $t) => $q->whereHas('paketTryout', fn($pq) => $pq->where('tingkat', $t)))
                            ->when($data['paket_tryout_id'], fn ($q, $pId) => $q->where('paket_tryout_id', $pId))
                            ->when($data['is_active'] !== null && $data['is_active'] !== '', fn ($q) => $q->where('is_active', $data['is_active']))
                            ->when($data['sekolah_id'], fn ($q, $sId) => $q->where('sekolah_id', $sId));
                    })
            ], layout: \Filament\Tables\Enums\FiltersLayout::AboveContent)
            ->filtersFormColumns(5)
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
            RelationManagers\RuanganProktorRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJadwalTryouts::route('/'),
            'create' => Pages\CreateJadwalTryout::route('/create'),
            'view' => Pages\ViewJadwalTryout::route('/{record}'),
            'edit' => Pages\EditJadwalTryout::route('/{record}/edit'),
        ];
    }
}

