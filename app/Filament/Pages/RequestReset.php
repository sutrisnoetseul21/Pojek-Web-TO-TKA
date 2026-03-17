<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class RequestReset extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-arrow-path-rounded-square';
    protected static ?string $navigationLabel = 'Request Reset Login';
    protected static ?string $title = 'Request Reset Login';
    protected static ?string $navigationGroup = 'Monitoring Ujian (Baru)';
    protected static ?int $navigationSort = 4;
    protected static string $view = 'filament.pages.request-reset';

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user->hasRole('super_admin') || $user->hasPermissionTo('manage_monitoring');
    }
}
