<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PesertaJadwalResource\Pages;
use App\Filament\Resources\PesertaJadwalResource\RelationManagers;
use App\Models\PesertaJadwal;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PesertaJadwalResource extends Resource
{
    protected static ?string $model = \App\Models\PesertaJadwal::class;

    protected static ?string $navigationLabel = 'Peserta Sedang Tes';
    protected static ?string $modelLabel = 'Peserta Sedang Tes';
    protected static ?string $pluralModelLabel = 'Peserta Sedang Tes';
    protected static ?string $navigationGroup = 'Monitoring Ujian';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        return $user->hasRole('super_admin') || $user->hasPermissionTo('manage_monitoring');
    }

    public static function canCreate(): bool { return false; }
    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool { return false; }

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
                Forms\Components\Section::make('Informasi Peserta')
                    ->schema([
                        Forms\Components\TextInput::make('user.nama_lengkap')
                            ->label('Nama Peserta'),
                        Forms\Components\TextInput::make('status')
                            ->label('Status'),
                        Forms\Components\DateTimePicker::make('waktu_mulai')
                            ->label('Waktu Mulai'),
                        Forms\Components\TextInput::make('sisa_waktu')
                            ->label('Sisa Waktu (Menit)'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.nama_lengkap')
                    ->label('Nama Peserta')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.username')
                    ->label('Username')
                    ->searchable()
                    ->fontFamily('mono'),
                Tables\Columns\TextColumn::make('user.kelas.nama_kelas')
                    ->label('Kelas')
                    ->searchable(),
                Tables\Columns\TextColumn::make('jadwalTryout.nama_sesi')
                    ->label('Jadwal')
                    ->searchable(),
                Tables\Columns\TextColumn::make('waktu_mulai')
                    ->label('Mulai')
                    ->dateTime('H:i:s')
                    ->sortable(),
                Tables\Columns\TextColumn::make('sisa_waktu')
                    ->label('Sisa')
                    ->getStateUsing(fn ($record) => $record->sisa_waktu ? "{$record->sisa_waktu}m" : '-')
                    ->color(fn ($record) => $record->sisa_waktu < 5 ? 'danger' : 'success'),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'gray' => 'registered',
                        'info' => 'started',
                        'success' => 'completed',
                        'warning' => 'timeout',
                        'danger' => 'disconnected',
                    ])
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'registered' => 'Belum Mulai',
                        'started' => 'Sedang Tes',
                        'completed' => 'Selesai',
                        'timeout' => 'Timeout',
                        'disconnected' => 'Terputus',
                        default => $state,
                    }),
            ])
            ->defaultSort('waktu_mulai', 'desc')
            ->poll('5s') // Refresh table every 5 seconds for real-time feel
            ->filters([
                Tables\Filters\SelectFilter::make('sekolah')
                    ->label('Sekolah')
                    ->relationship('user.sekolahRelation', 'nama_sekolah')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('jadwal_tryout_id')
                    ->label('Jadwal Aktif')
                    ->options(\App\Models\JadwalTryout::where('is_active', true)->where('tgl_selesai', '>=', now()->subHours(5))->pluck('nama_sesi', 'id'))
                    ->query(fn (Builder $query, array $data) => $query->when($data['value'], fn ($q) => $q->where('jadwal_tryout_id', $data['value'])))
                    ->preload(),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'registered' => 'Belum Mulai',
                        'started' => 'Sedang Tes',
                        'completed' => 'Selesai',
                        'timeout' => 'Timeout',
                        'disconnected' => 'Terputus',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('force_submit')
                    ->label('Force Submit')
                    ->icon('heroicon-o-stop')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status === 'started')
                    ->action(function ($record) {
                        $record->update(['status' => 'completed', 'waktu_selesai' => now()]);
                        
                        \App\Models\UjianActivityLog::create([
                            'peserta_jadwal_id' => $record->id,
                            'user_id' => $record->user_id,
                            'jadwal_tryout_id' => $record->jadwal_tryout_id,
                            'aktivitas' => 'force_submit',
                            'keterangan' => 'Ujian dihentikan paksa oleh admin ' . auth()->user()->nama_lengkap,
                        ]);
                    }),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePesertaJadwals::route('/'),
        ];
    }
}
