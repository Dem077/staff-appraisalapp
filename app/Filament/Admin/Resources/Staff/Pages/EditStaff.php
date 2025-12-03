<?php

namespace App\Filament\Admin\Resources\Staff\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Admin\Resources\Staff\StaffResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStaff extends EditRecord
{
    protected static string $resource = StaffResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
