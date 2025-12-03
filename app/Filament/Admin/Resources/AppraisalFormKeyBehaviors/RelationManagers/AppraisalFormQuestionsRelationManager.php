<?php

namespace App\Filament\Admin\Resources\AppraisalFormKeyBehaviors\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\Layout\Split;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Forms\Components\ThaanaInput;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AppraisalFormQuestionsRelationManager extends RelationManager
{
    protected static string $relationship = 'appraisalFormQuestions';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('behavioral_indicators')
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
                TextColumn::make('behavioral_indicators'),
                Split::make([

                    TextColumn::make('dhivehi_behavioral_indicators')
                        ->label('Behavioral indicators (ދިވެހި)')
                        ->extraAttributes(['style' => 'direction: rtl; font-family: Faruma ']),
                ]),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
