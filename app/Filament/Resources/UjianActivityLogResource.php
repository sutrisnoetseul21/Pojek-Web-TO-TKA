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

    protected static ?string $navigationLabel = 'Log Aktivitas';
    protected static ?string $modelLabel = 'Log Aktivitas';
    protected static ?string $pluralModelLabel = 'Log Aktivitas';
    protected static ?string $navigationGroup = 'Monitoring Ujian';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationIcon = 'heroicon-o-pencil-square';

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        return $user->hasRole('super_admin') || $user->hasPermissionTo('manage_monitoring');
    }

    public static function canCreate(): bool { return false; }
    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool { return false; }
    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool { return false; }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if ($user->isAdmin() && $user->sekolah_id) {
            $query->whereHas('user', function ($q) use ($user) {
                $q->where('sekolah_id', $user->sekolah_id);
            });
        }

        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Aktivitas')
                    ->schema([
                        Forms\Components\TextInput::make('created_at')
                            ->label('Waktu')
                            ->content(fn ($record) => $record?->created_at->format('d M Y, H:i:s')),
                        Forms\Components\TextInput::make('user.nama_lengkap')
                            ->label('Peserta'),
                        Forms\Components\TextInput::make('aktivitas')
                            ->label('Jenis Aktivitas'),
                        Forms\Components\Textarea::make('keterangan')
                            ->label('Keterangan')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('ip_address')
                            ->label('IP Address'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y, H:i:s')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.nama_lengkap')
                    ->label('Peserta')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.kelas.nama_kelas')
                    ->label('Kelas')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.sekolahRelation.nama_sekolah')
                    ->label('Sekolah')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\BadgeColumn::make('aktivitas')
                    ->label('Aktivitas')
                    ->colors([
                        'primary' => 'login',
                        'success' => 'submit',
                        'warning' => 'timeout',
                        'danger' => 'disconnect',
                        'info' => ['mulai_ujian', 'simpan_jawaban', 'logout', 'login_ulang', 'force_submit'],
                    ])
                    ->formatStateUsing(fn (string $state): string => str_replace('_', ' ', ucfirst($state))),
                Tables\Columns\TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->keterangan),
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('sekolah')
                    ->label('Sekolah')
                    ->relationship('user.sekolahRelation', 'nama_sekolah')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('jadwal_tryout_id')
                    ->label('Jadwal Tryout')
                    ->relationship('jadwalTryout', 'nama_sesi')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('aktivitas')
                    ->options([
                        'login' => 'Login',
                        'mulai_ujian' => 'Mulai Ujian',
                        'simpan_jawaban' => 'Simpan Jawaban',
                        'submit' => 'Submit',
                        'timeout' => 'Timeout',
                        'disconnect' => 'Disconnect',
                        'force_submit' => 'Force Submit',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageUjianActivityLogs::route('/'),
        ];
    }
}
