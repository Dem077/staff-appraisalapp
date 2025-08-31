<?php

namespace App\Enum;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
enum HODFormassigneeType: string implements HasLabel , HasColor
{
    case Manager = 'manager';
    case CoWorker = 'co-worker';
    case Subordinate = 'subordinate';

    public function getLabel(): ?string
    {
        // return $this->name;

         return match ($this) {
            self::Manager => 'Manager',
            self::CoWorker => 'Co-Worker',
            self::Subordinate => 'Subordinate',
        };
    }


    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Manager => 'primary',
            self::CoWorker => 'warning',
            self::Subordinate => 'success',
        };
    }
}
