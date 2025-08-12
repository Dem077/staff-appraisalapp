<?php

namespace App\Filament\Staff\Resources\AppraisalFormAssignedToStaffResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
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
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
