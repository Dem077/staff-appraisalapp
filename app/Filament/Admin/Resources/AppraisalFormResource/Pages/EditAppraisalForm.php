<?php

namespace App\Filament\Admin\Resources\AppraisalFormResource\Pages;

use App\Filament\Admin\Resources\AppraisalFormResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAppraisalForm extends EditRecord
{
    protected static string $resource = AppraisalFormResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
