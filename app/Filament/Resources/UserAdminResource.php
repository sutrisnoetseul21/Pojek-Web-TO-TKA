<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserAdminResource\Pages;
use App\Filament\Resources\UserAdminResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;

class UserAdminResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationLabel = 'User Admin';
    protected static ?string $modelLabel = 'Admin';
    protected static ?string $pluralModelLabel = 'Admins';
    protected static ?string $navigationGroup = 'Manajemen User';
    protected static ?int $navigationSort = 2;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where(fn (Builder $query) => $query->where('role', '=', 'admin'));
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->isSuperAdmin();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Akun Admin')
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
                            ->default('admin'),
                    ])->columns(2),
                
                Forms\Components\Section::make('Otoritas')
                    ->description('Tentukan jenjang dan sekolah yang dikelola oleh admin ini')
                    ->schema([
                        Forms\Components\Select::make('jenjang')
                            ->options([
                                'SD' => 'SD',
                                'SMP' => 'SMP',
                                'SMA' => 'SMA',
                                'SMK' => 'SMK',
                                'UMUM' => 'Umum',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('sekolah')
                            ->label('Nama Sekolah')
                            ->placeholder('Contoh: SDN 1 Gandaria')
                            ->required(),
                        Forms\Components\TextInput::make('npsn')
                            ->label('NPSN')
                            ->placeholder('Contoh: 12345678')
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('username')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('jenjang')
                    ->colors([
                        'primary' => 'SD',
                        'success' => 'SMP',
                        'warning' => 'SMA',
                        'danger' => 'SMK',
                        'info' => 'UMUM',
                    ]),
                Tables\Columns\TextColumn::make('sekolah')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('npsn')
                    ->label('NPSN')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('jenjang')
                    ->options([
                        'SD' => 'SD',
                        'SMP' => 'SMP',
                        'SMA' => 'SMA',
                        'SMK' => 'SMK',
                        'UMUM' => 'Umum',
                    ]),
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
            'index' => Pages\ListUserAdmins::route('/'),
            'create' => Pages\CreateUserAdmin::route('/create'),
            'edit' => Pages\EditUserAdmin::route('/{record}/edit'),
        ];
    }
}
