<?php

namespace App\Filament\Resources\BankSoalResource\Pages;

use App\Filament\Resources\BankSoalResource;
use App\Exports\BankSoalTemplateExport;
use App\Imports\BankSoalImport;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

class ListBankSoals extends ListRecords
{
    protected static string $resource = BankSoalResource::class;

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
                    $filename = "{$sekolahNama} - Template Bank Soal.xlsx";
                    return Excel::download(new BankSoalTemplateExport($user->jenjang ?? null), $filename);
                }),
                
            Actions\Action::make('import_soal')
                ->label('Import Excel')
                ->icon('heroicon-o-document-arrow-up')
                ->color('warning')
                ->form([
                    FileUpload::make('attachment')
                        ->label('File Excel')
                        ->required()
                        ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel']),
                ])
                ->action(function (array $data) use ($user) {
                    $file = storage_path('app/public/' . $data['attachment']);
                    
                    try {
                        Excel::import(new BankSoalImport($user->jenjang ?? null), $file);
                        Notification::make()
                            ->title('Berhasil import Soal')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Gagal import Soal')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
