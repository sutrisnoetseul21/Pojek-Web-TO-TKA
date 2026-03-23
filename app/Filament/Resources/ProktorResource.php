<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProktorResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProktorResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationLabel = 'Akun Proktor';
    protected static ?string $modelLabel = 'Proktor';
    protected static ?string $pluralModelLabel = 'Proktor';
    protected static ?string $navigationGroup = 'Manajemen Proktor';
    protected static ?int $navigationSort = 1;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        return $user->hasRole('super_admin') || $user->hasRole('admin');
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class])
            ->where('role', '=', 'proktor');

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
                Forms\Components\Section::make('Akun Proktor')
                    ->description('Informasi login untuk Proktor Ruangan')
                    ->schema([
                        Forms\Components\TextInput::make('username')
                            ->label('Username')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->disabled(fn($record) => $record !== null),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('nama_lengkap')
                            ->label('Nama Lengkap (Proktor)')
                            ->required(),
                        Forms\Components\TextInput::make('plain_password')
                            ->label('Password')
                            ->password()
                            ->revealable()
                            ->placeholder('Kosongkan untuk generate otomatis')
                            ->helperText('Jika dikosongkan saat tambah proktor, password akan digenerate otomatis'),
                        Forms\Components\Hidden::make('role')
                            ->default('proktor'),
                    ])->columns(['default' => 2]),
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
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('nama_lengkap')
                    ->label('Nama Proktor')
                    ->searchable()
                    ->placeholder('Belum diisi'),
            ])
            ->defaultSort('username', 'asc')
            ->filters([
                Tables\Filters\Filter::make('advanced_filters')
                    ->columnSpanFull()
                    ->form([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Select::make('sekolah_id')
                                    ->label('Sekolah')
                                    ->placeholder('All')
                                    ->options(fn() => \App\Models\Sekolah::pluck('nama_sekolah', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->visible(fn () => auth()->user()->hasRole('super_admin'))
                                    ->live(),

                                Forms\Components\Select::make('jenjang')
                                    ->label('Jenjang')
                                    ->placeholder('All')
                                    ->options([
                                        'SD' => 'SD', 'SMP' => 'SMP', 'SMA' => 'SMA', 'SMK' => 'SMK', 'UMUM' => 'UMUM'
                                    ])
                                    ->live(),

                                Forms\Components\Select::make('tingkat')
                                    ->label('Tingkat')
                                    ->placeholder('All')
                                    ->options(function (Forms\Get $get) {
                                        $jenjang = $get('jenjang');
                                        return match ($jenjang) {
                                            'SD' => ['1'=>'1', '2'=>'2', '3'=>'3', '4'=>'4', '5'=>'5', '6'=>'6'],
                                            'SMP' => ['7'=>'7', '8'=>'8', '9'=>'9'],
                                            'SMA', 'SMK' => ['10'=>'10', '11'=>'11', '12'=>'12'],
                                            default => [
                                                '1'=>'1', '2'=>'2', '3'=>'3', '4'=>'4', '5'=>'5', '6'=>'6',
                                                '7'=>'7', '8'=>'8', '9'=>'9', '10'=>'10', '11'=>'11', '12'=>'12'
                                            ]
                                        };
                                    })
                                    ->live(),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['sekolah_id'], fn ($q, $sId) => $q->where('sekolah_id', $sId))
                            ->when($data['jenjang'], fn ($q, $j) => $q->whereHas('penugasanRuangan.jadwalTryout.paketTryout', fn($pq) => $pq->where('jenjang', $j)))
                            ->when($data['tingkat'], fn ($q, $t) => $q->whereHas('penugasanRuangan.jadwalTryout.paketTryout', fn($pq) => $pq->where('tingkat', $t)));
                    })
            ], layout: \Filament\Tables\Enums\FiltersLayout::AboveContent)
            ->filtersFormColumns(3)
            ->actions([
                Tables\Actions\Action::make('alokasi')
                    ->label('Alokasi')
                    ->icon('heroicon-o-calendar-days')
                    ->color('warning')
                    ->modalHeading('Daftar Penugasan Proktor')
                    ->modalSubmitAction(function ($action, $record) {
                        return $action
                            ->label('Edit')
                            ->color('warning')
                            ->url(\App\Filament\Resources\PenugasanProktorResource::getUrl('index', [
                                'tableFilters' => [
                                    'advanced_filters' => [
                                        'proktor_id' => $record->id,
                                    ],
                                ],
                            ]));
                    })
                    ->modalCancelActionLabel('Tutup')
                    ->modalContent(fn ($record) => view('filament.actions.proktor-alokasi-modal', ['record' => $record])),
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
            \App\Filament\Resources\ProktorResource\RelationManagers\JadwalRuanganRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProktors::route('/'),
            'create' => Pages\CreateProktor::route('/create'),
            'edit' => Pages\EditProktor::route('/{record}/edit'),
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

