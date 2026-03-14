<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Sekolah;
use App\Models\JadwalTryout;
use App\Models\PesertaJadwal;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;

class MonitoringSesi extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Monitoring Sesi';
    protected static ?string $title = 'Monitoring Sesi Ujian';
    protected static ?string $navigationGroup = 'Monitoring Ujian';
    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.monitoring-sesi';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user->hasRole('super_admin') || $user->hasPermissionTo('manage_monitoring');
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Filter Monitoring')
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
                                    ->where('is_active', true)
                                    ->pluck('nama_sesi', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->live(),
                    ])->columns(2),
            ])
            ->statePath('data');
    }

    protected function getStats(): array
    {
        $jadwalId = $this->data['jadwal_id'] ?? null;
        $sekolahId = $this->data['sekolah_id'] ?? auth()->user()->sekolah_id;

        if (!$jadwalId) return [];

        $stats = PesertaJadwal::where('jadwal_tryout_id', $jadwalId)
            ->when($sekolahId, function($q) use ($sekolahId) {
                $q->whereHas('user', fn($u) => $u->where('sekolah_id', $sekolahId));
            })
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        return [
            'total' => array_sum($stats),
            'registered' => $stats['registered'] ?? 0,
            'started' => $stats['started'] ?? 0,
            'completed' => $stats['completed'] ?? 0,
            'problem' => ($stats['timeout'] ?? 0) + ($stats['disconnected'] ?? 0),
        ];
    }
}
