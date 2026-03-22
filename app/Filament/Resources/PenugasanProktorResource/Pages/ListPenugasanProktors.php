<?php

namespace App\Filament\Resources\PenugasanProktorResource\Pages;

use App\Filament\Resources\PenugasanProktorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPenugasanProktors extends ListRecords
{
    protected static string $resource = PenugasanProktorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->visible(fn () => auth()->user()->hasRole(['super_admin', 'admin'])),
        ];
    }
}
