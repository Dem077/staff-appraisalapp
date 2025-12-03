<?php

namespace App\Filament\Staff\Resources\Reports\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Staff\Resources\Reports\ReportsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditReports extends EditRecord
{
    protected static string $resource = ReportsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
