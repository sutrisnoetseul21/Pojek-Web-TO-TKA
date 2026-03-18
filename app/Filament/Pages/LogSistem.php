<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class LogSistem extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';
    protected static ?string $navigationLabel = 'Log Sistem';
    protected static ?string $title = 'Log Sistem';
    protected static ?string $navigationGroup = 'LOG';

    protected static string $view = 'filament.pages.log-sistem';

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user->hasRole('super_admin');
    }
}
