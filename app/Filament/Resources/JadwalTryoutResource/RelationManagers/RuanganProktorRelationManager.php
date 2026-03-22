<?php

namespace App\Filament\Resources\JadwalTryoutResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RuanganProktorRelationManager extends RelationManager
{
    protected static string $relationship = 'ruanganProktors';

    protected static ?string $title = 'Alokasi Ruangan & Proktor';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('ruangan_id')
                    ->label('Ruangan')
                    ->options(fn (RelationManager $livewire) => 
                        \App\Models\Ruangan::where('sekolah_id', $livewire->ownerRecord->sekolah_id)
                            ->pluck('nama_ruangan', 'id')
                            ->toArray()
                    )
                    ->required()
                    ->native(false)
                    ->searchable(),
                Forms\Components\Select::make('proktor_id')
                    ->label('Proktor')
                    ->options(fn (RelationManager $livewire) => 
                        \App\Models\User::where('sekolah_id', $livewire->ownerRecord->sekolah_id)
                            ->where('role', 'proktor')
                            ->pluck('nama_lengkap', 'id')
                            ->toArray()
                    )
                    ->nullable()
                    ->native(false)
                    ->searchable(),
                Forms\Components\Select::make('kelas_id')
                    ->label('Kelas/Rombel')
                    ->options(fn (RelationManager $livewire) => 
                        $livewire->ownerRecord->kelases()->count() > 0
                            ? $livewire->ownerRecord->kelases()->pluck('nama_kelas', 'kelas.id')->toArray()
                            : \App\Models\Kelas::where('sekolah_id', $livewire->ownerRecord->sekolah_id)
                                ->when($livewire->ownerRecord->paketTryout?->tingkat, fn ($q, $t) => $q->where('tingkat', $t))
                                ->pluck('nama_kelas', 'id')
                                ->toArray()
                    )
                    ->nullable()
                    ->placeholder('Semua Kelas (Default)')
                    ->native(false)
                    ->searchable()
                    ->unique(ignoreRecord: true, modifyRuleUsing: function (\Illuminate\Validation\Rules\Unique $rule, \Filament\Forms\Get $get, RelationManager $livewire) {
                        return $rule->where('jadwal_tryout_id', $livewire->ownerRecord->id)
                                    ->where('ruangan_id', $get('ruangan_id'))
                                    ->where('proktor_id', $get('proktor_id'));
                    }),
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
            ->recordTitleAttribute('ruangan.nama_ruangan')
            ->columns([
                Tables\Columns\TextColumn::make('ruangan.nama_ruangan')
                    ->label('Ruangan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('proktor.nama_lengkap')
                    ->label('Proktor')
                    ->default('-')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('kelas.nama_kelas')
                    ->label('Kelas/Rombel')
                    ->placeholder('Semua Kelas')
                    ->badge()
                    ->color('info'),
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
