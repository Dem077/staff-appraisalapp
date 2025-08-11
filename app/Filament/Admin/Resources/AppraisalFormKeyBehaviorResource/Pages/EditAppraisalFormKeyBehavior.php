<?php

namespace App\Filament\Admin\Resources\AppraisalFormKeyBehaviorResource\Pages;

use App\Filament\Admin\Resources\AppraisalFormKeyBehaviorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAppraisalFormKeyBehavior extends EditRecord
{
    protected static string $resource = AppraisalFormKeyBehaviorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
