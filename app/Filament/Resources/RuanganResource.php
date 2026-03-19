<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RuanganResource\Pages;
use App\Filament\Resources\RuanganResource\RelationManagers;
use App\Models\Ruangan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RuanganResource extends Resource
{
    protected static ?string $model = Ruangan::class;

    protected static ?string $navigationLabel = 'Ruang';
    protected static ?string $modelLabel = 'Ruang';
    protected static ?string $pluralModelLabel = 'Ruang';
    protected static ?string $navigationGroup = 'Manajemen Peserta dan Ruangan';
    protected static ?int $navigationSort = 15; // Set sort sort
    protected static ?string $navigationIcon = 'heroicon-o-home-modern';

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        return $user->hasRole('super_admin') || $user->hasRole('admin');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Data Ruangan')
                    ->description('Informasi ruangan tempat ujian')
                    ->schema([
                        Forms\Components\Select::make('sekolah_id')
                            ->label('Sekolah')
                            ->relationship('sekolah', 'nama_sekolah')
                            ->required()
                            ->default(fn () => auth()->user()->sekolah_id)
                            ->disabled(fn () => auth()->user()->hasRole('admin'))
                            ->dehydrated(),
                        Forms\Components\TextInput::make('nama_ruangan')
                            ->label('Nama Ruangan')
                            ->required()
                            ->placeholder('Contoh: Lab Komputer A'),
                        Forms\Components\TextInput::make('kode_ruangan')
                            ->label('Kode Ruangan')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->placeholder('Contoh: LAB-A'),
                        Forms\Components\TextInput::make('kapasitas')
                            ->label('Kapasitas')
                            ->numeric()
                            ->required()
                            ->default(0),
                        Forms\Components\Textarea::make('keterangan')
                            ->label('Keterangan')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sekolah.nama_sekolah')
                    ->label('Sekolah')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nama_ruangan')
                    ->label('Nama Ruangan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('kode_ruangan')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('kapasitas')
                    ->label('Kapasitas')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('sekolah_id')
                    ->relationship('sekolah', 'nama_sekolah')
                    ->label('Sekolah')
                    ->searchable()
                    ->preload(),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
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
            'index' => Pages\ListRuangans::route('/'),
            'create' => Pages\CreateRuangan::route('/create'),
            'edit' => Pages\EditRuangan::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);

        $user = auth()->user();

        if ($user->hasRole('admin') && $user->sekolah_id) {
            $query->where('sekolah_id', $user->sekolah_id);
        }

        return $query;
    }
}
