<?php

namespace App\Filament\Admin\Resources\AppraisalFormKeyBehaviors\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Admin\Resources\AppraisalFormKeyBehaviors\AppraisalFormKeyBehaviorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAppraisalFormKeyBehavior extends EditRecord
{
    protected static string $resource = AppraisalFormKeyBehaviorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
