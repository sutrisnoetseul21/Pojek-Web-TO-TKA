<?php

namespace App\Filament\Resources\BankStimulusResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class SoalRelationManager extends RelationManager
{
    protected static string $relationship = 'soal';

    protected static ?string $title = 'Soal Terkait';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('pertanyaan')
            ->columns([
                Tables\Columns\TextColumn::make('pertanyaan')
                    ->label('Soal')
                    ->html()
                    ->limit(80)
                    ->tooltip(fn($record) => strip_tags($record->pertanyaan))
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('tipe_soal')
                    ->label('Tipe')
                    ->colors([
                        'primary' => 'PG_TUNGGAL',
                        'success' => 'PG_KOMPLEKS',
                        'warning' => 'BENAR_SALAH',
                        'info' => 'MENJODOHKAN',
                        'gray' => 'ISIAN',
                    ]),
                Tables\Columns\TextColumn::make('bobot')
                    ->label('Bobot')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn($record) => route('filament.admin.resources.bank-soals.edit', $record)),
            ])
            ->bulkActions([
                //
            ]);
    }
}
