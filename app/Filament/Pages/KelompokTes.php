<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\PesertaJadwal;
use App\Models\JadwalTryout;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;

class KelompokTes extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Kelompok Tes';
    protected static ?string $title = 'Kelompok Tes';
    protected static ?string $navigationGroup = 'Monitoring Ujian (Baru)';
    protected static ?int $navigationSort = 6;

    protected static string $view = 'filament.pages.kelompok-tes';

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user->hasRole('super_admin') || $user->hasPermissionTo('manage_monitoring');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(PesertaJadwal::query()->with(['user', 'jadwalTryout']))
            ->columns([
                TextColumn::make('user.username')
                    ->label('Username')
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label('Nama Peserta')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'registered' => 'gray',
                        'active' => 'info',
                        'started', 'working' => 'warning',
                        'completed' => 'success',
                        default => 'danger',
                    }),
                TextColumn::make('jadwalTryout.nama_sesi')
                    ->label('Kode Tes'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'registered' => 'Non Active',
                        'active' => 'Active',
                        'working' => 'Working',
                        'completed' => 'Completed',
                    ]),
                SelectFilter::make('jadwal_tryout_id')
                    ->label('Kode Tes')
                    ->options(fn () => JadwalTryout::where('is_active', true)->pluck('nama_sesi', 'id')->toArray()),
            ])
            ->headerActions([
                Action::make('reset_all')
                    ->label('Reset All')
                    ->button()
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function () {
                        $activeJadwal = JadwalTryout::where('is_active', true)->first();
                        if ($activeJadwal) {
                            PesertaJadwal::where('jadwal_tryout_id', $activeJadwal->id)
                                ->update(['status' => 'registered']);
                            
                            Notification::make()
                                ->title('Semua Peserta Direset ke Non Active')
                                ->success()
                                ->send();
                        }
                    }),
            ])
            ->bulkActions([
                BulkAction::make('assign')
                    ->label('Assign')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Collection $records) {
                        $records->each(function ($record) {
                            if ($record->status === 'registered' || $record->status === 'disconnected') {
                                $record->update(['status' => 'active']);
                            }
                        });

                        Notification::make()
                            ->title('Peserta Berhasil Di-Assign')
                            ->success()
                            ->send();
                    }),
                BulkAction::make('unassign')
                    ->label('Unassign')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function (Collection $records) {
                        $records->each(function ($record) {
                            // Guard: only Allow unassigning Active but not started/working yet
                            if ($record->status === 'active') {
                                $record->update(['status' => 'registered']);
                            }
                        });

                        Notification::make()
                            ->title('Peserta Berhasil Di-Unassign')
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
