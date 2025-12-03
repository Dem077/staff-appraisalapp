<?php

namespace App\Filament\Staff\Resources\AppraisalFormAssignedToStaffs\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Radio;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Actions\Action;
use App\Models\AppraisalFormQuestions;
use App\Models\AppraisalFormKeyBehavior;
use App\Models\AppraisalFormEntries;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\BulkAction;
use Filament\Forms;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AppraisalFormEntriesRelationManager extends RelationManager
{
    protected static string $relationship = 'appraisalFormEntries';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
               TextInput::make('question_id')
                   ->required()
                   ->disabled()
                   ->maxLength(255),
               Select::make('appraisal_assigned_to_staff_id')
                    ->relationship('appraisalFormAssignedToStaff', 'id')
                   ->required(),
               TextInput::make('staff_score')
                   ->required()
                   ->maxLength(255),
               TextInput::make('supervisor_score')
                   ->required()
                   ->maxLength(255),
               Radio::make('hidden')
                   ->required()
                   ->boolean(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
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
            ->filters([
                //
            ])
            ->headerActions([
                Action::make('sync')
                    ->label('Sync Indicators')
                    ->action(function ($livewire) {
                        $record = $livewire->getOwnerRecord(); // The AppraisalFormAssignedToStaff model

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
                            AppraisalFormEntries::updateOrCreate(
                                [
                                    'appraisal_assigned_to_staff_id' => $record->id,
                                    'question_id' => $questionId,
                                ],
                                [
                                    'staff_score' => null,
                                    'supervisor_score' => null,
                                    'hidden' => false,
                                ]
                            );
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
                BulkActionGroup::make([
                    BulkAction::make('hide_all')
                    ->label('Mark/Unmark (N/A) ')
                    ->action(function (Collection $records) {

                        foreach ($records as $record) {
                            if ($record->hidden) {
                                $record->update(['hidden' => false]);
                            }else{
                                $record->update(['hidden' => true]);
                            }
                        }

                        Notification::make()
                            ->title('Success')
                            ->body('All selected entries marked Successfully.')
                            ->success()
                            ->send();
                    }),
                ]),
            ]);
    }
}
