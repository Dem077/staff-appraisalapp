<?php

namespace App\Enum;

enum AppraisalFormType: string
{
    case MidYear = 'mid-year';
    case YearEnd = 'year-end';
    case Probationary = 'probationary';

    public function getLabel(): ?string
    {
        // return $this->name;

         return match ($this) {
            self::MidYear => 'Mid Year',
            self::YearEnd => 'Year End',
             self::Probationary => 'Probationary',
        };
    }
}
