<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RefMapelResource\Pages;
use App\Filament\Resources\RefMapelResource\RelationManagers;
use App\Models\RefMapel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RefMapelResource extends Resource
{
    protected static ?string $model = RefMapel::class;

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if ($user->isAdmin() && $user->jenjang) {
            $query->where(fn (Builder $q) => $q->where('jenjang', '=', $user->jenjang));
        }

        return $query;
    }

    protected static ?string $navigationLabel = 'Mata Pelajaran';
    protected static ?string $modelLabel = 'Mata Pelajaran';
    protected static ?string $pluralModelLabel = 'Mata Pelajaran';
    protected static ?string $navigationGroup = 'Data Master';

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama_mapel')
                    ->label('Nama Mata Pelajaran')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('kode_mapel')
                    ->label('Kode Mapel')
                    ->required()
                    ->unique(ignoreRecord: true, modifyRuleUsing: function (\Illuminate\Validation\Rules\Unique $rule, callable $get) {
                        return $rule->where('jenjang', $get('jenjang'));
                    })
                    ->maxLength(255),
                Forms\Components\Select::make('jenjang')
                    ->options([
                        'SD' => 'SD',
                        'SMP' => 'SMP',
                        'SMA' => 'SMA',
                        'SMK' => 'SMK',
                        'UMUM' => 'UMUM',
                    ])
                    ->required()
                    ->default(fn () => auth()->user()->jenjang ?? 'UMUM')
                    ->disabled(fn () => auth()->user()->isAdmin() && auth()->user()->jenjang !== null)
                    ->dehydrated(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_mapel')
                    ->searchable(),
                Tables\Columns\TextColumn::make('kode_mapel')
                    ->searchable(),
                Tables\Columns\TextColumn::make('jenjang')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListRefMapels::route('/'),
            'create' => Pages\CreateRefMapel::route('/create'),
            'edit' => Pages\EditRefMapel::route('/{record}/edit'),
        ];
    }
}
