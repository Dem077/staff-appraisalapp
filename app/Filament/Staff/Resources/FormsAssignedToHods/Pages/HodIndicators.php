<?php

namespace App\Filament\Staff\Resources\FormsAssignedToHods\Pages;

use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Actions\Action;
use App\Models\AppraisalFormQuestions;
use App\Models\AppraisalFormKeyBehavior;
use App\Filament\Staff\Resources\FormsAssignedToHods\FormsAssignedToHodResource;
use App\Models\FormsAssignedToHod;
use App\Models\HodFormassignee;
use App\Models\HodFormAssigneeEntry;
use App\Models\Staff;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Table;
use Filament\Resources\Pages\Page;
use Illuminate\Database\Eloquent\Builder;

class HodIndicators extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = FormsAssignedToHodResource::class;

    protected static ?string $title = 'Questions';

    public $record;

    public $hodname;

    protected string $view = 'filament.staff.resources.forms-assigned-to-hod-resource.pages.hod-indicators';

    public function getSubheading(): string
    {
        return $this->hodname . ' form assigned to ' . Staff::find(HodFormassignee::find($this->record)?->assignee_id)?->name;
    }
    protected function getTableQuery(): Builder
    {
        // Query the HodFormAssigneeEntry model and filter by doctor_roster_id
        return HodFormAssigneeEntry::query()
            ->where('hod_form_assignee_id', $this->record)
            ->with('question');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('id'),
                TextColumn::make('question.appraisalFormKeyBehavior.name')
                    ->label('Key Behavior Category'),
                TextColumn::make('question.behavioral_indicators')
                    ->label('Behavioral Indicators')
                    ->wrap(),
                TextColumn::make('question.dhivehi_behavioral_indicators')
                    ->label('Behavioral indicators (ދިވެހި)')
                    ->wrap()
                    ->extraAttributes(['style' => 'direction: rtl; font-family: Faruma ']),
                ToggleColumn::make('hidden')
                    ->label('Is Not Applicable'),
            ])
            ->headerActions([
            Action::make('sync')
                    ->label('Sync Indicators')
                    ->action(function ($livewire) {
                        $record = FormsAssignedToHod::where('id', HodFormassignee::find($this->record)?->forms_assigned_to_hod_id)->first(); // The AppraisalFormAssignedToStaff model

                        // Get all question IDs for this assigned form
                        $questionIds = AppraisalFormQuestions::whereIn(
                            'appraisal_form_key_behavior_id',
                            AppraisalFormKeyBehavior::whereIn(
                                'appraisal_form_category_id',
                                $record->appraisalForm->appraisalFormCategories->pluck('id')
                            )->pluck('id')
                        )->pluck('id');

                        // Sync entries using updateOrCreate
                        foreach ($questionIds as $questionId) {
                            if (!HodFormAssigneeEntry::where('hod_form_assignee_id', $record->id)
                                ->where('question_id', $questionId)
                                ->exists()) {
                                HodFormAssigneeEntry::Create(
                                    [
                                        'hod_form_assignee_id' => $this->record,
                                        'question_id' => $questionId,
                                    ],
                                );
                            }
                        }
                        Notification::make()
                            ->title('Success')
                            ->body('Indicators synced successfully!')
                            ->success()
                            ->send();
                    })
                    ->color('success'),

            ])
            ->recordActions([
            ])
            ->toolbarActions([
            ]);
    }

}
