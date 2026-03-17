<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class DaftarPeserta extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static ?string $navigationLabel = 'Daftar Peserta';
    protected static ?string $title = 'Daftar Peserta';
    protected static ?string $navigationGroup = 'Monitoring Ujian (Baru)';
    protected static ?int $navigationSort = 2;
    protected static string $view = 'filament.pages.daftar-peserta';

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user->hasRole('super_admin') || $user->hasPermissionTo('manage_monitoring');
    }
}
