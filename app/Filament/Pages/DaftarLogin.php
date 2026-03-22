<?php

namespace App\Filament\Pages;

use App\Models\PesertaJadwal;
use Filament\Pages\Page;
use Filament\Tables\Table;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;

class DaftarLogin extends Page implements HasTable
{
    use InteractsWithTable, \App\Traits\HasProktorFilter;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-left-on-rectangle';
    protected static ?string $navigationLabel = 'Daftar Login';
    protected static ?string $title = 'Daftar Login';
    protected static ?string $navigationGroup = 'Monitoring Ujian';
    protected static ?int $navigationSort = 5;
    protected static string $view = 'filament.pages.daftar-login';

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user->hasRole('super_admin') || $user->hasPermissionTo('manage_monitoring');
    }

    public function table(Table $table): Table
    {
        $query = PesertaJadwal::query()
            ->whereIn('status', ['started', 'working'])
            ->whereIn('user_id', function ($query) {
                $query->select('user_id')
                    ->from('sessions')
                    ->whereNotNull('user_id');
            });

        $query = $this->applyProktorFilter($query);

        return $table
            ->query($query)
            ->poll('20s')
            ->columns([
                TextColumn::make('user.username')
                    ->label('Username/No Peserta')
                    ->searchable()
                    ->sortable()
                    ->fontFamily('mono'),
                TextColumn::make('user.name')
                    ->label('Nama Peserta')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.plain_password')
                    ->label('Password (Bantuan)')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('sisa_waktu')
                    ->label('Sisa Waktu')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? ceil($state / 60) . 'm' : '-')
                    ->color(fn ($record) => ($record->sisa_waktu ?? 0) < 300 ? 'danger' : 'success'),
            ])
            ->actions([
                Action::make('reset')
                    ->label('Reset Login')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        DB::table('sessions')->where('user_id', $record->user_id)->delete();
                        Notification::make()
                            ->title('Sesi login peserta berhasil direset.')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                BulkAction::make('reset_massal')
                    ->label('Reset Login Terpilih')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                        foreach ($records as $record) {
                            DB::table('sessions')->where('user_id', $record->user_id)->delete();
                        }
                        Notification::make()
                            ->title('Sesi login peserta terpilih berhasil direset.')
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
