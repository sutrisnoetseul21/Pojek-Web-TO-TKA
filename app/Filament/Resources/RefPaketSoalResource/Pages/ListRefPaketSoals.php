<?php

namespace App\Filament\Resources\RefPaketSoalResource\Pages;

use App\Filament\Resources\RefPaketSoalResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRefPaketSoals extends ListRecords
{
    protected static string $resource = RefPaketSoalResource::class;

    protected function getHeaderActions(): array
    {
        $user = auth()->user();
        $userJenjang = $user->jenjang ?? null;

        return [
            Actions\CreateAction::make(),
            
            Actions\Action::make('download_template')
                ->label('Template Excel')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action(function () use ($user) {
                    $sekolahNama = $user->sekolahRelation ? $user->sekolahRelation->nama_sekolah : 'Template';
                    $filename = "{$sekolahNama} - Template Input Kategori Soal.xlsx";
                    return \Maatwebsite\Excel\Facades\Excel::download(
                        new \App\Exports\RefPaketSoalTemplateExport($user->jenjang ?? null), 
                        $filename
                    );
                }),

            Actions\Action::make('import_paket_soal')
                ->label('Import Excel')
                ->icon('heroicon-o-document-arrow-up')
                ->color('warning')
                ->form([
                    \Filament\Forms\Components\FileUpload::make('attachment')
                        ->label('File Excel')
                        ->required()
                        ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel']),
                ])
                ->action(function (array $data) use ($userJenjang) {
                    $file = storage_path('app/public/' . $data['attachment']);
                    
                    try {
                        \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\RefPaketSoalImport($userJenjang), $file);
                        \Filament\Notifications\Notification::make()
                            ->title('Berhasil import Kategori Soal')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        \Filament\Notifications\Notification::make()
                            ->title('Gagal import Kategori Soal')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
