<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserAdminResource\Pages;
use App\Filament\Resources\UserAdminResource\RelationManagers;
use App\Models\User;
use App\Enums\Jenjang;
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
    protected static ?int $navigationSort = 1;

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

                Forms\Components\Section::make('Hak Akses')
                    ->description('Tentukan permission yang dimiliki admin ini')
                    ->schema([
                        Forms\Components\CheckboxList::make('permissions')
                            ->label('Hak Akses Admin')
                            ->relationship('permissions', 'name')
                            ->getOptionLabelFromRecordUsing(fn ($record) => match ($record->name) {
                                'manage_peserta'    => '👥 Kelola Kelas dan Peserta',
                                'manage_soal'       => '📝 Kelola Bank Soal (Mapel, Paket, Soal, Stimulus)',
                                'manage_ujian'      => '🏆 Kelola TRYOUT (Paket Tryout, Jadwal)',
                                'manage_monitoring' => '🔍 Kelola Monitoring Ujian',
                                'manage_laporan'    => '📊 Kelola Laporan & Hasil',
                                default             => $record->name,
                            })
                            ->required()
                            ->columns(1)
                            ->helperText('Centang satu atau lebih hak akses untuk admin ini'),
                    ]),

                Forms\Components\Section::make('Otoritas Wilayah')
                    ->description('Tentukan jenjang dan sekolah yang dikelola')
                    ->schema([
                        Forms\Components\Select::make('jenjang')
                            ->options(
                                collect(Jenjang::cases())->mapWithKeys(fn($j) => [$j->value => $j->label()])
                            )
                            ->required(),
                        Forms\Components\Select::make('sekolah_id')
                            ->label('Sekolah')
                            ->relationship('sekolahRelation', 'nama_sekolah')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('nama_sekolah')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('npsn')
                                    ->required()
                                    ->unique('sekolah', 'npsn')
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('alamat')
                                    ->maxLength(255),
                            ])
                            ->helperText('Pilih sekolah atau buat baru jika belum ada'),
                    ])->columns(2),

                Forms\Components\Section::make('Penugasan Kelas')
                    ->description('Tentukan bagaimana admin ini mengelola kelas')
                    ->schema([
                        Forms\Components\ToggleButtons::make('manage_all_kelas')
                            ->label('Mode Penugasan Kelas')
                            ->options([
                                true => 'Ikuti semua kelas di sekolah',
                                false => 'Pilih kelas tertentu',
                            ])
                            ->colors([
                                true => 'success',
                                false => 'primary',
                            ])
                            ->icons([
                                true => 'heroicon-o-check-circle',
                                false => 'heroicon-o-list-bullet',
                            ])
                            ->default(false)
                            ->live()
                            ->required(),
                        Forms\Components\CheckboxList::make('kelases')
                            ->label('Kelas yang Dikelola')
                            ->relationship('kelases', 'nama_kelas', modifyQueryUsing: function (Builder $query, Forms\Get $get) {
                                if ($sekolahId = $get('sekolah_id')) {
                                    return $query->where('sekolah_id', $sekolahId);
                                }
                                return $query;
                            })
                            ->visible(fn(Forms\Get $get) => $get('manage_all_kelas') == false)
                            ->required(fn(Forms\Get $get) => $get('manage_all_kelas') == false)
                            ->searchable()
                            ->columns(3)
                            ->helperText('Hanya menampilkan kelas dari sekolah yang dipilih di atas'),
                    ]),
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
                    ->searchable()
                    ->toggleable(),
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
                    ->sortable(),
                Tables\Columns\TextColumn::make('sekolahRelation.npsn')
                    ->label('NPSN')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('permissions.name')
                    ->label('Hak Akses')
                    ->badge()
                    ->separator(','),
                Tables\Columns\TextColumn::make('kelases_count')
                    ->label('Jml Kelas')
                    ->state(function (User $record) {
                        return $record->manage_all_kelas ? 'Semua' : $record->kelases()->count();
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('jenjang')
                    ->options(
                        collect(Jenjang::cases())->mapWithKeys(fn($j) => [$j->value => $j->label()])
                    ),
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
