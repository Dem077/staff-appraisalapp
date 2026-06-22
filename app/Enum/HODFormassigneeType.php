<?php

namespace App\Enum;

enum HODFormassigneeType: string
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
