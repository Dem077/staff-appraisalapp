<?php

namespace App\Filament\Staff\Resources\FormsAssignedToHodResource\Pages;

use App\Filament\Staff\Resources\FormsAssignedToHodResource;
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

class HodIndicators extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static string $resource = FormsAssignedToHodResource::class;

    protected static ?string $title = 'Questions';

    public $record;

    public $hodname;

    protected static string $view = 'filament.staff.resources.forms-assigned-to-hod-resource.pages.hod-indicators';

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
                Tables\Columns\TextColumn::make('id'),
                Tables\Columns\TextColumn::make('question.appraisalFormKeyBehavior.name')
                    ->label('Key Behavior Category'),
                Tables\Columns\TextColumn::make('question.behavioral_indicators')
                    ->label('Behavioral Indicators')
                    ->wrap(),
                Tables\Columns\TextColumn::make('question.dhivehi_behavioral_indicators')
                    ->label('Behavioral indicators (ދިވެހި)')
                    ->wrap()
                    ->extraAttributes(['style' => 'direction: rtl; font-family: Faruma ']),
                Tables\Columns\ToggleColumn::make('hidden')
                    ->label('Is Not Applicable'),
            ])
            ->headerActions([
            Tables\Actions\Action::make('sync')
                    ->label('Sync Indicators')
                    ->action(function ($livewire) {
                        $record = FormsAssignedToHod::where('id', HodFormassignee::find($this->record)?->forms_assigned_to_hod_id)->first(); // The AppraisalFormAssignedToStaff model

                        // Get all question IDs for this assigned form
                        $questionIds = \App\Models\AppraisalFormQuestions::whereIn(
                            'appraisal_form_key_behavior_id',
                            \App\Models\AppraisalFormKeyBehavior::whereIn(
                                'appraisal_form_category_id',
                                $record->appraisalForm->appraisalFormCategories->pluck('id')
                            )->pluck('id')
                        )->pluck('id');

                        // Sync entries using updateOrCreate
                        foreach ($questionIds as $questionId) {
                            if (!\App\Models\HodFormAssigneeEntry::where('hod_form_assignee_id', $record->id)
                                ->where('question_id', $questionId)
                                ->exists()) {
                                \App\Models\HodFormAssigneeEntry::Create(
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
            ->actions([
            ])
            ->bulkActions([
            ]);
    }

}
