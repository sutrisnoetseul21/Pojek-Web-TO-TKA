<?php

namespace App\Enums;

enum Jenjang: string
{
    case SD   = 'SD';
    case SMP  = 'SMP';
    case SMA  = 'SMA';
    case SMK  = 'SMK';
    case UMUM = 'UMUM';

    public function label(): string
    {
        return match ($this) {
            self::SD   => 'SD',
            self::SMP  => 'SMP',
            self::SMA  => 'SMA',
            self::SMK  => 'SMK',
            self::UMUM => 'Umum',
        };
    }
}
