<?php

namespace App\Filament\Staff\Resources\FormsAssignedToHods;

use Filament\Schemas\Schema;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Staff\Resources\FormsAssignedToHods\RelationManagers\HodFormEntriesRelationManager;
use App\Filament\Staff\Resources\FormsAssignedToHods\RelationManagers\HodFormAssigneesRelationManager;
use App\Filament\Staff\Resources\FormsAssignedToHods\Pages\ViewAssigneeResults;
use App\Filament\Staff\Resources\FormsAssignedToHods\Pages\ViewResults;
use App\Filament\Staff\Resources\FormsAssignedToHods\Pages\ListFormsAssignedToHods;
use App\Filament\Staff\Resources\FormsAssignedToHods\Pages\HodIndicators;
use App\Filament\Staff\Resources\FormsAssignedToHods\Pages\CreateFormsAssignedToHod;
use App\Filament\Staff\Resources\FormsAssignedToHods\Pages\EditFormsAssignedToHod;
use App\Enum\AppraisalFormLevel;
use App\Enum\HODFormassigneeStatus;
use App\Filament\Staff\Resources\FormsAssignedToHodResource\Pages;
use App\Filament\Staff\Resources\FormsAssignedToHodResource\RelationManagers;
use App\Models\FormsAssignedToHod;
use App\Models\HodFormassignee;
use App\Models\Staff;
use App\Services\Shortcuts;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
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

    protected static string | \UnitEnum | null $navigationGroup = 'Assign Appraisals';

    protected static ?string $navigationLabel = 'Management Team Leaders';

    protected static ?string $label = 'Appraisal Assignment Management Team Leaders';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-users';



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
         if(Auth::user()->can('view_all_forms::assigned::to::hod')){
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

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('assigned_date')
                    ->readOnly()
                    ->default(now())
                    ->required(),
                Select::make('appraisal_form_id')
                    ->relationship('appraisalForm', 'name' , fn (Builder $query) => $query->where('level', AppraisalFormLevel::Level3->value))
                    ->native(false)
                    ->disabledOn('edit')
                    ->required(),
                Select::make('staff_id')
                    ->relationship('staff', 'name')
                    ->required()
                    ->searchable(['name', 'emp_no'])
                    ->native(false)
                    ->preload()
                    ->reactive()
                    ->live()
                    ->getOptionLabelFromRecordUsing(fn (Model $record) => "{$record->name} ({$record->emp_no})"),
                Select::make('supervisor_id')
                    ->relationship('supervisor', 'name')
                    ->required()
                    ->reactive()
                    ->live(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('assigned_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('appraisalForm.name')
                    ->label('Appraisal Form')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('staff.name')
                    ->label('HOD Name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('supervisor.name')
                    ->label('Supervisor Name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('fill_hod')
                    ->label('Fill Form')
                    ->color('warning')
                    ->visible(fn($record) => $record->status === HODFormassigneeStatus::PendingStaff && $record->hod_id ===  auth('staff')->user()?->id)
                    ->url(fn($record) => route('hod-appraisal-form-fill', ['record' => $record]))
                    ->openUrlInNewTab(),
                Action::make('fill_hod_assignee')
                    ->label('Fill Form')
                    ->color('primary')
                    ->visible(fn ($record) => $record->hodFormAssignees->where('assignee_id',auth('staff')->user()->id)->where('status','pending_staff_appraisal')->first() && $record->status === HODFormassigneeStatus::PendingAssignee && $record->hodFormAssignees->contains('assignee_id',  auth('staff')->user()?->id))
                    ->url(fn($record) => route('assignee-hod-appraisal-form-fill', ['record' => $record]))
                    ->openUrlInNewTab(),
                Action::make('results')
                    ->label('View Details')
                    ->button()
                    ->visible(fn ($record) => ($record->status === HODFormassigneeStatus::Completed || $record->status === HODFormassigneeStatus::PendingAssignee || $record->status === HODFormassigneeStatus::HRComment) && ($record->hod_id ===  auth('staff')->user()?->id ||  $record->supervisor_id ===  auth('staff')->user()?->id || Auth::user()->can('view_all_forms::assigned::to::hod') || in_array('HR', Shortcuts::callgetapi('/user/roles', ['id' => auth('staff')->user()->api_id])->json() ?? [])))
                    ->color('primary')
                    ->url(fn($record) => route('filament.staff.resources.forms-assigned-to-hods.results', ['record' => $record]))
                    ,
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
            HodFormEntriesRelationManager::class,
            HodFormAssigneesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'assignee-results' => ViewAssigneeResults::route('/{record}/assignee-results'),
            'results' => ViewResults::route('/{record}/results'),
            'index' => ListFormsAssignedToHods::route('/'),'appointments' => HodIndicators::route('/{record}/{hodname}/indicators'),
            'create' => CreateFormsAssignedToHod::route('/create'),
            'edit' => EditFormsAssignedToHod::route('/{record}/edit'),
        ];
    }
}
