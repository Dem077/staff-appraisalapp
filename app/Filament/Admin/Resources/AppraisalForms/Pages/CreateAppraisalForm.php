<?php

namespace App\Filament\Admin\Resources\AppraisalForms\Pages;

use App\Filament\Admin\Resources\AppraisalForms\AppraisalFormResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateAppraisalForm extends CreateRecord
{
    protected static string $resource = AppraisalFormResource::class;
}
