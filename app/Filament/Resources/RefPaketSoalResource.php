<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RefPaketSoalResource\Pages;
use App\Filament\Resources\RefPaketSoalResource\RelationManagers;
use App\Models\RefPaketSoal;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RefPaketSoalResource extends Resource
{
    protected static ?string $model = RefPaketSoal::class;

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        return $user->hasRole('super_admin') || $user->hasPermissionTo('manage_soal');
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if ($user->isAdmin() && $user->jenjang) {
            $query->whereHas('mapel', function ($q) use ($user) {
                $q->where('jenjang', '=', $user->jenjang);
            });
        }

        return $query;
    }

    protected static ?string $navigationLabel = 'Kategori Soal';
    protected static ?string $modelLabel = 'Kategori Soal';
    protected static ?string $pluralModelLabel = 'Kategori Soal';
    protected static ?string $navigationGroup = 'Bank Soal';

    protected static ?string $navigationIcon = 'heroicon-o-folder';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('mapel_id')
                    ->options(\App\Models\RefMapel::all()->mapWithKeys(fn($m) => [$m->id => "{$m->nama_mapel} - {$m->jenjang}"]))
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('Mata Pelajaran')
                    ->reactive()
                    ->afterStateUpdated(function (callable $set, $state) {
                        if ($state) {
                            $mapel = \App\Models\RefMapel::find($state);
                            if ($mapel) {
                                $set('jenjang', $mapel->jenjang);
                            }
                        }
                    }),
                Forms\Components\TextInput::make('nama_paket')
                    ->label('Nama Kategori / Folder')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('jenjang')
                    ->label('Jenjang')
                    ->disabled()
                    ->dehydrated()
                    ->helperText('Otomatis dari Mata Pelajaran'),
                Forms\Components\Textarea::make('keterangan')
                    ->label('Keterangan')
                    ->placeholder('Catatan atau deskripsi untuk kategori ini...')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_paket')
                    ->label('Nama Kategori')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('mapel.nama_mapel')
                    ->label('Mata Pelajaran')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('jenjang')
                    ->colors([
                        'info' => 'SD',
                        'success' => 'SMP',
                        'warning' => 'SMA',
                        'primary' => 'UMUM',
                    ]),
                Tables\Columns\TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->limit(30)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('soal_count')
                    ->label('Jumlah Soal')
                    ->counts('soal')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('jenjang')
                    ->options([
                        'SD' => 'SD',
                        'SMP' => 'SMP',
                        'SMA' => 'SMA',
                        'UMUM' => 'UMUM',
                    ]),
                Tables\Filters\SelectFilter::make('mapel_id')
                    ->relationship('mapel', 'nama_mapel')
                    ->label('Mata Pelajaran')
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRefPaketSoals::route('/'),
            'create' => Pages\CreateRefPaketSoal::route('/create'),
            'edit' => Pages\EditRefPaketSoal::route('/{record}/edit'),
        ];
    }
}
