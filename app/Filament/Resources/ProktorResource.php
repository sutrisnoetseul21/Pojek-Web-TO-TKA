<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProktorResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProktorResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationLabel = 'Akun Proktor';
    protected static ?string $modelLabel = 'Proktor';
    protected static ?string $pluralModelLabel = 'Proktor';
    protected static ?string $navigationGroup = 'Manajemen Proktor';
    protected static ?int $navigationSort = 1;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        return $user->hasRole('super_admin') || $user->hasRole('admin');
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class])
            ->where('role', '=', 'proktor');

        $user = auth()->user();

        if ($user->hasRole('admin') && $user->sekolah_id) {
            $query->where('sekolah_id', $user->sekolah_id);
        }

        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Akun Proktor')
                    ->description('Informasi login untuk Pengawas Ruangan')
                    ->schema([
                        Forms\Components\TextInput::make('username')
                            ->label('Username')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->disabled(fn($record) => $record !== null),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('nama_lengkap')
                            ->label('Nama Lengkap (Pengawas)')
                            ->required(),
                        Forms\Components\TextInput::make('plain_password')
                            ->label('Password')
                            ->password()
                            ->revealable()
                            ->placeholder('Kosongkan untuk generate otomatis')
                            ->helperText('Jika dikosongkan saat tambah proktor, password akan digenerate otomatis'),
                        Forms\Components\Hidden::make('role')
                            ->default('proktor'),
                    ])->columns(['default' => 2]),

                Forms\Components\Section::make('Otoritas Wilayah')
                    ->description('Tentukan sekolah tempat tugas Proktor')
                    ->schema([
                        Forms\Components\Select::make('sekolah_id')
                            ->label('Sekolah')
                            ->relationship('sekolahRelation', 'nama_sekolah')
                            ->visible(fn () => auth()->user()->hasRole('super_admin'))
                            ->required(fn () => auth()->user()->hasRole('super_admin'))
                            ->dehydrated()
                            ->live(),
                        Forms\Components\Hidden::make('sekolah_id')
                            ->default(fn () => auth()->user()->sekolah_id)
                            ->visible(fn () => auth()->user()->hasRole('admin')),
                    ])->columns(['default' => 1]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('username')
                    ->label('Username')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('plain_password')
                    ->label('Password')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('nama_lengkap')
                    ->label('Nama Pengawas')
                    ->searchable()
                    ->placeholder('Belum diisi'),
                Tables\Columns\TextColumn::make('sekolahRelation.nama_sekolah')
                    ->label('Sekolah')
                    ->searchable()
                    ->placeholder('Belum diisi'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->defaultSort('username', 'asc')
            ->filters([
                Tables\Filters\SelectFilter::make('sekolah_id')
                    ->label('Sekolah')
                    ->relationship('sekolahRelation', 'nama_sekolah')
                    ->searchable()
                    ->preload()
                    ->placeholder('Semua Sekolah')
                    ->visible(fn () => auth()->user()->hasRole('super_admin')),
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
            'index' => Pages\ListProktors::route('/'),
            'create' => Pages\CreateProktor::route('/create'),
            'edit' => Pages\EditProktor::route('/{record}/edit'),
        ];
    }

    /**
     * Generate random password: 5 uppercase letters + asterisk
     */
    public static function generatePassword(): string
    {
        $letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $password = '';
        for ($i = 0; $i < 5; $i++) {
            $password .= $letters[random_int(0, strlen($letters) - 1)];
        }
        return $password . '*';
    }

    /**
     * Get next available username number based on a custom prefix
     */
    public static function getNextUsernameNumberFromPrefix(string $prefix): int
    {
        $lastUser = User::where('username', 'like', $prefix . '%')
            ->orderByRaw('CAST(SUBSTRING(username, ' . (strlen($prefix) + 1) . ') AS UNSIGNED) DESC')
            ->first();

        if (!$lastUser) {
            return 1;
        }

        $lastNumber = (int) substr($lastUser->username, strlen($prefix));
        return $lastNumber + 1;
    }
}

