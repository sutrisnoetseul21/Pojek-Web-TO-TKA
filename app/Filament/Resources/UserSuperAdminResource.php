<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserSuperAdminResource\Pages;
use App\Filament\Resources\UserSuperAdminResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;

class UserSuperAdminResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationLabel = 'User Super Admin';
    protected static ?string $modelLabel = 'Super Admin';
    protected static ?string $pluralModelLabel = 'Super Admins';
    protected static ?string $navigationGroup = 'Manajemen User';
    protected static ?int $navigationSort = 2;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where(fn (Builder $query) => $query->where('role', '=', 'super_admin'));
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->isSuperAdmin();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Akun Super Admin')
                    ->schema([
                        Forms\Components\TextInput::make('username')
                            ->required()
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create'),
                        Forms\Components\Hidden::make('role')
                            ->default('super_admin'),
                    ])->columns(['default' => 2]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('username')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUserSuperAdmins::route('/'),
            'create' => Pages\CreateUserSuperAdmin::route('/create'),
            'edit' => Pages\EditUserSuperAdmin::route('/{record}/edit'),
        ];
    }
}
