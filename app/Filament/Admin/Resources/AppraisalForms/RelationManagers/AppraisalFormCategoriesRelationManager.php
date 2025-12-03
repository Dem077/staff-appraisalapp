<?php

namespace App\Filament\Admin\Resources\AppraisalForms\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachBulkAction;
use App\Enum\AppraisalFormCategoryType;
use App\Enum\AppraisalFormLevel;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AppraisalFormCategoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'appraisalFormCategories';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Key Behavior Group'),
                TextColumn::make('appraisalFormKeyBehaviors.name')
                    ->listWithLineBreaks()
                    ->bulleted()
            ])
            ->filters([
                //
            ])
            ->headerActions([
                AttachAction::make()
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
                        }elseif ($formlevel == AppraisalFormLevel::Probationary->value){
                            $type = AppraisalFormCategoryType::FormProbationary->value;
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
            ->recordActions([
                DetachAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                ]),
            ]);
    }
}
