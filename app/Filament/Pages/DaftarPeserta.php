<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\PesertaJadwal;
use App\Models\JadwalTryout;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;

class DaftarPeserta extends Page implements HasTable, HasForms
{
    use InteractsWithTable, InteractsWithForms, \App\Traits\HasProktorFilter;

    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static ?string $navigationLabel = 'Daftar Peserta';
    protected static ?string $title = 'Daftar Peserta';
    protected static ?string $navigationGroup = 'Monitoring Ujian';
    protected static ?int $navigationSort = 2;
    protected static string $view = 'filament.pages.daftar-peserta';

    // State untuk filter
    public ?array $filterData = ['kelompok' => 'all', 'kelas_id' => 'all'];

    public function mount(): void
    {
        $this->form->fill([
            'kelompok' => 'all',
            'kelas_id' => 'all',
        ]);
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user->hasRole('super_admin') || $user->hasPermissionTo('manage_monitoring');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('kelompok')
                    ->label('Kelompok/Kelas')
                    ->options(function () {
                        // Ambil sesi dari jadwal yang aktif hari ini
                        $sessions = JadwalTryout::where('is_active', true)
                            ->where('tgl_mulai', '<=', now()->endOfDay())
                            ->where('tgl_selesai', '>=', now()->startOfDay())
                            ->get()
                            ->pluck('nama_sesi', 'nama_sesi')
                            ->filter() // Buang null/empty
                            ->toArray();

                        // Sortir agar rapi
                        ksort($sessions);

                        return array_merge(['all' => 'Semua Kelas'], $sessions);
                    })
                    ->native(false)
                    ->selectablePlaceholder(false),

                Select::make('kelas_id')
                    ->label('Kelas')
                    ->options(function () {
                        return \App\Models\Kelas::pluck('nama_kelas', 'id')->toArray();
                    })
                    ->placeholder('Semua Kelas')
                    ->native(false),
            ])
            ->statePath('filterData');
    }

    public function applyFilter()
    {
        // Livewire re-renders automatically
        // table query will reactive to $this->filterData
    }

    public function table(Table $table): Table
    {
        // 1. Ambil Jadwal aktif hari ini
        $activeJadwalIds = JadwalTryout::where('is_active', true)
            ->where('tgl_mulai', '<=', now()->endOfDay())
            ->where('tgl_selesai', '>=', now()->startOfDay())
            ->pluck('id');

        // 2. Query data peserta jadwal
        $query = PesertaJadwal::query()
            ->whereIn('jadwal_tryout_id', $activeJadwalIds);

        $query = $this->applyProktorFilter($query);

        // 3. Filter berdasarkan kelompok (nama_sesi)
        $selectedKelompok = $this->filterData['kelompok'] ?? 'all';
        if ($selectedKelompok !== 'all') {
            $query->whereHas('jadwalTryout', function (Builder $q) use ($selectedKelompok) {
                $q->where('nama_sesi', $selectedKelompok);
            });
        }

        // 4. Filter berdasarkan Kelas
        $selectedKelas = $this->filterData['kelas_id'] ?? 'all';
        if ($selectedKelas && $selectedKelas !== 'all') {
            $query->whereHas('user', function ($q) use ($selectedKelas) {
                $q->where('kelas_id', $selectedKelas);
            });
        }

        return $table
            ->query($query)
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('index')
                    ->label('No')
                    ->rowIndex(),
                TextColumn::make('user.username')
                    ->label('Username')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.nama_lengkap')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('jadwalTryout.nama_sesi')
                    ->label('Kelompok')
                    ->badge()
                    ->color('info')
                    ->sortable(),
                TextColumn::make('user.nomor_peserta')
                    ->label('NIK/No. Peserta')
                    ->searchable()
                    ->sortable()
                    ->default('-'),
            ])
            ->emptyStateHeading('Tidak ada data peserta untuk sesi ini.');
    }
}
