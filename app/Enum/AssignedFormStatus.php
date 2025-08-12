<?php

namespace App\Enum;

use Filament\Support\Contracts\HasLabel;
enum AssignedFormStatus: string implements HasLabel
{
    case PendingStaff = 'pending_staff_appraisal';
    case PendingSupervisor = 'pending_supervisor_appraisal';
    case Complete = 'complete';

    public function getLabel(): ?string
    {
        // return $this->name;

         return match ($this) {
            self::PendingStaff => 'Pending Staff Appraisal',
            self::PendingSupervisor => 'Pending Supervisor Appraisal',
            self::Complete => 'Complete',
        };
    }
}