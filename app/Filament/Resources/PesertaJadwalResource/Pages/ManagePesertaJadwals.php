<?php

namespace App\Filament\Resources\PesertaJadwalResource\Pages;

use App\Filament\Resources\PesertaJadwalResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

use App\Models\JadwalTryout;
use App\Models\PesertaJadwal;
use App\Models\User;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ManagePesertaJadwals extends ManageRecords
{
    protected static string $resource = PesertaJadwalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('sinkronisasi_peserta')
                ->label('Sinkronisasi Peserta')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Sinkronisasi Data Peserta')
                ->modalDescription('Aksi ini akan men-generate data peserta yang belum mulai untuk jadwal yang aktif. Lanjutkan?')
                ->action(function () {
                    // 1. Ambil jadwal aktif yang belum selesai
                    $jadwals = JadwalTryout::where('is_active', true)
                        ->where('tgl_selesai', '>=', now())
                        ->get();

                    $countNew = 0;

                    foreach ($jadwals as $jadwal) {
                        // 2. Ambil kelas dari Jadwal
                        $kelasIds = $jadwal->kelases()->pluck('kelas.id');
                        
                        if ($kelasIds->isNotEmpty()) {
                            // 3. Ambil Peserta di Kelas tersebut
                            $userIds = User::whereIn('kelas_id', $kelasIds)
                                ->where('role', 'peserta')
                                ->pluck('id');

                            foreach ($userIds as $userId) {
                                $record = PesertaJadwal::firstOrCreate([
                                    'user_id' => $userId,
                                    'jadwal_tryout_id' => $jadwal->id,
                                ], [
                                    'token_used' => $jadwal->token,
                                    'status' => 'registered',
                                ]);

                                if ($record->wasRecentlyCreated) {
                                    $countNew++;
                                }
                            }
                        }
                    }

                    \Filament\Notifications\Notification::make()
                        ->title('Sinkronisasi Berhasil')
                        ->body($countNew . ' peserta baru berhasil disinkronkan ke jadwal aktif.')
                        ->success()
                        ->send();
                }),
        ];
    }
}
