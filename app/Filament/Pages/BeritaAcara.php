<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class BeritaAcara extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Administrasi Tes';

    protected static ?int $navigationSort = 5;

    protected static string $view = 'filament.pages.berita-acara';
}
