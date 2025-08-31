<?php

namespace App\Enum;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
enum HODFormassigneeStatus: string implements HasLabel , HasColor
{
    case PendingStaff = 'pending_staff_appraisal';
    case PendingAssignee = 'pending_assignee_appraisal';
    case Completed = 'complete';

    public function getLabel(): ?string
    {
        // return $this->name;

         return match ($this) {
            self::PendingStaff => 'Pending Staff',
            self::PendingAssignee => 'Pending Assignee',
            self::Completed => 'Completed',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::PendingStaff => 'gray',
            self::PendingAssignee => 'warning',
            self::Completed => 'success',
        };
    }
}
