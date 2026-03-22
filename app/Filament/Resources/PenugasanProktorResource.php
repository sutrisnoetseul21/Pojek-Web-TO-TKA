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

class PenugasanProktorResource extends Resource
{
    protected static ?string $model = JadwalRuanganProktor::class;

    protected static ?string $navigationLabel = 'Alokasi Pengawas';
    protected static ?string $modelLabel = 'Alokasi';
    protected static ?string $pluralModelLabel = 'Alokasi Pengawas';
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

        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('proktor_id')
                    ->label('Nama Pengawas')
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
                    ->searchable(),

                Forms\Components\Select::make('status')
                    ->options([
                        'active' => 'Aktif',
                        'inactive' => 'Tidak Aktif',
                    ])
                    ->required()
                    ->default('active')
                    ->native(false),

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
                    ->label('Nama Pengawas')
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
                    ->placeholder('Semua Kelas')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('proktor_id')
                    ->label('Nama Pengawas')
                    ->options(fn () => \App\Models\User::where('role', 'proktor')
                        ->when(auth()->user()->hasRole('admin'), fn ($q) => $q->where('sekolah_id', auth()->user()->sekolah_id))
                        ->pluck('nama_lengkap', 'id')
                        ->toArray())
                    ->searchable()
                    ->placeholder('Semua Pengawas')
                    ->visible(fn () => auth()->user()->hasRole(['super_admin', 'admin'])),
            ])
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
