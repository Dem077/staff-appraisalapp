<?php

namespace App\Filament\Admin\Resources\AppraisalFormKeyBehaviors\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Admin\Resources\AppraisalFormKeyBehaviors\AppraisalFormKeyBehaviorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAppraisalFormKeyBehaviors extends ListRecords
{
    protected static string $resource = AppraisalFormKeyBehaviorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
