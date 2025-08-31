<?php

namespace App\Filament\Staff\Resources\FormsAssignedToHodResource\RelationManagers;

use App\Models\FormsAssignedToHod;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class HodFormEntriesRelationManager extends RelationManager
{
    protected static string $relationship = 'hodFormEntries';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
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
                        $record = FormsAssignedToHod::where('id', $livewire->getOwnerRecord()->id)->first(); // The AppraisalFormAssignedToStaff model
                        // dd($record);
                        // Get all question IDs for this assigned form
                        $questionIds = \App\Models\AppraisalFormQuestions::whereIn(
                            'appraisal_form_key_behavior_id',
                            \App\Models\AppraisalFormKeyBehavior::whereIn(
                                'appraisal_form_category_id',
                                $record->appraisalForm->appraisalFormCategories->pluck('id')
                            )->pluck('id')
                        )->pluck('id');

                        // Sync entries using Create
                        foreach ($questionIds as $questionId) {
                            if (!\App\Models\HodFormEntries::where('forms_assigned_to_hod_id', $record->id)
                                ->where('question_id', $questionId)
                                ->exists()) {
                                \App\Models\HodFormEntries::Create(
                                    [
                                        'forms_assigned_to_hod_id' => $record->id,
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
