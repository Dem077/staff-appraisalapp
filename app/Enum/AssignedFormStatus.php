<?php

namespace App\Enum;

use App\Services\Shortcuts;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
enum AssignedFormStatus: string implements HasLabel , HasColor
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
    public function getColor(): string | array | null
    {
        return match ($this) {
            self::PendingStaff => 'gray',
            self::PendingSupervisor => 'warning',
            self::Complete => 'success',
        };
    }
}
