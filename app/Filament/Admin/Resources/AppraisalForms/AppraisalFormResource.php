<?php

namespace App\Filament\Admin\Resources\AppraisalForms;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Admin\Resources\AppraisalForms\RelationManagers\AppraisalFormCategoriesRelationManager;
use App\Filament\Admin\Resources\AppraisalForms\Pages\ListAppraisalForms;
use App\Filament\Admin\Resources\AppraisalForms\Pages\CreateAppraisalForm;
use App\Filament\Admin\Resources\AppraisalForms\Pages\EditAppraisalForm;
use App\Enum\AppraisalFormLevel;
use App\Enum\AppraisalFormType;
use App\Filament\Admin\Resources\AppraisalForms\Pages;
use App\Filament\Admin\Resources\AppraisalForms\RelationManagers;
use App\Forms\Components\ThaanaInput;
use App\Models\AppraisalForm;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use function Termwind\style;

class AppraisalFormResource extends Resource
{
    protected static ?string $model = AppraisalForm::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Appraisal';

    protected static ?string $navigationLabel = 'Appraisal Forms';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
//                Forms\Components\TextInput::make('name')
//                    ->required()
//                    ->maxLength(255),
                Select::make('type')
                    ->required()
                    ->native(false)
                    ->options(AppraisalFormType::class),
                ThaanaInput::make('name')
                    ->label('Name (Thaana)')
                    ->extraAttributes(['style' => 'direction: rtl; unicode-bidi: bidi-override; '])
                    ->required(),
                Toggle::make('is_active')
                    ->default(true),
                Select::make('level')
                    ->required()
                    ->native(false)
                    ->options(AppraisalFormLevel::class),
                Textarea::make('description')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('type'),
                ToggleColumn::make('is_active'),
                TextColumn::make('level'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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
            AppraisalFormCategoriesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAppraisalForms::route('/'),
            'create' => CreateAppraisalForm::route('/create'),
            'edit' => EditAppraisalForm::route('/{record}/edit'),
        ];
    }
}
