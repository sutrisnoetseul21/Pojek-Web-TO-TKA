<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Sekolah;
use App\Models\JadwalTryout;
use App\Models\PesertaJadwal;
use App\Models\Kelas;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Illuminate\Database\Eloquent\Builder;

class HasilSementara extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationLabel = 'Hasil Sementara';
    protected static ?string $title = 'Hasil Sementara Ujian';
    protected static ?string $navigationGroup = 'Laporan / Hasil';
    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.hasil-sementara';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user->hasRole('super_admin') || $user->hasPermissionTo('manage_laporan');
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Filter Laporan')
                    ->schema([
                        Select::make('sekolah_id')
                            ->label('Sekolah')
                            ->options(Sekolah::pluck('nama_sekolah', 'id'))
                            ->searchable()
                            ->preload()
                            ->live()
                            ->default(auth()->user()->sekolah_id)
                            ->disabled(auth()->user()->hasRole('admin')),
                        Select::make('jadwal_id')
                            ->label('Jadwal Tryout')
                            ->options(function (callable $get) {
                                $sekolahId = $get('sekolah_id');
                                return JadwalTryout::when($sekolahId, fn($q) => $q->where('sekolah_id', $sekolahId))
                                    ->pluck('nama_sesi', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->live(),
                        Select::make('kelas_id')
                            ->label('Kelas (Opsional)')
                            ->options(function (callable $get) {
                                $sekolahId = $get('sekolah_id');
                                if (!$sekolahId) return [];
                                return Kelas::where('sekolah_id', $sekolahId)->pluck('nama_kelas', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->live(),
                    ])->columns(3),
            ])
            ->statePath('data');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(function () {
                $query = PesertaJadwal::query()
                    ->with(['user', 'user.kelas', 'jadwalTryout']);

                $sekolahId = $this->data['sekolah_id'] ?? auth()->user()->sekolah_id;
                $jadwalId = $this->data['jadwal_id'] ?? null;
                $kelasId = $this->data['kelas_id'] ?? null;

                if ($sekolahId) {
                    $query->whereHas('user', fn($q) => $q->where('sekolah_id', $sekolahId));
                }

                if ($jadwalId) {
                    $query->where('jadwal_tryout_id', $jadwalId);
                }

                if ($kelasId) {
                    $query->whereHas('user', fn($q) => $q->where('kelas_id', $kelasId));
                }

                return $query;
            })
            ->columns([
                TextColumn::make('user.nama_lengkap')
                    ->label('Nama Peserta')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.kelas.nama_kelas')
                    ->label('Kelas'),
                TextColumn::make('jawaban_count')
                    ->label('Terjawab')
                    ->counts('jawabanPeserta'),
                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'gray' => 'registered',
                        'info' => 'started',
                        'success' => 'completed',
                        'warning' => 'timeout',
                        'danger' => 'disconnected',
                    ])
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                TextColumn::make('waktu_mulai')
                    ->label('Mulai')
                    ->dateTime('d/m/y H:i'),
                TextColumn::make('waktu_selesai')
                    ->label('Selesai')
                    ->dateTime('d/m/y H:i'),
            ])
            ->filters([])
            ->actions([])
            ->bulkActions([]);
    }
}
