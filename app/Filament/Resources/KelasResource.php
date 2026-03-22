<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KelasResource\Pages;
use App\Models\Kelas;
use App\Enums\Jenjang;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class KelasResource extends Resource
{
    protected static ?string $model = Kelas::class;

    protected static ?string $navigationLabel = 'Kelas';
    protected static ?string $modelLabel = 'Kelas';
    protected static ?string $pluralModelLabel = 'Kelas';
    protected static ?string $navigationGroup = 'Manajemen Peserta dan Ruangan';
    protected static ?int $navigationSort = 2;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        return $user->hasRole('super_admin') || $user->hasPermissionTo('manage_peserta');
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);

        $user = auth()->user();

        // Admin hanya bisa melihat kelas di sekolahnya
        if ($user->hasRole('admin') && $user->sekolah_id) {
            $query->where('sekolah_id', $user->sekolah_id);
        }

        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Data Kelas')
                    ->description('Informasi kelas')
                    ->schema([
                        Forms\Components\Select::make('sekolah_id')
                            ->label('Sekolah')
                            ->relationship('sekolah', 'nama_sekolah')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $sekolah = \App\Models\Sekolah::find($state);
                                    if ($sekolah) {
                                        $set('jenjang', $sekolah->jenjang);
                                        $set('tingkat', null); // Clear tingkat when sekolah changes
                                    }
                                }
                            })
                            ->createOptionForm([
                                Forms\Components\TextInput::make('nama_sekolah')
                                    ->required()
                                    ->maxLength(100),
                                Forms\Components\TextInput::make('npsn')
                                    ->required()
                                    ->unique('sekolah', 'npsn')
                                    ->length(8),
                            ])
                            ->default(fn () => auth()->user()->sekolah_id)
                            ->disabled(fn () => auth()->user()->hasRole('admin'))
                            ->dehydrated(),
                        Forms\Components\Select::make('jenjang')
                            ->label('Jenjang')
                            ->options(
                                collect(Jenjang::cases())->mapWithKeys(fn($j) => [$j->value => $j->label()])
                            )
                            ->required()
                            ->default(fn () => auth()->user()->jenjang)
                            ->disabled()
                            ->dehydrated(),
                        Forms\Components\Select::make('tingkat')
                            ->label('Tingkat')
                            ->options(function (Forms\Get $get) {
                                $jenjang = $get('jenjang');
                                return match ($jenjang) {
                                    'SD' => [1=>1, 2=>2, 3=>3, 4=>4, 5=>5, 6=>6],
                                    'SMP' => [7=>7, 8=>8, 9=>9],
                                    'SMA', 'SMK' => [10=>10, 11=>11, 12=>12],
                                    default => []
                                };
                            })
                            ->required(fn (Forms\Get $get) => $get('jenjang') !== 'UMUM')
                            ->placeholder('-- Pilih Tingkat --'),
                        Forms\Components\TextInput::make('nama_kelas')
                            ->label('Nama Kelas')
                            ->required()
                            ->maxLength(50)
                            ->placeholder('Contoh: 10-A, 11-IPA-2'),
                        Forms\Components\Textarea::make('keterangan')
                            ->label('Keterangan')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sekolah.nama_sekolah')
                    ->label('Sekolah')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nama_kelas')
                    ->label('Kelas')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tingkat')
                    ->label('Tingkat')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('jenjang')
                    ->colors([
                        'primary' => 'SD',
                        'success' => 'SMP',
                        'warning' => 'SMA',
                        'danger' => 'SMK',
                        'info' => 'UMUM',
                    ]),
                Tables\Columns\TextColumn::make('peserta_count')
                    ->label('Jml Peserta')
                    ->counts('peserta')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('nama_kelas', 'asc')
            ->filters([
                Tables\Filters\SelectFilter::make('sekolah_id')
                    ->relationship('sekolah', 'nama_sekolah')
                    ->label('Sekolah')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('jenjang')
                    ->options(
                        collect(Jenjang::cases())->mapWithKeys(fn($j) => [$j->value => $j->label()])
                    ),
                Tables\Filters\SelectFilter::make('tingkat')
                    ->label('Tingkat')
                    ->options([
                        '1' => 'Tingkat 1', '2' => 'Tingkat 2', '3' => 'Tingkat 3',
                        '4' => 'Tingkat 4', '5' => 'Tingkat 5', '6' => 'Tingkat 6',
                        '7' => 'Tingkat 7', '8' => 'Tingkat 8', '9' => 'Tingkat 9',
                        '10' => 'Tingkat 10', '11' => 'Tingkat 11', '12' => 'Tingkat 12',
                    ]),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKelas::route('/'),
            'create' => Pages\CreateKelas::route('/create'),
            'edit' => Pages\EditKelas::route('/{record}/edit'),
        ];
    }
}
