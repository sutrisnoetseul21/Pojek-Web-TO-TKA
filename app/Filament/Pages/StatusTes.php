<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\JadwalTryout;
use App\Models\Setting;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;

class StatusTes extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-check-circle';
    protected static ?string $navigationLabel = 'Status Tes';
    protected static ?string $title = 'Status Tes';
    protected static ?string $navigationGroup = 'Monitoring Ujian (Baru)';
    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.status-tes';

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user->hasRole('super_admin') || $user->hasPermissionTo('manage_monitoring');
    }

    public function table(Table $table): Table
    {
        return $table
            ->poll('10s') // 🔄 Auto Refresh setiap 10 detik agar status berubah otomatis
            ->query(JadwalTryout::query()
                ->where('is_active', true)
                ->where('tgl_mulai', '<=', now()->endOfDay())
                ->where('tgl_selesai', '>=', now()->startOfDay())
            )
            ->columns([
                TextColumn::make('index')
                    ->label('No')
                    ->rowIndex(),
                TextColumn::make('paketTryout.kode')
                    ->label('Kode')
                    ->searchable(),
                TextColumn::make('paketTryout.nama_paket')
                    ->label('Nama Tes')
                    ->searchable(),
                TextColumn::make('tgl_mulai')
                    ->label('Waktu Tes')
                    ->dateTime('d/m/Y H:i')
                    ->description(fn (JadwalTryout $record): string => $record->tgl_selesai ? $record->tgl_selesai->format('H:i') : ''),
                TextColumn::make('waktu_perpanjangan')
                    ->label('Waktu Perpanjangan Tes')
                    ->default('-'), // Placeholder
                TextColumn::make('nama_sesi')
                    ->label('Kelompok Tes (Sesi)'),
                TextColumn::make('is_token_active')
                    ->label('Aksi')
                    ->badge()
                    ->color(fn (JadwalTryout $record): string => $record->is_token_active ? 'danger' : 'info')
                    ->state(fn (JadwalTryout $record): string => $record->is_token_active ? 'Unset' : 'Set')
                    ->action(
                        \Filament\Tables\Actions\Action::make('set_active')
                            ->requiresConfirmation()
                            ->action(function (JadwalTryout $record) {
                                if ($record->is_token_active) {
                                    $record->update(['is_token_active' => false]);
                                    \Filament\Notifications\Notification::make()
                                        ->title('Rilis Token Dinonaktifkan')
                                        ->success()
                                        ->send();
                                } else {
                                    $newToken = strtoupper(\Illuminate\Support\Str::random(6));
                                    $record->update([
                                        'is_token_active' => true,
                                        'token' => $newToken,
                                        'token_released_at' => now(),
                                        'token_active_until' => now()->addMinutes(20),
                                    ]);

                                    \Filament\Notifications\Notification::make()
                                        ->title('Rilis Token Diaktifkan')
                                        ->body("Token baru : $newToken")
                                        ->success()
                                        ->send();
                                }
                            })
                    ),
                TextColumn::make('token')
                    ->label('Token')
                    ->badge()
                    ->color('warning')
                    ->state(function (JadwalTryout $record) {
                        return $record->is_token_active ? $record->token : '-';
                    }),
            ])
            ->actions([
                // Kosong, aksi dipindah ke kolom 'is_token_active'
            ]);
    }
}
