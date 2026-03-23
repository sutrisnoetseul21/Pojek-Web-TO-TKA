<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PenugasanProktorResource\Pages;
use App\Models\JadwalRuanganProktor;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;

class PenugasanProktorResource extends Resource
{
    protected static ?string $model = JadwalRuanganProktor::class;

    protected static ?string $navigationLabel = 'Pengaturan Server Proktor';
    protected static ?string $modelLabel = 'Pengaturan Server Proktor';
    protected static ?string $pluralModelLabel = 'Pengaturan Server Proktor';
    protected static ?string $navigationGroup = 'Manajemen Proktor';
    protected static ?int $navigationSort = 2;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    public static function canViewAny(): bool
    {
        return true; // Semua bisa lihat (Admin & Proktor)
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if ($user->hasRole('proktor')) {
            $query->where('proktor_id', $user->id);
        } elseif ($user->hasRole('admin') && $user->sekolah_id) {
            $query->whereHas('jadwalTryout', fn ($q) => $q->where('sekolah_id', $user->sekolah_id));
        }

        // Tampilkan hanya 1 baris per kombinasi proktor+jadwal+ruangan
        $query->whereIn('id', function ($sub) use ($user) {
            $sub->selectRaw('MIN(id)')
                ->from('jadwal_ruangan_proktor')
                ->when($user->hasRole('proktor'), fn ($q) => $q->where('proktor_id', $user->id))
                ->groupBy('proktor_id', 'jadwal_tryout_id', 'ruangan_id');
        });

        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('proktor_id')
                    ->label('Nama Proktor')
                    ->options(function () {
                        return User::where('role', 'proktor')
                            ->when(auth()->user()->hasRole('admin'), fn ($q) => $q->where('sekolah_id', auth()->user()->sekolah_id))
                            ->pluck('nama_lengkap', 'id')
                            ->toArray();
                    })
                    ->required()
                    ->native(false)
                    ->searchable()
                    ->visible(fn () => auth()->user()->hasRole(['super_admin', 'admin'])),

                Forms\Components\Select::make('jadwal_tryout_id')
                    ->label('Jadwal Ujian')
                    ->options(function () {
                        $sekolahId = auth()->user()->sekolah_id;
                        return \App\Models\JadwalTryout::with('paketTryout')
                            ->when($sekolahId, fn ($q) => $q->where('sekolah_id', $sekolahId))
                            ->get()
                            ->mapWithKeys(fn ($j) => [$j->id => ($j->paketTryout->nama_paket ?? 'Tanpa Paket') . ' (' . ($j->nama_sesi ?? 'Sesi') . ')'])
                            ->toArray();
                    })
                    ->required()
                    ->live()
                    ->native(false)
                    ->searchable(),
                    
                Forms\Components\Select::make('ruangan_id')
                    ->label('Ruangan')
                    ->options(function () {
                        $sekolahId = auth()->user()->sekolah_id;
                        return \App\Models\Ruangan::when($sekolahId, fn ($q) => $q->where('sekolah_id', $sekolahId))
                            ->pluck('nama_ruangan', 'id')
                            ->toArray();
                    })
                    ->required()
                    ->native(false)
                    ->searchable(),

                Forms\Components\Select::make('kelas_id')
                    ->label('Kelas/Rombel')
                    ->options(function (Forms\Get $get) {
                        $jadwalId = $get('jadwal_tryout_id');
                        if (!$jadwalId) return [];

                        $jadwal = \App\Models\JadwalTryout::with(['kelases', 'paketTryout'])->find($jadwalId);
                        if (!$jadwal) return [];

                        return $jadwal->kelases()->count() > 0
                            ? $jadwal->kelases()->pluck('nama_kelas', 'kelas.id')->toArray()
                            : \App\Models\Kelas::where('sekolah_id', auth()->user()->sekolah_id)
                                ->when($jadwal->paketTryout?->tingkat, fn ($q, $t) => $q->where('tingkat', $t))
                                ->pluck('nama_kelas', 'id')
                                ->toArray();
                    })
                    ->nullable()
                    ->placeholder('Semua Kelas (Default)')
                    ->native(false)
                    ->searchable()
                    ->rules(function (Forms\Get $get, $record) {
                        $jadwalId = $get('jadwal_tryout_id');
                        $kelasId = $get('kelas_id');

                        // Hanya validasi jika kelas dipilih
                        if (!$kelasId || !$jadwalId) return [];

                        return [
                            Rule::unique('jadwal_ruangan_proktor', 'kelas_id')
                                ->where('jadwal_tryout_id', $jadwalId)
                                ->ignore($record?->id),
                        ];
                    })
                    ->validationMessages([
                        'unique' => 'Kelas ini sudah ditugaskan ke Proktor lain pada Jadwal Ujian yang sama.',
                    ]),

                Forms\Components\Hidden::make('status')
                    ->default('active'),

                Forms\Components\Textarea::make('catatan')
                    ->label('Catatan')
                    ->nullable()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('proktor.nama_lengkap')
                    ->label('Nama Proktor')
                    ->searchable()
                    ->sortable()
                    ->visible(fn () => auth()->user()->hasRole(['super_admin', 'admin'])),
                Tables\Columns\TextColumn::make('jadwalTryout.paketTryout.nama_paket')
                    ->label('Paket Ujian')
                    ->description(fn ($record) => $record->jadwalTryout ? $record->jadwalTryout->nama_sesi : '-')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ruangan.nama_ruangan')
                    ->label('Ruangan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('kelas.nama_kelas')
                    ->label('Kelas/Rombel')
                    ->state(function ($record) {
                        return \App\Models\JadwalRuanganProktor::with('kelas')
                            ->where('proktor_id', $record->proktor_id)
                            ->where('jadwal_tryout_id', $record->jadwal_tryout_id)
                            ->where('ruangan_id', $record->ruangan_id)
                            ->get()
                            ->map(fn ($r) => $r->kelas?->nama_kelas ?? 'Semua Kelas')
                            ->unique()
                            ->values()
                            ->toArray();
                    })
                    ->badge()
                    ->color('info')
                    ->separator(', '),
                Tables\Columns\TextColumn::make('jadwalTryout.status')
                    ->label('Jadwal Ujian')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'AKAN_DATANG' => 'warning',
                        'BERLANGSUNG' => 'success',
                        'SELESAI' => 'gray',
                        default => 'secondary',
                    }),
                Tables\Columns\TextColumn::make('jadwalTryout.is_active')
                    ->label('Status Ujian')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Aktif' : 'Tidak Aktif')
                    ->color(fn (bool $state): string => $state ? 'success' : 'danger'),
            ])
            ->filters([
                Tables\Filters\Filter::make('advanced_filters')
                    ->columnSpanFull()
                    ->form([
                        Forms\Components\Grid::make(4)
                            ->schema([
                                Forms\Components\Select::make('sekolah_id')
                                    ->label('Sekolah')
                                    ->placeholder('All')
                                    ->options(fn() => \App\Models\Sekolah::pluck('nama_sekolah', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->visible(fn () => auth()->user()->hasRole('super_admin'))
                                    ->live(),

                                Forms\Components\Select::make('jenjang')
                                    ->label('Jenjang')
                                    ->placeholder('All')
                                    ->options([
                                        'SD' => 'SD', 'SMP' => 'SMP', 'SMA' => 'SMA', 'SMK' => 'SMK', 'UMUM' => 'UMUM'
                                    ])
                                    ->live(),

                                Forms\Components\Select::make('tingkat')
                                    ->label('Tingkat')
                                    ->placeholder('All')
                                    ->options(function (Forms\Get $get) {
                                        $jenjang = $get('jenjang');
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

                                Forms\Components\Select::make('proktor_id')
                                    ->label('Nama Proktor')
                                    ->placeholder('All')
                                    ->options(function () {
                                        return \App\Models\User::where('role', 'proktor')
                                            ->when(auth()->user()->hasRole('admin'), fn ($q) => $q->where('sekolah_id', auth()->user()->sekolah_id))
                                            ->pluck('nama_lengkap', 'id')
                                            ->toArray();
                                    })
                                    ->searchable()
                                    ->visible(fn () => auth()->user()->hasRole(['super_admin', 'admin'])),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['sekolah_id'], fn ($q, $sId) => $q->whereHas('jadwalTryout', fn($jq) => $jq->where('sekolah_id', $sId)))
                            ->when($data['jenjang'], fn ($q, $j) => $q->whereHas('jadwalTryout', fn($jq) => $jq->whereHas('paketTryout', fn($pq) => $pq->where('jenjang', $j))))
                            ->when($data['tingkat'], fn ($q, $t) => $q->whereHas('jadwalTryout', fn($jq) => $jq->whereHas('paketTryout', fn($pq) => $pq->where('tingkat', $t))))
                            ->when($data['proktor_id'], fn ($q, $pId) => $q->where('proktor_id', $pId));
                    })
            ], layout: \Filament\Tables\Enums\FiltersLayout::AboveContent)
            ->filtersFormColumns(4)
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn () => auth()->user()->hasRole(['super_admin', 'admin'])),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => auth()->user()->hasRole(['super_admin', 'admin'])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPenugasanProktors::route('/'),
        ];
    }
}
