<?php

namespace App\Filament\Resources\SekolahResource\Pages;

use App\Filament\Resources\SekolahResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSekolahs extends ListRecords
{
    protected static string $resource = SekolahResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            
            Actions\Action::make('download_template')
                ->label('Template Excel')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action(function () {
                    return \Maatwebsite\Excel\Facades\Excel::download(
                        new \App\Exports\SekolahTemplateExport(), 
                        'Template_Input_Sekolah.xlsx'
                    );
                }),

            Actions\Action::make('import_sekolah')
                ->label('Import Excel')
                ->icon('heroicon-o-document-arrow-up')
                ->color('warning')
                ->form([
                    \Filament\Forms\Components\FileUpload::make('attachment')
                        ->label('File Excel')
                        ->required()
                        ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel']),
                ])
                ->action(function (array $data) {
                    $file = storage_path('app/public/' . $data['attachment']);
                    
                    try {
                        \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\SekolahImport(), $file);
                        \Filament\Notifications\Notification::make()
                            ->title('Berhasil import Sekolah')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        \Filament\Notifications\Notification::make()
                            ->title('Gagal import Sekolah')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
