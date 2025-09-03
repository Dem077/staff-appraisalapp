<?php

namespace App\Filament\Admin\Resources;

use App\Enum\AppraisalFormCategoryType;
use App\Filament\Admin\Resources\AppraisalFormKeyBehaviorResource\Pages;
use App\Filament\Admin\Resources\AppraisalFormKeyBehaviorResource\RelationManagers;
use App\Models\AppraisalFormKeyBehavior;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AppraisalFormKeyBehaviorResource extends Resource
{
    protected static ?string $model = AppraisalFormKeyBehavior::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Appraisal';

    protected static ?string $navigationLabel = 'Key Behaviors';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('appraisal_form_category_id')
                    ->label('Key Behavior Group')
                    ->relationship('appraisalFormCategory', 'name')
                    ->required()
                    ->reactive()
                    ->live()
                    ->native(false)
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->required(),
                        Forms\Components\Select::make('type')
                            ->label('For Form Level')
                            ->native(false)
                            ->options(AppraisalFormCategoryType::class)
                    ])
                    ->editOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->required(),
                        Forms\Components\Select::make('type')
                            ->label('For Form Level')
                            ->native(false)
                            ->options(AppraisalFormCategoryType::class)
                    ])
                ,
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Repeater::make('appraisalFormQuestions')
                    ->label('Behavioral Indicators (Questions)')
                    ->relationship()
                    ->columnSpanFull()
                    ->hiddenOn('edit')
                    ->simple(
                        Forms\Components\TextInput::make('behavioral_indicators')
                            ->required()
                            ->maxLength(255),
                    )
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('appraisalFormCategory.type')
                    ->badge(),
                Tables\Columns\TextColumn::make('quest_count')
                    ->label('Number of Questions'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->groups([
                Tables\Grouping\Group::make('appraisalFormCategory.name')
                    ->label(''),
            ])
            ->defaultGroup('appraisalFormCategory.name')
            ->filters([
                Tables\Filters\SelectFilter::make('form_level')
                    ->label('Form Level')
                    ->options(
                        collect(AppraisalFormCategoryType::cases())
                            ->mapWithKeys(fn($case) => [$case->value => $case->getLabel()])
                            ->toArray()
                    )
                    ->placeholder('All')
                    ->query(function (Builder $query, array $data): Builder {
                        return $data['value']
                            ?  $query->whereHas('appraisalFormCategory', function ($q) use ($data) {
                                $q->where('type', $data);
                            })
                            : $query;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\AppraisalFormQuestionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAppraisalFormKeyBehaviors::route('/'),
            'create' => Pages\CreateAppraisalFormKeyBehavior::route('/create'),
            'edit' => Pages\EditAppraisalFormKeyBehavior::route('/{record}/edit'),
        ];
    }
}
