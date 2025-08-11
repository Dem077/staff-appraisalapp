<?php

namespace App\Filament\Admin\Resources\AppraisalFormResource\Pages;

use App\Filament\Admin\Resources\AppraisalFormResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAppraisalForms extends ListRecords
{
    protected static string $resource = AppraisalFormResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
