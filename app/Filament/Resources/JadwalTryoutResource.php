<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JadwalTryoutResource\Pages;
use App\Filament\Resources\JadwalTryoutResource\RelationManagers;
use App\Models\JadwalTryout;
use App\Models\PaketTryout;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class JadwalTryoutResource extends Resource
{
    protected static ?string $model = JadwalTryout::class;

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        return $user->hasRole('super_admin') || $user->hasPermissionTo('manage_soal');
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if ($user->isAdmin() && $user->sekolah_id) {
            $query->where('sekolah_id', $user->sekolah_id);
        }

        return $query;
    }

    protected static ?string $navigationLabel = 'Jadwal Tryout';
    protected static ?string $modelLabel = 'Jadwal Tryout';
    protected static ?string $pluralModelLabel = 'Jadwal Tryout';
    protected static ?string $navigationGroup = 'Tryout';
    protected static ?int $navigationSort = 2;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Jadwal')
                    ->description('Tentukan waktu pelaksanaan tryout')
                    ->schema([
                        Forms\Components\Select::make('sekolah_id')
                            ->label('Sekolah')
                            ->relationship('sekolah', 'nama_sekolah')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->disabled(fn () => auth()->user()->isAdmin())
                            ->dehydrated()
                            ->default(fn () => auth()->user()->sekolah_id)
                            ->afterStateUpdated(fn (Forms\Set $set) => $set('paket_tryout_id', null))
                            ->afterStateUpdated(fn (Forms\Set $set) => $set('kelases', [])),
                        Forms\Components\Select::make('paket_tryout_id')
                            ->label('Paket Tryout')
                            ->options(function (Forms\Get $get) {
                                $sekolahId = $get('sekolah_id');
                                return PaketTryout::where('is_active', true)
                                    ->where(function ($q) use ($sekolahId) {
                                        $q->whereNull('sekolah_id')
                                          ->when($sekolahId, fn ($query) => $query->orWhere('sekolah_id', $sekolahId));
                                    })
                                    ->pluck('nama_paket', 'id');
                            })
                            ->searchable()
                            ->required()
                            ->helperText('Pilih paket tryout yang akan dijadwalkan'),
                        Forms\Components\Select::make('kelases')
                            ->label('Target Kelas')
                            ->relationship('kelases', 'nama_kelas', modifyQueryUsing: fn (Builder $query, Forms\Get $get) => $query->where('sekolah_id', $get('sekolah_id')))
                            ->multiple()
                            ->required()
                            ->preload()
                            ->searchable()
                            ->helperText('Pilih satu atau lebih kelas yang wajib mengikuti jadwal ini'),
                        Forms\Components\TextInput::make('nama_sesi')
                            ->label('Nama Sesi')
                            ->placeholder('Contoh: Sesi 1 - Pagi')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('kuota_peserta')
                            ->label('Kuota Peserta')
                            ->numeric()
                            ->placeholder('Kosongkan jika unlimited')
                            ->helperText('Batas jumlah peserta (opsional)'),
                    ])->columns(2),

                Forms\Components\Section::make('Waktu Pelaksanaan')
                    ->schema([
                        Forms\Components\DateTimePicker::make('tgl_mulai')
                            ->label('Tanggal & Jam Mulai')
                            ->required()
                            ->native(false)
                            ->displayFormat('d M Y, H:i'),
                        Forms\Components\DateTimePicker::make('tgl_selesai')
                            ->label('Tanggal & Jam Selesai')
                            ->required()
                            ->native(false)
                            ->displayFormat('d M Y, H:i')
                            ->afterOrEqual('tgl_mulai'),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Jadwal Aktif')
                            ->default(true)
                            ->helperText('Jadwal aktif akan tampil untuk siswa'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('paketTryout.nama_paket')
                    ->label('Paket Tryout')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sekolah.nama_sekolah')
                    ->label('Sekolah')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('kelases.nama_kelas')
                    ->label('Kelas')
                    ->badge()
                    ->separator(', '),
                Tables\Columns\TextColumn::make('nama_sesi')
                    ->label('Sesi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('token')
                    ->label('Token')
                    ->badge()
                    ->color('warning')
                    ->copyable()
                    ->searchable()
                    ->fontFamily('mono'),
                Tables\Columns\TextColumn::make('tgl_mulai')
                    ->label('Mulai')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tgl_selesai')
                    ->label('Selesai')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('kuota_peserta')
                    ->label('Kuota')
                    ->getStateUsing(fn($record) => $record->kuota_peserta ?? '∞'),
                Tables\Columns\TextColumn::make('peserta_count')
                    ->label('Peserta')
                    ->counts('peserta')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->getStateUsing(fn($record) => $record->status)
                    ->colors([
                        'info' => 'AKAN_DATANG',
                        'success' => 'BERLANGSUNG',
                        'gray' => 'SELESAI',
                    ])
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'AKAN_DATANG' => 'Akan Datang',
                        'BERLANGSUNG' => 'Berlangsung',
                        'SELESAI' => 'Selesai',
                        default => $state,
                    }),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->defaultSort('tgl_mulai', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('paket_tryout_id')
                    ->relationship('paketTryout', 'nama_paket')
                    ->label('Paket Tryout')
                    ->preload(),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
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
            'index' => Pages\ListJadwalTryouts::route('/'),
            'create' => Pages\CreateJadwalTryout::route('/create'),
            'edit' => Pages\EditJadwalTryout::route('/{record}/edit'),
        ];
    }
}

