<?php

namespace App\Enum;

use Filament\Support\Contracts\HasLabel;
enum AppraisalFormType: string implements HasLabel
{
    case MidYear = 'mid-year';
    case YearEnd = 'year-end';

    public function getLabel(): ?string
    {
        // return $this->name;

         return match ($this) {
            self::MidYear => 'Mid Year',
            self::YearEnd => 'Year End',
        };
    }
}