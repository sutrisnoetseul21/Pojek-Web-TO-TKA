<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\PesertaJadwal;
use App\Models\JadwalTryout;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;

class StatusPeserta extends Page implements HasTable, HasForms
{
    use InteractsWithTable, InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Status Peserta';
    protected static ?string $title = 'Status Peserta';
    protected static ?string $navigationGroup = 'Monitoring Ujian';
    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.status-peserta';

    // State untuk filter
    public ?array $filterData = ['status_test' => 'all'];

    public function mount(): void
    {
        $this->form->fill([
            'status_test' => 'all',
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
                Select::make('status_test')
                    ->label('Status Test')
                    ->options([
                        'all' => 'All',
                        'registered' => 'Login',
                        'started' => 'Sedang Dikerjakan',
                        'completed' => 'Test Selesai',
                    ])
                    ->native(false)
                    ->selectablePlaceholder(false),
            ])
            ->statePath('filterData');
    }

    public function applyFilter()
    {
        // Livewire re-renders automatically
        // table query will react to $this->filterData
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
            ->whereIn('jadwal_tryout_id', $activeJadwalIds)
            ->with(['user', 'jadwalTryout', 'currentMapel']); // Eager load

        // 3. Filter berdasarkan status
        $selectedStatus = $this->filterData['status_test'] ?? 'all';
        if ($selectedStatus !== 'all') {
            $query->where('status', $selectedStatus);
        }

        return $table
            ->query($query)
            ->columns([
                TextColumn::make('index')
                    ->label('No')
                    ->rowIndex(),
                TextColumn::make('status')
                    ->label('Status Test')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'registered' => 'warning', // Login (Kuning)
                        'started' => 'danger',   // Sedang Dikerjakan (Merah)
                        'completed' => 'success', // Tes Selesai (Hijau)
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'registered' => 'Login',
                        'started' => 'Sedang Dikerjakan',
                        'completed' => 'Tes Selesai',
                        default => $state,
                    })
                    ->sortable(),
                TextColumn::make('user.username')
                    ->label('Username')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.nama_lengkap')
                    ->label('Nama Peserta')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('currentMapel.nama_mapel')
                    ->label('Sub Test Terakhir')
                    ->default('-')
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Aktivitas Terakhir')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('sisa_waktu')
                    ->label('Sisa Waktu')
                    ->formatStateUsing(fn ($state) => $state ? ceil($state / 60) . 'm' : '-')
                    ->sortable(),
            ])
            ->actions([
                \Filament\Tables\Actions\Action::make('selesai_tes')
                    ->label('Selesai Tes')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Selesai Tes Paksa')
                    ->modalDescription('Apakah Anda yakin ingin menyelesaikan tes paksa untuk peserta ini? Status peserta akan berubah menjadi Selesai.')
                    ->visible(fn (PesertaJadwal $record) => $record->status === 'started')
                    ->action(function (PesertaJadwal $record) {
                        $record->update([
                            'status' => 'completed',
                            'waktu_selesai' => now(),
                        ]);

                        Notification::make()
                            ->title('Status Berhasil Diubah')
                            ->body('Peserta dipaksa menyelesaikan tes.')
                            ->success()
                            ->send();
                    })
            ])
            ->emptyStateHeading('Tidak ada data peserta untuk status ini.')
            ->poll('20s');
    }
}
