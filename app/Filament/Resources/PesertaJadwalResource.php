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
            ->poll('5s')
            ->filters([
                Tables\Filters\SelectFilter::make('sekolah')
                    ->label('Sekolah')
                    ->relationship('user.sekolahRelation', 'nama_sekolah')
                    ->visible(fn () => auth()->user()->hasRole('super_admin'))
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('jadwal_tryout_id')
                    ->label('Pilih Sesi Ujian')
                    ->options(
                        \App\Models\JadwalTryout::with('paketTryout')
                            ->where('is_active', true)
                            ->get()
                            ->mapWithKeys(fn ($j) => [$j->id => ($j->paketTryout?->nama_paket ?? 'Tanpa Paket') . ' - ' . $j->nama_sesi])
                    )
                    ->searchable()
                    ->preload()
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['value'])) {
                            return $query->where('jadwal_tryout_id', -1);
                        }
                        return $query->where('jadwal_tryout_id', $data['value']);
                    }),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'registered' => 'Belum Mulai',
                        'started' => 'Sedang Tes',
                        'completed' => 'Selesai',
                        'timeout' => 'Timeout',
                        'disconnected' => 'Terputus',
                    ]),
            ], layout: \Filament\Tables\Enums\FiltersLayout::AboveContent)
            ->filtersFormColumns(1)
            ->emptyStateHeading('Pilih Sesi Ujian Terlebih Dahulu')
            ->emptyStateDescription('Silakan pilih jadwal dari dropdown di atas untuk memunculkan data peserta.')
            ->emptyStateIcon('heroicon-o-clipboard-document-list')
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('force_lanjut_mapel')
                    ->label('Force Lanjut Mapel')
                    ->icon('heroicon-o-arrow-right-circle')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Paksa Lanjut Mapel')
                    ->modalDescription('Apakah Anda yakin ingin memaksa peserta ini melompati mapel dan beralih ke materi selanjutnya?')
                    ->visible(fn ($record) => $record->status === 'started')
                    ->action(fn ($record) => $record->calculateAndSubmit(false)),

                Tables\Actions\Action::make('force_submit')
                    ->label('Force Selesai Total')
                    ->icon('heroicon-o-stop')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Paksa Selesai Ujian')
                    ->modalDescription('Aksi ini akan menghentikan seluruh rangkaian ujian siswa seketika dan mengunci akun. Lanjutkan?')
                    ->visible(fn ($record) => $record->status === 'started')
                    ->action(function ($record) {
                        $record->calculateAndSubmit(true);
                        
                        \App\Models\UjianActivityLog::create([
                            'peserta_jadwal_id' => $record->id,
                            'user_id' => $record->user_id,
                            'jadwal_tryout_id' => $record->jadwal_tryout_id,
                            'aktivitas' => 'force_submit',
                            'keterangan' => 'Ujian dihentikan paksa (Selesai Total) oleh admin ' . auth()->user()->nama_lengkap,
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
