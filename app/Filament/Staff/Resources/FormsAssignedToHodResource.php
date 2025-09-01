<?php

namespace App\Filament\Staff\Resources;

use App\Enum\HODFormassigneeStatus;
use App\Filament\Staff\Resources\FormsAssignedToHodResource\Pages;
use App\Filament\Staff\Resources\FormsAssignedToHodResource\RelationManagers;
use App\Models\FormsAssignedToHod;
use App\Models\HodFormassignee;
use App\Models\Staff;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class FormsAssignedToHodResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = FormsAssignedToHod::class;

    protected static ?string $navigationGroup = 'Assign Appraisals';

    protected static ?string $navigationLabel = 'Management Team Leaders';

    protected static ?string $label = 'Appraisal Assignment Management Team Leaders';

    protected static ?string $navigationIcon = 'heroicon-o-users';



    public static function getPermissionPrefixes(): array
    {
        return [
            'view_any',
            'view_all',
            'view',
            'create',
            'update',
            'delete',
            'assign_hod_appraisal',
            'delete_any',
            'force_delete',
            'restore',
            'force_delete_any',
        ];
    }
     public static function getEloquentQuery(): Builder
     {
         if(Auth::user()->can('view_all', FormsAssignedToHod::class)){
             return parent::getEloquentQuery();
         }
         else{
             return parent::getEloquentQuery()
                 ->where('hod_id', Auth::user()->id)
                 ->orWhereHas('hodFormAssignees', function (Builder $query) {;
                     $query->where('assignee_id', Auth::user()->id);
                 });
        }
     }

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
                    ->disabledOn('edit')
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
                    ->label('Appraisal Form')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('staff.name')
                    ->label('HOD Name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('fill_hod')
                    ->label('Fill Form')
                    ->color('warning')
                    ->visible(fn($record) => $record->status === HODFormassigneeStatus::PendingStaff && $record->staff_id ===  auth('staff')->user()?->id)
                    ->url(fn($record) => route('hod-appraisal-form-fill', ['record' => $record]))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('fill_hod_assignee')
                    ->label('Fill Form')
                    ->color('primary')
                    ->visible(fn($record) => HodFormassignee::where('assignee_id',auth('staff')->user()->id)->where('forms_assigned_to_hod_id', $record->id)->where('status','pending_staff_appraisal')->first() && $record->status === HODFormassigneeStatus::PendingAssignee && $record->hodFormAssignees->contains('assignee_id',  auth('staff')->user()?->id))
                    ->url(fn($record) => route('assignee-hod-appraisal-form-fill', ['record' => $record]))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('results')
                    ->label('View Details')
                    ->button()
                    ->color('primary')
                    ->url(fn($record) => route('filament.staff.resources.forms-assigned-to-hods.results', ['record' => $record]))
                    ,
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
            RelationManagers\HodFormEntriesRelationManager::class,
            RelationManagers\HodFormAssigneesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'assignee-results' => Pages\ViewAssigneeResults::route('/{record}/assignee-results'),
            'results' => Pages\ViewResults::route('/{record}/results'),
            'index' => Pages\ListFormsAssignedToHods::route('/'),'appointments' => Pages\HodIndicators::route('/{record}/{hodname}/indicators'),
            'create' => Pages\CreateFormsAssignedToHod::route('/create'),
            'edit' => Pages\EditFormsAssignedToHod::route('/{record}/edit'),
        ];
    }
}
