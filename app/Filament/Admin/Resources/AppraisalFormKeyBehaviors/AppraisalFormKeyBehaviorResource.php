<?php

namespace App\Filament\Admin\Resources\AppraisalFormKeyBehaviors;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Admin\Resources\AppraisalFormKeyBehaviors\RelationManagers\AppraisalFormQuestionsRelationManager;
use App\Filament\Admin\Resources\AppraisalFormKeyBehaviors\Pages\ListAppraisalFormKeyBehaviors;
use App\Filament\Admin\Resources\AppraisalFormKeyBehaviors\Pages\CreateAppraisalFormKeyBehavior;
use App\Filament\Admin\Resources\AppraisalFormKeyBehaviors\Pages\EditAppraisalFormKeyBehavior;
use App\Enum\AppraisalFormCategoryType;
use App\Filament\Admin\Resources\AppraisalFormKeyBehaviors\Pages;
use App\Filament\Admin\Resources\AppraisalFormKeyBehaviors\RelationManagers;
use App\Forms\Components\ThaanaInput;
use App\Models\AppraisalFormCategory;
use App\Models\AppraisalFormKeyBehavior;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AppraisalFormKeyBehaviorResource extends Resource
{
    protected static ?string $model = AppraisalFormKeyBehavior::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string | \UnitEnum | null $navigationGroup = 'Appraisal';

    protected static ?string $navigationLabel = 'Key Behaviors';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('appraisal_form_category_id')
                    ->label('Key Behavior Group')
                    ->relationship('appraisalFormCategory', 'name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->name} ( {$record->type->getLabel()} )")
                    ->required()
                    ->searchable(['name', 'type'])
                    ->preload()
                    ->reactive()
                    ->default(fn () => AppraisalFormCategory::latest('created_at')->value('id'))
                    ->live()
                    ->native(false)
                    ->createOptionForm([
                        TextInput::make('name')
                            ->required(),
                        Select::make('type')
                            ->label('For Form Level')
                            ->native(false)
                            ->options(AppraisalFormCategoryType::class)
                    ])
                    ->editOptionForm([
                        TextInput::make('name')
                            ->required(),
                        Select::make('type')
                            ->label('For Form Level')
                            ->native(false)
                            ->options(AppraisalFormCategoryType::class)
                    ])
                ,
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Repeater::make('appraisalFormQuestions')
                    ->label('Behavioral Indicators (Questions)')
                    ->relationship()
                    ->columnSpanFull()
                    ->hiddenOn('edit')
                    ->schema([
                        TextInput::make('behavioral_indicators')
                            ->required()
                            ->maxLength(255),
                        ThaanaInput::make('dhivehi_behavioral_indicators')
                            ->label('Name (Thaana)')
                            ->extraAttributes(['style' => 'direction: rtl; font-family: Faruma ']),
                    ])
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('appraisalFormCategory.type')
                    ->badge(),
                TextColumn::make('quest_count')
                    ->label('Number of Questions'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->groups([
                Group::make('appraisalFormCategory.name')
                    ->label(''),
            ])
            ->defaultGroup('appraisalFormCategory.name')
            ->filters([
                SelectFilter::make('form_level')
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
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            AppraisalFormQuestionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAppraisalFormKeyBehaviors::route('/'),
            'create' => CreateAppraisalFormKeyBehavior::route('/create'),
            'edit' => EditAppraisalFormKeyBehavior::route('/{record}/edit'),
        ];
    }
}
