<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RefPaketSoalResource\Pages;
use App\Filament\Resources\RefPaketSoalResource\RelationManagers;
use App\Models\RefPaketSoal;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RefPaketSoalResource extends Resource
{
    protected static ?string $model = RefPaketSoal::class;

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        return $user->hasRole('super_admin') || $user->hasPermissionTo('manage_soal');
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if ($user->isAdmin() && $user->jenjang) {
            $query->whereHas('mapel', function ($q) use ($user) {
                $q->where('jenjang', '=', $user->jenjang);
            });
        }

        return $query;
    }

    protected static ?string $navigationLabel = 'Paket Soal';
    protected static ?string $modelLabel = 'Paket Soal';
    protected static ?string $pluralModelLabel = 'Paket Soal';
    protected static ?string $navigationGroup = 'Bank Soal & Mata Pelajaran';
    protected static ?int $navigationSort = 4;

    protected static ?string $navigationIcon = 'heroicon-o-folder';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('mapel_id')
                    ->options(function () {
                        $user = auth()->user();
                        $query = \App\Models\RefMapel::query();

                        if ($user->isAdmin() && $user->jenjang) {
                            $query->where('jenjang', $user->jenjang);
                        }

                        return $query->get()->mapWithKeys(fn($m) => [$m->id => "{$m->nama_mapel} - {$m->jenjang}"]);
                    })
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('Mata Pelajaran')
                    ->reactive()
                    ->afterStateUpdated(function (callable $set, $state) {
                        if ($state) {
                            $mapel = \App\Models\RefMapel::find($state);
                            if ($mapel) {
                                $set('jenjang', $mapel->jenjang);
                                $set('tingkat', null); // Clear tingkat when mapel changes
                            }
                        }
                    }),
                Forms\Components\TextInput::make('nama_paket')
                    ->label('Nama Paket Soal')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('jenjang')
                    ->label('Jenjang')
                    ->disabled()
                    ->dehydrated()
                    ->helperText('Otomatis dari Mata Pelajaran'),
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
                Forms\Components\Textarea::make('keterangan')
                    ->label('Keterangan')
                    ->placeholder('Catatan atau deskripsi untuk kategori ini...')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_paket')
                    ->label('Nama Kategori')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('mapel.nama_mapel')
                    ->label('Mata Pelajaran')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tingkat')
                    ->label('Tingkat')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('jenjang')
                    ->colors([
                        'info' => 'SD',
                        'success' => 'SMP',
                        'warning' => 'SMA',
                        'primary' => 'UMUM',
                    ]),
                Tables\Columns\TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->limit(30)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('soal_count')
                    ->label('Jumlah Soal')
                    ->counts('soal')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('advanced_filters')
                    ->columnSpanFull()
                    ->form([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Select::make('jenjang')
                                    ->label('Jenjang')
                                    ->options([
                                        'SD' => 'SD',
                                        'SMP' => 'SMP',
                                        'SMA' => 'SMA',
                                        'SMK' => 'SMK',
                                        'UMUM' => 'UMUM',
                                    ])
                                    ->placeholder('All')
                                    ->visible(fn () => ! (auth()->user()->isAdmin() && auth()->user()->jenjang))
                                    ->live(),
                                
                                Forms\Components\Select::make('mapel_id')
                                    ->label('Mata Pelajaran')
                                    ->placeholder('All')
                                    ->options(function (Forms\Get $get) {
                                        $user = auth()->user();
                                        $jenjang = $get('jenjang') ?? ($user->isAdmin() ? $user->jenjang : null);
                                        
                                        return \App\Models\RefMapel::when($jenjang, fn($q) => $q->where('jenjang', $jenjang))
                                            ->get()
                                            ->mapWithKeys(fn($m) => [$m->id => "{$m->nama_mapel} - {$m->jenjang}"]);
                                    })
                                    ->live()
                                    ->searchable()
                                    ->preload(),

                                Forms\Components\Select::make('tingkat')
                                    ->label('Tingkat')
                                    ->placeholder('All')
                                    ->options([
                                        '1' => 'Tingkat 1', '2' => 'Tingkat 2', '3' => 'Tingkat 3',
                                        '4' => 'Tingkat 4', '5' => 'Tingkat 5', '6' => 'Tingkat 6',
                                        '7' => 'Tingkat 7', '8' => 'Tingkat 8', '9' => 'Tingkat 9',
                                        '10' => 'Tingkat 10', '11' => 'Tingkat 11', '12' => 'Tingkat 12',
                                    ]),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['mapel_id'], fn ($q, $mId) => $q->where('mapel_id', $mId))
                            ->when($data['tingkat'], fn ($q, $t) => $q->where('tingkat', $t))
                            ->when($data['jenjang'] && empty($data['mapel_id']), function ($q) use ($data) {
                                $q->whereHas('mapel', fn ($m) => $m->where('jenjang', $data['jenjang']));
                            });
                    })
            ], layout: \Filament\Tables\Enums\FiltersLayout::AboveContent)
            ->filtersFormColumns(4)
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
            'index' => Pages\ListRefPaketSoals::route('/'),
            'create' => Pages\CreateRefPaketSoal::route('/create'),
            'edit' => Pages\EditRefPaketSoal::route('/{record}/edit'),
        ];
    }
}
