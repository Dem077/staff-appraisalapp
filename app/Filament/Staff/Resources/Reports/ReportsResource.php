<?php

namespace App\Filament\Staff\Resources\Reports;

use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Staff\Resources\Reports\Pages\ListReports;
use App\Filament\Staff\Resources\Reports\Pages\CreateReports;
use App\Filament\Staff\Resources\Reports\Pages\EditReports;
use App\Filament\Staff\Resources\ReportsResource\Pages;
use App\Filament\Staff\Resources\ReportsResource\RelationManagers;
use App\Filament\Staff\Widgets\KeyBehaviorEntriesChart;
use App\Models\AppraisalFormEntries;
use App\Models\Reports;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ReportsResource extends Resource
{
    protected static ?string $model = AppraisalFormEntries::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';


//    public static function getWidgets(): array
//    {
//        return [
//            KeyBehaviorEntriesChart::class,
//        ];
//    }
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReports::route('/'),
            'create' => CreateReports::route('/create'),
            'edit' => EditReports::route('/{record}/edit'),
        ];
    }
}
