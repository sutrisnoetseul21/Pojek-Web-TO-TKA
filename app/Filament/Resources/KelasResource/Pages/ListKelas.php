<?php

namespace App\Filament\Resources\KelasResource\Pages;

use App\Filament\Resources\KelasResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKelas extends ListRecords
{
    protected static string $resource = KelasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            
            Actions\Action::make('download_template')
                ->label('Template Excel')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->form([
                    \Filament\Forms\Components\Select::make('sekolah_id')
                        ->label('Sekolah')
                        ->relationship('sekolah', 'nama_sekolah')
                        ->default(fn () => auth()->user()->sekolah_id)
                        ->disabled(fn () => auth()->user()->hasRole('admin'))
                        ->dehydrated()
                        ->required()
                        ->searchable()
                        ->preload(),
                ])
                ->action(function (array $data) {
                    $sekolah = \App\Models\Sekolah::find($data['sekolah_id']);
                    $filename = ($sekolah ? $sekolah->nama_sekolah : 'Template') . ' - Template Input Kelas.xlsx';
                    return \Maatwebsite\Excel\Facades\Excel::download(
                        new \App\Exports\KelasTemplateExport($data['sekolah_id']), 
                        $filename
                    );
                }),

            Actions\Action::make('import_kelas')
                ->label('Import Excel')
                ->icon('heroicon-o-document-arrow-up')
                ->color('warning')
                ->form([
                    \Filament\Forms\Components\Select::make('sekolah_id')
                        ->label('Target Sekolah')
                        ->relationship('sekolah', 'nama_sekolah')
                        ->default(fn () => auth()->user()->sekolah_id)
                        ->disabled(fn () => auth()->user()->hasRole('admin'))
                        ->dehydrated()
                        ->required()
                        ->searchable()
                        ->preload(),
                    \Filament\Forms\Components\FileUpload::make('attachment')
                        ->label('File Excel')
                        ->required()
                        ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel']),
                ])
                ->action(function (array $data) {
                    $file = storage_path('app/public/' . $data['attachment']);
                    
                    try {
                        \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\KelasImport($data['sekolah_id']), $file);
                        \Filament\Notifications\Notification::make()
                            ->title('Berhasil import Kelas')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        \Filament\Notifications\Notification::make()
                            ->title('Gagal import Kelas')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
