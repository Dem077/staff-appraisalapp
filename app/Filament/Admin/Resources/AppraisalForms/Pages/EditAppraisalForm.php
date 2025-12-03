<?php

namespace App\Filament\Admin\Resources\AppraisalForms\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Admin\Resources\AppraisalForms\AppraisalFormResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAppraisalForm extends EditRecord
{
    protected static string $resource = AppraisalFormResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
