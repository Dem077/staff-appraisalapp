<?php

namespace App\Filament\Admin\Resources\AppraisalForms\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Admin\Resources\AppraisalForms\AppraisalFormResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAppraisalForms extends ListRecords
{
    protected static string $resource = AppraisalFormResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
