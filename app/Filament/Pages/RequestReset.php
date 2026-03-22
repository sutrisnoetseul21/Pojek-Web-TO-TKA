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

class RequestReset extends Page implements HasTable
{
    use InteractsWithTable, \App\Traits\HasProktorFilter;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path-rounded-square';
    protected static ?string $navigationLabel = 'Request Reset Login';
    protected static ?string $title = 'Request Reset Login';
    protected static ?string $navigationGroup = 'Monitoring Ujian';
    protected static ?int $navigationSort = 4;
    protected static string $view = 'filament.pages.request-reset';

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user->hasRole('super_admin') || $user->hasPermissionTo('manage_monitoring');
    }

    public function table(Table $table): Table
    {
        $query = PesertaJadwal::query()
            ->whereNotNull('request_reset_at');

        $query = $this->applyProktorFilter($query);

        return $table
            ->query($query)
            ->poll('5s') // Auto refresh agar antrean terupdate otomatis
            ->columns([
                TextColumn::make('user.username')
                    ->label('Username')
                    ->searchable()
                    ->sortable()
                    ->fontFamily('mono'),
                TextColumn::make('user.name')
                    ->label('Nama Peserta')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('request_reset_at')
                    ->label('Waktu Request')
                    ->dateTime('H:i:s / d M Y')
                    ->badge()
                    ->color('warning')
                    ->sortable(),
            ])
            ->actions([
                Action::make('setujui')
                    ->label('Setujui Reset')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        // 1. Matikan klem session
                        DB::table('sessions')->where('user_id', $record->user_id)->delete();
                        // 2. Kosongkan antrean bantuan
                        $record->update(['request_reset_at' => null]);
                        
                        Notification::make()
                            ->title('Request reset disetujui. Sesi peserta telah dibuka.')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                BulkAction::make('setujui_massal')
                    ->label('Setujui Terpilih')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                        foreach ($records as $record) {
                            DB::table('sessions')->where('user_id', $record->user_id)->delete();
                            $record->update(['request_reset_at' => null]);
                        }
                        Notification::make()
                            ->title('Request reset massal berhasil disetujui.')
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
