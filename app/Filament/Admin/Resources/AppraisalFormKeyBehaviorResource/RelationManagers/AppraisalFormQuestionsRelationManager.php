<?php

namespace App\Filament\Admin\Resources\AppraisalFormKeyBehaviorResource\RelationManagers;

use App\Forms\Components\ThaanaInput;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AppraisalFormQuestionsRelationManager extends RelationManager
{
    protected static string $relationship = 'appraisalFormQuestions';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('behavioral_indicators')
                    ->required()
                    ->columnSpanFull()
                    ->maxLength(255),
                ThaanaInput::make('dhivehi_behavioral_indicators')
                    ->label('Name (Thaana)')
                    ->columnSpanFull(),

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('behavioral_indicators'),
                Tables\Columns\Layout\Split::make([

                    Tables\Columns\TextColumn::make('dhivehi_behavioral_indicators')
                        ->label('Behavioral indicators (ދިވެހި)')
                        ->extraAttributes(['style' => 'direction: rtl; font-family: Faruma ']),
                ]),
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
