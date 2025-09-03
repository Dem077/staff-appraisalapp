<?php

namespace App\Enum;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
enum AppraisalFormCategoryType: string implements HasLabel, HasColor
{
    case FormLevel1 = 'form_level_1';
    case FormLevel2 = 'form_level_2';
    case FormLevel3 = 'form_level_3';

    public function getLabel(): ?string
    {
        // return $this->name;

         return match ($this) {
            self::FormLevel1 => 'Form Level 1',
            self::FormLevel2 => 'Form Level 2',
            self::FormLevel3 => 'Form Level 3',

        };
    }

    public function getColor(): ?string
    {
        return match ($this) {
            self::FormLevel1 => 'primary',
            self::FormLevel2 => 'secondary',
            self::FormLevel3 => 'success',
        };
    }
}
