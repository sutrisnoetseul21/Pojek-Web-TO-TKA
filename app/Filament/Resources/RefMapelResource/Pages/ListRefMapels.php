<?php

namespace App\Filament\Resources\RefMapelResource\Pages;

use App\Filament\Resources\RefMapelResource;
use App\Exports\RefMapelTemplateExport;
use App\Imports\RefMapelImport;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

class ListRefMapels extends ListRecords
{
    protected static string $resource = RefMapelResource::class;

    protected function getHeaderActions(): array
    {
        $user = auth()->user();

        return [
            Actions\CreateAction::make(),
            
            Actions\Action::make('download_template')
                ->label('Download Template')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action(function () use ($user) {
                    $sekolahNama = $user->sekolahRelation ? $user->sekolahRelation->nama_sekolah : 'Template';
                    $filename = "{$sekolahNama} - Template Mata Pelajaran.xlsx";
                    return Excel::download(new RefMapelTemplateExport($user->jenjang ?? null), $filename);
                }),

            Actions\Action::make('import_excel')
                ->label('Import Excel')
                ->icon('heroicon-o-document-arrow-up')
                ->color('warning')
                ->form([
                    FileUpload::make('attachment')
                        ->label('File Excel')
                        ->required()
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 
                            'application/vnd.ms-excel'
                        ]),
                ])
                ->action(function (array $data) use ($user) {
                    $file = storage_path('app/public/' . $data['attachment']);

                    try {
                        Excel::import(new RefMapelImport($user->jenjang ?? null), $file);
                        Notification::make()
                            ->title('Berhasil import Mata Pelajaran')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Gagal import Mata Pelajaran')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
