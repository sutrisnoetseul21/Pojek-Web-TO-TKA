<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KartuPesertaResource\Pages;
use App\Filament\Resources\KartuPesertaResource\Pages\PreviewKartuPeserta;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection;

class KartuPesertaResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationLabel = 'Kartu Login';
    protected static ?string $modelLabel = 'Kartu Peserta';
    protected static ?string $pluralModelLabel = 'Kartu Peserta';
    protected static ?string $navigationGroup = 'Administrasi Tes';
    protected static ?int $navigationSort = 4;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        return $user->isSuperAdmin() || $user->hasPermissionTo('manage_peserta');
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->where('role', '=', 'peserta');

        $user = auth()->user();

        if ($user->isAdmin() && $user->sekolah_id) {
            $query->where('sekolah_id', $user->sekolah_id);
        }

        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Read-only view for Kartu Peserta
                Forms\Components\TextInput::make('username')->disabled(),
                Forms\Components\TextInput::make('nama_lengkap')->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('username')
                    ->label('Username')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nama_lengkap')
                    ->label('Nama')
                    ->searchable()
                    ->placeholder('Belum diisi'),
                Tables\Columns\TextColumn::make('nisn')
                    ->label('NISN')
                    ->searchable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('sekolahRelation.nama_sekolah')
                    ->label('Sekolah')
                    ->searchable(),
                Tables\Columns\TextColumn::make('kelas.nama_kelas')
                    ->label('Kelas')
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('advanced_filters')
                    ->columnSpanFull()
                    ->form([
                        Forms\Components\Grid::make(5)
                            ->schema([
                                Forms\Components\Select::make('sekolah_id')
                                    ->label('Sekolah')
                                    ->options(fn() => \App\Models\Sekolah::pluck('nama_sekolah', 'id'))
                                    ->placeholder('All')
                                    ->live()
                                    ->searchable()
                                    ->preload()
                                    ->visible(fn () => auth()->user()->isSuperAdmin()),

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
                                        $user = auth()->user();
                                        $jenjang = $get('jenjang') ?? ($user->isAdmin() ? $user->jenjang : null);
                                        return match ($jenjang) {
                                            'SD' => ['1'=>'1', '2'=>'2', '3'=>'3', '4'=>'4', '5'=>'5', '6'=>'6'],
                                            'SMP' => ['7'=>'7', '8'=>'8', '9'=>'9'],
                                            'SMA', 'SMK' => ['10'=>'10', '11'=>'11', '12'=>'12'],
                                            default => []
                                        };
                                    })
                                    ->live(),

                                Forms\Components\Select::make('kelas_id')
                                    ->label('Kelas')
                                    ->placeholder('All')
                                    ->options(function (Forms\Get $get) {
                                        $user = auth()->user();
                                        $sekolahId = $get('sekolah_id') ?? ($user->isAdmin() ? $user->sekolah_id : null);
                                        $tingkat = $get('tingkat');
                                        $jenjang = $get('jenjang') ?? ($user->isAdmin() ? $user->jenjang : null);

                                        return \App\Models\Kelas::query()
                                            ->when($sekolahId, fn($q) => $q->where('sekolah_id', $sekolahId))
                                            ->when($tingkat, fn($q) => $q->where('tingkat', $tingkat))
                                            ->when($jenjang, fn($q) => $q->where('jenjang', $jenjang))
                                            ->pluck('nama_kelas', 'id');
                                    })
                                    ->live()
                                    ->searchable()
                                    ->preload(),

                                Forms\Components\Select::make('jadwal_tryout_id')
                                    ->label('Jadwal Ujian')
                                    ->placeholder('All')
                                    ->options(function (Forms\Get $get) {
                                        $sekolahId = $get('sekolah_id') ?? (auth()->user()->isAdmin() ? auth()->user()->sekolah_id : null);
                                        
                                        return \App\Models\JadwalTryout::where('is_active', true)
                                            ->when($sekolahId, fn($q) => $q->where('sekolah_id', $sekolahId))
                                            ->with('paketTryout')
                                            ->get()
                                            ->mapWithKeys(function ($jadwal) {
                                                $namaUjian = $jadwal->paketTryout ? $jadwal->paketTryout->nama_paket : '—';
                                                $sesi = $jadwal->nama_sesi ? " - Sesi: {$jadwal->nama_sesi}" : '';
                                                return [$jadwal->id => "{$namaUjian}{$sesi}"];
                                            });
                                    })
                                    ->searchable()
                                    ->preload(),
                            ]),
                    ])
                    ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data): \Illuminate\Database\Eloquent\Builder {
                        $user = auth()->user();
                        $jenjang = $data['jenjang'] ?? ($user->isAdmin() ? $user->jenjang : null);

                        return $query
                            ->when($data['sekolah_id'], fn ($q, $sId) => $q->where('sekolah_id', $sId))
                            ->when($data['kelas_id'], fn ($q, $kId) => $q->where('kelas_id', $kId))
                            ->when($data['tingkat'], fn ($q, $t) => $q->whereHas('kelas', fn($sub) => $sub->where('tingkat', $t)))
                            ->when($jenjang, fn ($q, $j) => $q->whereHas('kelas', fn($sub) => $sub->where('jenjang', $j)))
                            ->when($data['jadwal_tryout_id'], fn ($q, $jId) => $q->whereHas('jadwalTryouts', fn($sub) => $sub->where('jadwal_tryout_id', $jId)));
                    })
            ], layout: \Filament\Tables\Enums\FiltersLayout::AboveContent)
            ->filtersFormColumns(5)
            ->headerActions([])
            ->actions([
                Tables\Actions\Action::make('cetak_satuan')
                    ->label('Cetak')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->form([
                        Forms\Components\Select::make('jenis_cetak')
                            ->label('Jenis Cetakan')
                            ->options([
                                'tanpa_jadwal' => '1. Cetak hanya kartu login tanpa jadwal ujian',
                                'dengan_jadwal' => '2. Cetak kartu peserta dengan jadwal ujian',
                            ])
                            ->required()
                            ->default('tanpa_jadwal')
                            ->live(),
                        Forms\Components\Select::make('jadwal_tryout_id')
                            ->label('Jadwal Ujian')
                            ->options(function () {
                                return \App\Models\JadwalTryout::orderBy('tgl_mulai', 'desc')
                                    ->with('paketTryout')
                                    ->get()
                            ->mapWithKeys(function ($jadwal) {
                                $namaUjian = $jadwal->paketTryout ? $jadwal->paketTryout->nama_paket : '—';
                                $sesi = $jadwal->nama_sesi ? " - Sesi: {$jadwal->nama_sesi}" : '';
                                return [$jadwal->id => "{$namaUjian}{$sesi}"];
                            });
                            })
                            ->required(fn (Forms\Get $get) => $get('jenis_cetak') === 'dengan_jadwal')
                            ->visible(fn (Forms\Get $get) => $get('jenis_cetak') === 'dengan_jadwal')
                            ->searchable()
                            ->preload(),
                    ])
                    ->action(function (array $data, $record) {
                        return redirect()->to(Pages\PreviewKartuPeserta::getUrl([
                            'ids'              => $record->id,
                            'jenis_cetak'      => $data['jenis_cetak'],
                            'jadwal_tryout_id' => $data['jadwal_tryout_id'] ?? null,
                        ]));
                    }),
            ])
            ->bulkActions([
                BulkAction::make('cetak_kartu_terpilih')
                    ->label('Cetak Terpilih')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->form([
                        Forms\Components\Select::make('jenis_cetak')
                            ->label('Jenis Cetakan')
                            ->options([
                                'tanpa_jadwal' => '1. Cetak hanya kartu login tanpa jadwal ujian',
                                'dengan_jadwal' => '2. Cetak kartu peserta dengan jadwal ujian',
                            ])
                            ->required()
                            ->default('tanpa_jadwal')
                            ->live(),
                        Forms\Components\Select::make('jadwal_tryout_id')
                            ->label('Jadwal Ujian')
                            ->options(function () {
                                return \App\Models\JadwalTryout::orderBy('tgl_mulai', 'desc')
                                    ->with('paketTryout')
                                    ->get()
                            ->mapWithKeys(function ($jadwal) {
                                $namaUjian = $jadwal->paketTryout ? $jadwal->paketTryout->nama_paket : '—';
                                $sesi = $jadwal->nama_sesi ? " - Sesi: {$jadwal->nama_sesi}" : '';
                                return [$jadwal->id => "{$namaUjian}{$sesi}"];
                            });
                            })
                            ->required(fn (Forms\Get $get) => $get('jenis_cetak') === 'dengan_jadwal')
                            ->visible(fn (Forms\Get $get) => $get('jenis_cetak') === 'dengan_jadwal')
                            ->searchable()
                            ->preload(),
                    ])
                    ->action(function (array $data, Collection $records) {
                        $ids = $records->pluck('id')->implode(',');
                        return redirect()->to(Pages\PreviewKartuPeserta::getUrl([
                            'ids'              => $ids,
                            'jenis_cetak'      => $data['jenis_cetak'],
                            'jadwal_tryout_id' => $data['jadwal_tryout_id'] ?? null,
                        ]));
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageKartuPesertas::route('/'),
            'preview' => Pages\PreviewKartuPeserta::route('/preview'),
        ];
    }
}
