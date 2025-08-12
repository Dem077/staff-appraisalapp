<?php

namespace App\Filament\Staff\Resources;

use App\Enum\AssignedFormStatus;
use App\Filament\Staff\Resources\AppraisalFormAssignedToStaffResource\Pages;
use App\Filament\Staff\Resources\AppraisalFormAssignedToStaffResource\RelationManagers;
use App\Models\AppraisalFormAssignedToStaff;
use App\Models\Staff;
use Filament\Forms;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Http;

class AppraisalFormAssignedToStaffResource extends Resource
{
    protected static ?string $model = AppraisalFormAssignedToStaff::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('assigned_date')
                    ->readOnly()
                    ->default(now())
                    ->required(),
                Forms\Components\Select::make('appraisal_form_id')
                    ->relationship('appraisalForm', 'name')
                    ->native(false)
                    ->required(),
                Forms\Components\Select::make('staff_id')
                    ->relationship('staff', 'name')
                    ->required()
                    ->searchable(['name', 'emp_no'])
                    ->afterStateUpdated(function (Forms\Set $set, $state) {
                        if ($state) {
                            $user = Staff::where('id', $state)->pluck('api_id')->first();
                            $supervisor = Http::withHeaders([
                                'X-API-KEY' => config('app.appkey'),
                                'Accept' => 'application/json',
                            ])->get(config('app.apiurl') . '/users/supervisor', [
                                    'id' => $user,
                                ]);
                                
                            $userid = Staff::where('api_id',  $supervisor->json()['id'])->pluck('id')->first();
                                $set('supervisor_id', $userid);
                        }
                    })
                    ->native(false)
                    ->preload()
                    ->reactive()
                    ->live()
                    ->getOptionLabelFromRecordUsing(fn (Model $record) => "{$record->name} ({$record->emp_no})"),
                Forms\Components\TextInput::make('supervisor_id')
                    ->required()
                    ->default(fn (Forms\Get $get) => $get('supervisor_id'))
                    ->reactive()
                    ->live()
                    ->readOnly(),
                // Forms\Components\Textarea::make('supervisor_comment')
                //     ->columnSpanFull(),
                // Forms\Components\Textarea::make('staff_comment')
                //     ->columnSpanFull(),
                // Forms\Components\Select::make('status')
                //     ->options(AssignedFormStatus::class)
                //     ->required()
                //     ->native(false)
                //     ->default(AssignedFormStatus::PendingStaff->value),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('assigned_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('appraisalForm.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('staff.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('supervisor.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
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
            RelationManagers\AppraisalFormEntriesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAppraisalFormAssignedToStaff::route('/'),
            'create' => Pages\CreateAppraisalFormAssignedToStaff::route('/create'),
            'edit' => Pages\EditAppraisalFormAssignedToStaff::route('/{record}/edit'),
        ];
    }
}
