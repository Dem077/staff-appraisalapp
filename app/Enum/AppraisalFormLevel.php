<?php

namespace App\Enum;

enum AppraisalFormLevel: string
{
    case Level1 = 'level_1';
    case Level2 = 'level_2';
    case Level3 = 'level_3';
    case Probationary = 'probationary';

    public function getLabel(): ?string
    {
        // return $this->name;

         return match ($this) {
            self::Level1 => 'Level 1',
            self::Level2 => 'Level 2',
            self::Level3 => 'Level 3',
            self::Probationary => 'Probationary',
        };
    }
}
