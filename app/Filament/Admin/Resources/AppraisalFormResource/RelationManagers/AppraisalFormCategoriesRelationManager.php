<?php

namespace App\Filament\Admin\Resources\AppraisalFormResource\RelationManagers;

use App\Enum\AppraisalFormCategoryType;
use App\Enum\AppraisalFormLevel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AppraisalFormCategoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'appraisalFormCategories';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Key Behavior Group'),
                Tables\Columns\TextColumn::make('appraisalFormKeyBehaviors.name')
                    ->listWithLineBreaks()
                    ->bulleted()
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->preloadRecordSelect()
                    ->multiple()
                    ->modelLabel('Key Behavior Group')
                    ->recordSelectOptionsQuery(function (Builder $query) {

                        $formlevel = $this->getOwnerRecord()->level;
                        if( $formlevel == AppraisalFormLevel::Level1->value){
                            $type = AppraisalFormCategoryType::FormLevel1->value;
                        }elseif ($formlevel == AppraisalFormLevel::Level2->value){
                            $type = AppraisalFormCategoryType::FormLevel2->value;
                        }elseif ($formlevel == AppraisalFormLevel::Level3->value){
                            $type = AppraisalFormCategoryType::FormLevel3->value;
                        }else{
                            $type = "";
                        }
                        return $query->where('appraisal_form_categories.type', $type)
                            ->select([
                                'appraisal_form_categories.id',
                                'appraisal_form_categories.name',
                                'appraisal_form_categories.type',
                            ]);
                    }),
            ])
            ->actions([
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}
