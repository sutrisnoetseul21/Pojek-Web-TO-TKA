<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class DaftarLogin extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-arrow-left-on-rectangle';
    protected static ?string $navigationLabel = 'Daftar Login';
    protected static ?string $title = 'Daftar Login';
    protected static ?string $navigationGroup = 'Monitoring Ujian (Baru)';
    protected static ?int $navigationSort = 5;
    protected static string $view = 'filament.pages.daftar-login';

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user->hasRole('super_admin') || $user->hasPermissionTo('manage_monitoring');
    }
}
