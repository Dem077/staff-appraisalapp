<?php

namespace App\Filament\Staff\Resources\AppraisalFormAssignedToStaffs;

use Filament\Schemas\Schema;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Staff\Resources\AppraisalFormAssignedToStaffs\RelationManagers\AppraisalFormEntriesRelationManager;
use App\Filament\Staff\Resources\AppraisalFormAssignedToStaffs\Pages\ViewResults;
use App\Filament\Staff\Resources\AppraisalFormAssignedToStaffs\Pages\ListAppraisalFormAssignedToStaff;
use App\Filament\Staff\Resources\AppraisalFormAssignedToStaffs\Pages\CreateAppraisalFormAssignedToStaff;
use App\Filament\Staff\Resources\AppraisalFormAssignedToStaffs\Pages\EditAppraisalFormAssignedToStaff;
use App\Enum\AppraisalFormLevel;
use App\Enum\AssignedFormStatus;
use App\Filament\Staff\Resources\AppraisalFormAssignedToStaffResource\Pages;
use App\Filament\Staff\Resources\AppraisalFormAssignedToStaffResource\RelationManagers;
use App\Models\AppraisalFormAssignedToStaff;
use App\Models\FormsAssignedToHod;
use App\Models\Staff;
use App\Services\Shortcuts;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class AppraisalFormAssignedToStaffResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = AppraisalFormAssignedToStaff::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Assign Appraisals';

    protected static ?string $navigationLabel = 'Team Leaders / Members';

    protected static ?string $label = 'Appraisal Assignment Team Leaders / Members';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-user-group';

    public static function getPermissionPrefixes(): array
        {
            return [
                'view_any',
                'view',
                'view_all',
                'create',
                'update',
                'delete',
                'assign_appraisal',
                'delete_any',
                'force_delete',
                'restore',
                'force_delete_any',
            ];
        }
    public static function getEloquentQuery(): Builder
    {
        if(Auth::user()->can('view_all_appraisal::form::assigned::to::staff')){
            return parent::getEloquentQuery();
        }
        else {
            return parent::getEloquentQuery()
                ->where('supervisor_id', Auth::user()->id)
                ->orwhere('staff_id', Auth::user()->id);
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
                    ->relationship('appraisalForm', 'name' , fn (Builder $query) => $query->where('level', '!=', AppraisalFormLevel::Level3->value))
                    ->native(false)
                    ->disabledOn('edit')
                    ->required(),
                Select::make('staff_id')
                    ->relationship('staff', 'name')
                    ->required()
                    ->searchable(['name', 'emp_no'])
                    ->afterStateUpdated(function (Set $set, $state) {
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
                TextInput::make('supervisor_id')
                    ->required()
                    ->hiddenOn('edit')
                    ->default(fn (Get $get) => $get('supervisor_id'))
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
                TextColumn::make('assigned_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('appraisalForm.name')
                    ->sortable(),
                TextColumn::make('staff.name')
                    ->sortable(),
                TextColumn::make('supervisor.name')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge(),
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
                EditAction::make()
                    ->visible(fn($record) => $record->status === AssignedFormStatus::PendingStaff && $record->supervisor_id === auth('staff')->user()->id),
                Action::make('fill_form')
                    ->label('Fill Form')
                        ->button()
                        ->color('warning')
                        ->visible(fn($record) => $record->status === AssignedFormStatus::PendingStaff && $record->staff_id === auth('staff')->user()->id)
                        ->url(fn($record) => route('appraisal-form-fill', ['record' => $record]))
                        ->openUrlInNewTab(),
                Action::make('supervisor_fill_form')
                    ->label('Fill Form')
                        ->button()
                        ->color('success')
                        ->visible(fn($record) => $record->status === AssignedFormStatus::PendingSupervisor && $record->supervisor_id ===  auth('staff')->user()->id)
                        ->url(fn($record) => route('supervisor-appraisal-form-fill', ['record' => $record]))
                        ->openUrlInNewTab(),
                Action::make('results')
                    ->label('View Details')
                        ->button()
                        ->color('primary')
                        ->visible(fn($record) => $record->status !== AssignedFormStatus::PendingStaff && ($record->staff_id === auth('staff')->user()->id ||  $record->supervisor_id === auth('staff')->user()->id || in_array('HR', Shortcuts::callgetapi('/user/roles', ['id' => auth('staff')->user()->api_id])->json() ?? [])))
                        ->url(fn($record) => route('filament.staff.resources.appraisal-form-assigned-to-staffs.results', ['record' => $record]))
                        ,
            ])
            ->recordUrl(false)
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            AppraisalFormEntriesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'results' => ViewResults::route('/{record}/results'),
            'index' => ListAppraisalFormAssignedToStaff::route('/'),
            'create' => CreateAppraisalFormAssignedToStaff::route('/create'),
            'edit' => EditAppraisalFormAssignedToStaff::route('/{record}/edit'),
        ];
    }
}
