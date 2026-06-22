<?php

namespace App\Enum;

enum HODFormassigneeStatus: string
{
    case PendingStaff = 'pending_staff_appraisal';
    case PendingAssignee = 'pending_assignee_appraisal';
    case HRComment = 'hr_comment';
    case Completed = 'complete';

    public function getLabel(): ?string
    {
        // return $this->name;

         return match ($this) {
            self::PendingStaff => 'Pending Staff',
            self::PendingAssignee => 'Pending Assignee',
             self::HRComment => 'HR Comment',
            self::Completed => 'Completed',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::PendingStaff => 'gray',
            self::PendingAssignee => 'warning',
            self::HRComment => 'primary',
            self::Completed => 'success',
        };
    }
}
