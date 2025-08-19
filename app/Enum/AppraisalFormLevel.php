<?php

namespace App\Enum;

use Filament\Support\Contracts\HasLabel;
enum AppraisalFormLevel: string implements HasLabel
{
    case Level1 = 'level_1';
    case Level2 = 'level_2';
    case Level3 = 'level_3';

    public function getLabel(): ?string
    {
        // return $this->name;

         return match ($this) {
            self::Level1 => 'Level 1',
            self::Level2 => 'Level 2',
            self::Level3 => 'Level 3',
        };
    }
}