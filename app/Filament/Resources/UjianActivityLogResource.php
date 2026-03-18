<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UjianActivityLogResource\Pages;
use App\Filament\Resources\UjianActivityLogResource\RelationManagers;
use App\Models\UjianActivityLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UjianActivityLogResource extends Resource
{
    protected static ?string $model = UjianActivityLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-magnifying-glass';
    protected static ?string $navigationLabel = 'Riwayat Aktivitas Peserta';
    protected static ?string $title = 'Riwayat Aktivitas';
    protected static ?string $navigationGroup = 'LOG';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([/* Read Only */]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('index')
                    ->label('No')
                    ->rowIndex(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.username')
                    ->label('Username')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.nama_lengkap')
                    ->label('Nama Peserta')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('jadwalTryout.nama_ujian')
                    ->label('Sesi Ujian')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('aktivitas')
                    ->label('Aktivitas')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Login' => 'warning',
                        'Selesai' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->limit(40)
                    ->sortable(),
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP Address'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('aktivitas')
                    ->options([
                        'Login' => 'Login',
                        'Start Subtes' => 'Start Subtes',
                        'Selesai' => 'Selesai',
                    ]),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageUjianActivityLogs::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['user', 'jadwalTryout']);
    }
}
