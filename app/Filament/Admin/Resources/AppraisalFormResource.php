<?php

namespace App\Filament\Admin\Resources;

use App\Enum\AppraisalFormLevel;
use App\Enum\AppraisalFormType;
use App\Filament\Admin\Resources\AppraisalFormResource\Pages;
use App\Filament\Admin\Resources\AppraisalFormResource\RelationManagers;
use App\Models\AppraisalForm;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AppraisalFormResource extends Resource
{
    protected static ?string $model = AppraisalForm::class;

    protected static ?string $navigationGroup = 'Appraisal';

    protected static ?string $navigationLabel = 'Appraisal Forms';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('type')
                    ->required()
                    ->native(false)
                    ->options(AppraisalFormType::class),
                Forms\Components\Toggle::make('is_active')
                    ->default(true),
                Forms\Components\Select::make('level')
                    ->required()
                    ->native(false)
                    ->options(AppraisalFormLevel::class),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type'),
                Tables\Columns\ToggleColumn::make('is_active'),
                Tables\Columns\TextColumn::make('level'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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
            RelationManagers\AppraisalFormCategoriesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAppraisalForms::route('/'),
            'create' => Pages\CreateAppraisalForm::route('/create'),
            'edit' => Pages\EditAppraisalForm::route('/{record}/edit'),
        ];
    }
}
