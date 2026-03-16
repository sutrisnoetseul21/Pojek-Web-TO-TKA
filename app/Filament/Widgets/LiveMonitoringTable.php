<?php

namespace App\Filament\Widgets;

use App\Models\PesertaJadwal;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class LiveMonitoringTable extends BaseWidget
{
    /**
     * Urutan Widget (Ke-2, di tengah)
     */
    protected static ?int $sort = 2;

    /**
     * Tampilan lebar penuh untuk tabel
     */
    protected int | string | array $columnSpan = 'full';

    /**
     * Waktu polling auto-refresh (5 detik)
     */
    protected static ?string $pollingInterval = '5s';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                // Eager Load 'user.kelas' dan 'jadwalTryout' untuk mencegah masalah N+1 Query
                PesertaJadwal::where('status', 'started')->with(['user.kelas', 'jadwalTryout'])
            )
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nama Peserta')
                    ->searchable(false) // Nonaktifkan search agar query sangat ringan
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.kelas.nama_kelas')
                    ->label('Kelas')
                    ->default('-')
                    ->searchable(false)
                    ->sortable(),

                Tables\Columns\TextColumn::make('jadwalTryout.nama_sesi')
                    ->label('Sesi Tryout')
                    ->searchable(false)
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state): string => match ($state) {
                        'started' => 'success',
                        'completed' => 'gray',
                        default => 'primary',
                    })
                    ->formatStateUsing(fn ($state): string => match ($state) {
                        'started' => 'Sedang Ujian',
                        'completed' => 'Selesai',
                        default => 'Terdaftar',
                    }),
            ])
            ->defaultPaginationPageOption(5)
            ->paginationPageOptions([5])
            ->defaultSort('waktu_mulai', 'desc')
            ->heading('Live Monitoring Peserta');
    }
}
