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
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class HasilTryout extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationLabel = 'Hasil Tryout';
    protected static ?string $title = 'Laporan Hasil Tryout';
    protected static ?string $navigationGroup = 'Laporan / Hasil';
    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.hasil-tryout';

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
                Section::make('Filter Laporan Akhir')
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
                    ->with(['user', 'user.kelas', 'jadwalTryout'])
                    ->whereIn('status', ['completed', 'timeout']);

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
                TextColumn::make('ranking')
                    ->label('Rank')
                    ->getStateUsing(fn ($record, $rowLoop) => $rowLoop->iteration)
                    ->alignCenter(),
                TextColumn::make('user.nama_lengkap')
                    ->label('Nama Peserta')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.kelas.nama_kelas')
                    ->label('Kelas'),
                TextColumn::make('skor_total')
                    ->label('Skor')
                    ->sortable()
                    ->alignRight()
                    ->getStateUsing(fn($record) => $record->nilai_akhir ?? 0), // Assuming there's a nilai_akhir column or logic
                TextColumn::make('stats')
                    ->label('B / S / K')
                    ->getStateUsing(fn($record) => ($record->jumlah_benar ?? 0) . ' / ' . ($record->jumlah_salah ?? 0) . ' / ' . ($record->jumlah_kosong ?? 0))
                    ->alignCenter(),
                TextColumn::make('waktu_selesai')
                    ->label('Waktu Submit')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('nilai_akhir', 'desc')
            ->filters([])
            ->actions([
                Action::make('detail_hasil')
                    ->label('Detail')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => "#") // Placeholder for detail view
            ])
            ->bulkActions([]);
    }

    protected function getStats(): array
    {
        $jadwalId = $this->data['jadwal_id'] ?? null;
        if (!$jadwalId) return [];

        $query = PesertaJadwal::where('jadwal_tryout_id', $jadwalId)
            ->whereIn('status', ['completed', 'timeout']);
            
        return [
            'avg' => round($query->avg('nilai_akhir') ?? 0, 2),
            'max' => $query->max('nilai_akhir') ?? 0,
            'min' => $query->min('nilai_akhir') ?? 0,
            'count' => $query->count(),
        ];
    }
}
