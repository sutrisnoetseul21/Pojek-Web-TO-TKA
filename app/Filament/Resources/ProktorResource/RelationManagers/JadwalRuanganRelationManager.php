<?php

namespace App\Filament\Resources\ProktorResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class JadwalRuanganRelationManager extends RelationManager
{
    protected static string $relationship = 'penugasanRuangan';

    protected static ?string $title = 'Penugasan Jadwal & Ruangan';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('jadwal_tryout_id')
                    ->label('Jadwal Ujian')
                    ->options(fn (RelationManager $livewire) => 
                        $livewire->ownerRecord->sekolah_id 
                        ? \App\Models\JadwalTryout::with('paketTryout')
                            ->where('sekolah_id', $livewire->ownerRecord->sekolah_id)
                            ->get()
                            ->mapWithKeys(fn ($j) => [$j->id => ($j->paketTryout->nama_paket ?? 'Tanpa Paket') . ' (' . ($j->nama_sesi ?? 'Sesi') . ')'])
                            ->toArray()
                        : \App\Models\JadwalTryout::with('paketTryout')
                            ->get()
                            ->mapWithKeys(fn ($j) => [$j->id => ($j->paketTryout->nama_paket ?? 'Tanpa Paket') . ' (' . ($j->nama_sesi ?? 'Sesi') . ')'])
                            ->toArray()
                    )
                    ->required()
                    ->native(false)
                    ->searchable(),
                    
                Forms\Components\Select::make('ruangan_id')
                    ->label('Ruangan')
                    ->options(fn (RelationManager $livewire) => 
                         $livewire->ownerRecord->sekolah_id 
                         ? \App\Models\Ruangan::where('sekolah_id', $livewire->ownerRecord->sekolah_id)
                            ->pluck('nama_ruangan', 'id')
                            ->toArray()
                         : \App\Models\Ruangan::pluck('nama_ruangan', 'id')->toArray()
                    )
                    ->required()
                    ->native(false)
                    ->searchable(),

                Forms\Components\Select::make('status')
                    ->options([
                        'active' => 'Aktif',
                        'inactive' => 'Tidak Aktif',
                    ])
                    ->required()
                    ->default('active')
                    ->native(false),

                Forms\Components\Textarea::make('catatan')
                    ->label('Catatan')
                    ->nullable()
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('jadwalTryout.paketTryout.nama_paket')
                    ->label('Paket Ujian')
                    ->description(fn ($record) => $record->jadwalTryout ? $record->jadwalTryout->nama_sesi : '-')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ruangan.nama_ruangan')
                    ->label('Ruangan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Aktif',
                        'inactive' => 'Tidak Aktif',
                    }),
                Tables\Columns\TextColumn::make('catatan')
                    ->label('Catatan')
                    ->limit(20)
                    ->default('-'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
