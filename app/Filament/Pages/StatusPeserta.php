<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class StatusPeserta extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Status Peserta';
    protected static ?string $title = 'Status Peserta';
    protected static ?string $navigationGroup = 'Monitoring Ujian (Baru)';
    protected static ?int $navigationSort = 1;
    protected static string $view = 'filament.pages.status-peserta';

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user->hasRole('super_admin') || $user->hasPermissionTo('manage_monitoring');
    }
}
