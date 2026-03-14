<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KartuPesertaResource\Pages;
use App\Filament\Resources\KartuPesertaResource\Pages\PreviewKartuPeserta;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection;

class KartuPesertaResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationLabel = 'Kartu Peserta';
    protected static ?string $modelLabel = 'Kartu Peserta';
    protected static ?string $pluralModelLabel = 'Kartu Peserta';
    protected static ?string $navigationGroup = 'Manajemen Peserta';
    protected static ?int $navigationSort = 4;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        return $user->hasRole('super_admin') || $user->hasPermissionTo('manage_peserta');
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
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
                // Read-only view for Kartu Peserta
                Forms\Components\TextInput::make('username')->disabled(),
                Forms\Components\TextInput::make('nama_lengkap')->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('username')
                    ->label('Username')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nama_lengkap')
                    ->label('Nama')
                    ->searchable()
                    ->placeholder('Belum diisi'),
                Tables\Columns\TextColumn::make('sekolahRelation.nama_sekolah')
                    ->label('Sekolah')
                    ->searchable(),
                Tables\Columns\TextColumn::make('kelas.nama_kelas')
                    ->label('Kelas')
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('sekolah_id')
                    ->label('Sekolah')
                    ->relationship('sekolahRelation', 'nama_sekolah')
                    ->searchable()
                    ->preload()
                    ->placeholder('Semua Sekolah')
                    ->hidden(fn () => auth()->user()->hasRole('admin')),
                Tables\Filters\SelectFilter::make('kelas_id')
                    ->label('Kelas')
                    ->relationship('kelas', 'nama_kelas')
                    ->searchable()
                    ->preload(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('cetak_masal')
                    ->label('Cetak Masal')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->form([
                        Forms\Components\Select::make('sekolah_id')
                            ->label('Sekolah')
                            ->relationship('sekolahRelation', 'nama_sekolah')
                            ->default(fn () => auth()->user()->sekolah_id)
                            ->disabled(fn () => auth()->user()->hasRole('admin'))
                            ->dehydrated()
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live(),
                        Forms\Components\Select::make('kelas_id')
                            ->label('Kelas')
                            ->options(function (Forms\Get $get) {
                                $sekolahId = $get('sekolah_id');
                                if (!$sekolahId) return [];
                                return \App\Models\Kelas::where('sekolah_id', $sekolahId)
                                    ->pluck('nama_kelas', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->placeholder('Semua Kelas di Sekolah Ini')
                            ->helperText('Kosongkan untuk mencetak semua kelas di sekolah terpilih'),
                    ])
                    ->action(function (array $data) {
                        return redirect()->to(PreviewKartuPeserta::getUrl([
                            'sekolah_id' => $data['sekolah_id'],
                            'kelas_id'   => $data['kelas_id'] ?: null,
                        ]));
                    })
                    ->visible(fn () => auth()->user()->hasRole('super_admin') || auth()->user()->hasRole('admin')),
            ])
            ->actions([
                Tables\Actions\Action::make('cetak_satuan')
                    ->label('Cetak')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->url(fn ($record) => Pages\PreviewKartuPeserta::getUrl(['ids' => $record->id])),
            ])
            ->bulkActions([
                BulkAction::make('cetak_kartu_terpilih')
                    ->label('Cetak Terpilih')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->action(function (Collection $records) {
                        $ids = $records->pluck('id')->implode(',');
                        return redirect()->to(Pages\PreviewKartuPeserta::getUrl(['ids' => $ids]));
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageKartuPesertas::route('/'),
            'preview' => Pages\PreviewKartuPeserta::route('/preview'),
        ];
    }
}
