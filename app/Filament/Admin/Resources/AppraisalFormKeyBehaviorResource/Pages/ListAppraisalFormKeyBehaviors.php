<?php

namespace App\Filament\Admin\Resources\AppraisalFormKeyBehaviorResource\Pages;

use App\Filament\Admin\Resources\AppraisalFormKeyBehaviorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAppraisalFormKeyBehaviors extends ListRecords
{
    protected static string $resource = AppraisalFormKeyBehaviorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
