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
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationLabel = 'User Peserta';
    protected static ?string $modelLabel = 'User';
    protected static ?string $pluralModelLabel = 'Users';
    protected static ?string $navigationGroup = 'Manajemen Peserta';
    protected static ?int $navigationSort = 3;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        return $user->hasRole('super_admin') || $user->hasPermissionTo('manage_peserta');
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class])
            ->where('role', '=', 'peserta');

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
                Forms\Components\Section::make('Akun')
                    ->description('Informasi login user')
                    ->schema([
                        Forms\Components\TextInput::make('username')
                            ->label('Username')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->disabled(fn($record) => $record !== null),
                        Forms\Components\TextInput::make('plain_password')
                            ->label('Password')
                            ->password()
                            ->revealable()
                            ->placeholder('Kosongkan untuk generate otomatis')
                            ->helperText('Jika dikosongkan saat tambah user, password akan digenerate otomatis'),
                        Forms\Components\Hidden::make('role')
                            ->default('peserta'),
                    ])->columns(['default' => 3]),

                Forms\Components\Section::make('Biodata & Penempatan')
                    ->description('Biodata peserta dan penempatan kelas')
                    ->schema([
                        Forms\Components\TextInput::make('nama_lengkap')
                            ->label('Nama Lengkap')
                            ->required(),
                        Forms\Components\Select::make('jenjang')
                            ->options([
                                'SD' => 'SD',
                                'SMP' => 'SMP',
                                'SMA' => 'SMA',
                                'SMK' => 'SMK',
                                'UMUM' => 'Umum',
                            ])
                            ->required()
                            ->default(fn () => auth()->user()->jenjang)
                            ->disabled(fn () => auth()->user()->hasRole('admin') && auth()->user()->jenjang !== null)
                            ->dehydrated(),
                        Forms\Components\Select::make('sekolah_id')
                            ->label('Sekolah')
                            ->relationship('sekolahRelation', 'nama_sekolah')
                            ->required()
                            ->default(fn () => auth()->user()->sekolah_id)
                            ->disabled(fn () => auth()->user()->hasRole('admin'))
                            ->dehydrated()
                            ->live(),
                        Forms\Components\Select::make('kelas_id')
                            ->label('Kelas')
                            ->options(function (Forms\Get $get) {
                                $sekolahId = $get('sekolah_id');
                                if (!$sekolahId) return [];
                                return \App\Models\Kelas::where('sekolah_id', $sekolahId)
                                    ->pluck('nama_kelas', 'id');
                            })
                            ->required()
                            ->searchable()
                            ->preload()
                            ->helperText('Hanya menampilkan kelas dari sekolah terpilih'),
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
                    ])->columns(['default' => 3]),
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
                Tables\Columns\BadgeColumn::make('jenjang')
                    ->colors([
                        'primary' => 'SD',
                        'success' => 'SMP',
                        'warning' => 'SMA',
                        'danger' => 'SMK',
                        'info' => 'UMUM',
                    ]),
                Tables\Columns\TextColumn::make('sekolahRelation.nama_sekolah')
                    ->label('Sekolah')
                    ->searchable()
                    ->placeholder('Belum diisi')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('kelas.nama_kelas')
                    ->label('Kelas')
                    ->searchable()
                    ->placeholder('Belum diisi')
                    ->sortable(),
                Tables\Columns\TextColumn::make('sekolahRelation.npsn')
                    ->label('NPSN')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_biodata_complete')
                    ->label('Biodata')
                    ->boolean(),
                Tables\Columns\TextColumn::make('jadwalTryouts_count')
                    ->label('Tryout')
                    ->counts('jadwalTryouts'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('username', 'asc')
            ->filters([
                Tables\Filters\SelectFilter::make('sekolah_id')
                    ->label('Sekolah')
                    ->relationship('sekolahRelation', 'nama_sekolah')
                    ->searchable()
                    ->preload()
                    ->placeholder('Semua Sekolah'),
                Tables\Filters\SelectFilter::make('kelas_id')
                    ->label('Kelas')
                    ->relationship('kelas', 'nama_kelas')
                    ->searchable()
                    ->preload(),
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
                    BulkAction::make('cetak_kartu')
                        ->label('Cetak Kartu')
                        ->icon('heroicon-o-printer')
                        ->color('info')
                        ->action(function (Collection $records) {
                            $ids = $records->pluck('id')->implode(',');
                            $url = route('print.kartu-peserta', ['ids' => $ids]);
                            
                            // Redirect ke halaman print dalam tab baru
                            return redirect()->away($url);
                        })
                        ->requiresConfirmation(false),
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

