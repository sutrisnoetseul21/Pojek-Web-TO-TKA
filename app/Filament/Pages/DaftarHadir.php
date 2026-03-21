<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class DaftarHadir extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Administrasi Tes';

    protected static ?int $navigationSort = 6;

    protected static string $view = 'filament.pages.daftar-hadir';
}
