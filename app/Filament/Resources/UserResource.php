<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationLabel = 'User Peserta';
    protected static ?string $modelLabel = 'User';
    protected static ?string $pluralModelLabel = 'Users';
    protected static ?string $navigationGroup = 'Manajemen User';
    protected static ?int $navigationSort = 1;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Akun')
                    ->description('Informasi login user')
                    ->schema([
                        Forms\Components\TextInput::make('username')
                            ->label('Username')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->disabled(fn($record) => $record !== null),
                        Forms\Components\TextInput::make('plain_password')
                            ->label('Password (Plain)')
                            ->disabled()
                            ->helperText('Password tidak bisa dirubah untuk keamanan'),
                        Forms\Components\Select::make('role')
                            ->options([
                                'admin' => 'Admin',
                                'peserta' => 'Peserta',
                            ])
                            ->required()
                            ->default('peserta'),
                    ])->columns(3),

                Forms\Components\Section::make('Biodata')
                    ->description('Biodata peserta (diisi saat pertama login)')
                    ->schema([
                        Forms\Components\TextInput::make('nama_lengkap')
                            ->label('Nama Lengkap'),
                        Forms\Components\TextInput::make('sekolah')
                            ->label('Sekolah'),
                        Forms\Components\TextInput::make('tempat_lahir')
                            ->label('Tempat Lahir'),
                        Forms\Components\DatePicker::make('tanggal_lahir')
                            ->label('Tanggal Lahir'),
                        Forms\Components\Select::make('jenis_kelamin')
                            ->label('Jenis Kelamin')
                            ->options([
                                'L' => 'Laki-laki',
                                'P' => 'Perempuan',
                            ]),
                        Forms\Components\Toggle::make('is_biodata_complete')
                            ->label('Biodata Lengkap')
                            ->disabled(),
                    ])->columns(3),
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
                Tables\Columns\TextColumn::make('plain_password')
                    ->label('Password')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),
                Tables\Columns\BadgeColumn::make('role')
                    ->colors([
                        'danger' => 'admin',
                        'info' => 'peserta',
                    ]),
                Tables\Columns\TextColumn::make('nama_lengkap')
                    ->label('Nama')
                    ->searchable()
                    ->placeholder('Belum diisi'),
                Tables\Columns\TextColumn::make('sekolah')
                    ->label('Sekolah')
                    ->searchable()
                    ->placeholder('Belum diisi')
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_biodata_complete')
                    ->label('Biodata')
                    ->boolean(),
                Tables\Columns\TextColumn::make('jadwalTryouts_count')
                    ->label('Tryout')
                    ->counts('jadwalTryouts')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('username', 'asc')
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->options([
                        'admin' => 'Admin',
                        'peserta' => 'Peserta',
                    ]),
                Tables\Filters\TernaryFilter::make('is_biodata_complete')
                    ->label('Biodata Lengkap'),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
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
     * Get next available username number
     */
    public static function getNextUsernameNumber(int $year): int
    {
        $prefix = 'P' . $year;
        $lastUser = User::where('username', 'like', $prefix . '%')
            ->orderBy('username', 'desc')
            ->first();

        if (!$lastUser) {
            return 1;
        }

        $lastNumber = (int) substr($lastUser->username, strlen($prefix));
        return $lastNumber + 1;
    }
}

