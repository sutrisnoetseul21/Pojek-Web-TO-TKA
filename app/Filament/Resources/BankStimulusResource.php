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

    protected static ?string $navigationLabel = 'Stimulus (Wacana)';
    protected static ?string $modelLabel = 'Stimulus';
    protected static ?string $pluralModelLabel = 'Stimulus';
    protected static ?string $navigationGroup = 'Bank Soal';

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('mapel_id')
                    ->options(\App\Models\RefMapel::all()->mapWithKeys(fn($m) => [$m->id => "{$m->nama_mapel} - {$m->jenjang}"]))
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn(Forms\Set $set) => $set('paket_id', null)),
                Forms\Components\Select::make('paket_id')
                    ->relationship('paket', 'nama_paket', modifyQueryUsing: fn(Builder $query, Forms\Get $get) => $query->where('mapel_id', $get('mapel_id')))
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
                    ->label('Paket')
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
                Tables\Filters\SelectFilter::make('mapel_id')
                    ->relationship('mapel', 'nama_mapel')
                    ->label('Mata Pelajaran')
                    ->preload(),
                Tables\Filters\SelectFilter::make('paket_id')
                    ->relationship('paket', 'nama_paket')
                    ->label('Paket')
                    ->preload(),
                Tables\Filters\SelectFilter::make('tipe')
                    ->options([
                        'TEKS' => 'Teks',
                        'GAMBAR' => 'Gambar',
                        'AUDIO' => 'Audio',
                        'VIDEO' => 'Video',
                    ]),
            ])
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
                    ->action(fn () => Excel::download(new StimulusTemplateExport, 'template-stimulus.xlsx')),
                
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
                            Excel::import(new StimulusImport, storage_path('app/public/' . $data['file']));
                            
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
