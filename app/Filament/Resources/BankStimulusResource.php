<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BankStimulusResource\Pages;
use App\Filament\Resources\BankStimulusResource\RelationManagers;
use App\Models\BankStimulus;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\StimulusTemplateExport;
use App\Imports\StimulusImport;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;

class BankStimulusResource extends Resource
{
    protected static ?string $model = BankStimulus::class;

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

    protected static ?string $navigationLabel = 'Stimulus (Wacana)';
    protected static ?string $modelLabel = 'Stimulus';
    protected static ?string $pluralModelLabel = 'Stimulus';
    protected static ?string $navigationGroup = 'Bank Soal & Mata Pelajaran';
    protected static ?int $navigationSort = 3;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

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
                    ->live()
                    ->afterStateUpdated(fn(Forms\Set $set) => $set('paket_id', null)),
                Forms\Components\Select::make('paket_id')
                    ->relationship('paket', 'nama_paket', modifyQueryUsing: fn(Builder $query, Forms\Get $get) => $query->where('mapel_id', $get('mapel_id')))
                    ->label('Paket Soal')
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('judul')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\RichEditor::make('konten')
                    ->required()
                    ->fileAttachmentsDisk('public')
                    ->fileAttachmentsDirectory('stimulus-images')
                    ->fileAttachmentsVisibility('public')
                    ->columnSpanFull(),
                Forms\Components\Select::make('tipe')
                    ->options([
                        'TEKS' => 'Teks',
                        'AUDIO' => 'Audio',
                        'VIDEO' => 'Video',
                        'GAMBAR' => 'Gambar',
                    ])
                    ->required()
                    ->default('TEKS'),
                Forms\Components\FileUpload::make('file_path')
                    ->disk('public')
                    ->directory('stimulus')
                    ->visibility('public'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('judul')
                    ->label('Judul Stimulus')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('mapel.nama_mapel')
                    ->label('Mata Pelajaran')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('paket.nama_paket')
                    ->label('Paket Soal')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('tipe')
                    ->colors([
                        'primary' => 'TEKS',
                        'success' => 'GAMBAR',
                        'warning' => 'AUDIO',
                        'danger' => 'VIDEO',
                    ]),
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
                        Forms\Components\Grid::make(4)
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

                                Forms\Components\Select::make('paket_id')
                                    ->label('Paket Soal')
                                    ->placeholder('All')
                                    ->options(function (Forms\Get $get) {
                                        $mapelId = $get('mapel_id');
                                        if (!$mapelId) return [];
                                        
                                        return \App\Models\RefPaketSoal::where('mapel_id', $mapelId)
                                            ->pluck('nama_paket', 'id');
                                    })
                                    ->disabled(fn (Forms\Get $get) => !$get('mapel_id'))
                                    ->searchable()
                                    ->preload(),
                                
                                Forms\Components\Select::make('tipe')
                                    ->label('Tipe')
                                    ->placeholder('All')
                                    ->options([
                                        'TEKS' => 'Teks',
                                        'GAMBAR' => 'Gambar',
                                        'AUDIO' => 'Audio',
                                        'VIDEO' => 'Video',
                                    ]),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['mapel_id'], fn ($q, $mId) => $q->where('mapel_id', $mId))
                            ->when($data['paket_id'], fn ($q, $pId) => $q->where('paket_id', $pId))
                            ->when($data['tipe'], fn ($q, $type) => $q->where('tipe', $type))
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
            ->headerActions([
                Tables\Actions\Action::make('download_template')
                    ->label('Download Template')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->action(function () {
                        $user = auth()->user();
                        $sekolahNama = $user->sekolahRelation ? $user->sekolahRelation->nama_sekolah : 'Template';
                        $filename = "{$sekolahNama} - Template Stimulus.xlsx";
                        return Excel::download(new StimulusTemplateExport($user->jenjang), $filename);
                    }),
                
                Tables\Actions\Action::make('import_excel')
                    ->label('Import Excel')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('success')
                    ->form([
                        Forms\Components\FileUpload::make('file')
                            ->label('File Excel')
                            ->required()
                            ->disk('public')
                            ->directory('temp-imports'),
                    ])
                    ->action(function (array $data) {
                        try {
                            $userJenjang = auth()->user()->jenjang;
                            Excel::import(new StimulusImport($userJenjang), storage_path('app/public/' . $data['file']));
                            
                            Notification::make()
                                ->title('Berhasil mengimpor data stimulus')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Gagal mengimpor data: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
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
            RelationManagers\SoalRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBankStimuli::route('/'),
            'create' => Pages\CreateBankStimulus::route('/create'),
            'view' => Pages\ViewBankStimulus::route('/{record}'),
            'edit' => Pages\EditBankStimulus::route('/{record}/edit'),
        ];
    }
}
