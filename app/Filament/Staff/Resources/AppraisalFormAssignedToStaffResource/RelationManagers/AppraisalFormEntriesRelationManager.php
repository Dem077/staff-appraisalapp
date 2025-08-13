<?php

namespace App\Filament\Staff\Resources\AppraisalFormAssignedToStaffResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Form;
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

    public function form(Form $form): Form
    {
        return $form
            ->schema([
               Forms\Components\TextInput::make('question_id')
                   ->required()
                   ->disabled()
                   ->maxLength(255),
               Forms\Components\Select::make('appraisal_assigned_to_staff_id')
                    ->relationship('appraisalFormAssignedToStaff', 'id')
                   ->required(),
               Forms\Components\TextInput::make('staff_score')
                   ->required()
                   ->maxLength(255),
               Forms\Components\TextInput::make('supervisor_score')
                   ->required()
                   ->maxLength(255),
               Forms\Components\Radio::make('hidden')
                   ->required()
                   ->boolean(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('id'),
                Tables\Columns\TextColumn::make('question.appraisalFormKeyBehavior.name')
                    ->label('Key Behavior Category'),
                Tables\Columns\TextColumn::make('question.behavioral_indicators')
                    ->label('Behavioral Indicators')
                    ->wrap(),
                Tables\Columns\ToggleColumn::make('hidden')
                    ->label('Is Not Applicable'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\Action::make('sync')
                    ->label('Sync Indicators')
                    ->action(function ($livewire) {
                        $record = $livewire->getOwnerRecord(); // The AppraisalFormAssignedToStaff model

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
                            \App\Models\AppraisalFormEntries::updateOrCreate(
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
            ->actions([
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('hide_all')
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
