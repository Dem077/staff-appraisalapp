<?php

namespace App\Filament\Staff\Resources\FormsAssignedToHodResource\RelationManagers;

use App\Models\Staff;
use Filament\Actions\CreateAction;
use Filament\Forms;
use Filament\Forms\Components\Actions;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use GuzzleHttp\Promise\Create;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class HodFormAssigneesRelationManager extends RelationManager
{
    protected static string $relationship = 'hodFormAssignees';

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
                Tables\Columns\TextColumn::make('staff.name'),
                Tables\Columns\TextColumn::make('assignee_type')
                    ->badge(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // CreateAction::make()
                //     ->label('Add Assignee')
                //     ->icon('heroicon-o-plus')
                //     ->form([
                //         Forms\Components\Select::make('staff_id')
                //             ->label('Staff')
                //             ->relationship('staff', 'name')
                //             ->required(),
                //         Forms\Components\Select::make('assignee_type')
                //             ->label('Assignee Type')
                //             ->options([
                //                 'manager' => 'Manager',
                //                 'co-worker' => 'Co-Worker',
                //                 'subordinate' => 'Subordinate',
                //             ])
                //             ->required(),
                //     ])
                //     ->action(function (array $data) {
                //         $this->ownerRecord->hodFormAssignees()->create($data);
                //         Notification::make()
                //             ->title('Assignee Added Successfully')
                //             ->success()
                //             ->send();
                //     }),
            ])
            ->actions([
                Action::make('view_indicators')
                    ->label('View Indicators')
                    ->icon('heroicon-o-eye')
                    ->button()
                    ->url(fn ($record) => route('filament.staff.resources.forms-assigned-to-hods.appointments', ['record' => $record->id , 'hodname' =>Staff::find($record->formsAssignedToHod->hod_id)->name ?? '']))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
            ]);
    }
}
