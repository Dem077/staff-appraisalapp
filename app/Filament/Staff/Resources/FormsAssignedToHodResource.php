<?php

namespace App\Filament\Staff\Resources;

use App\Filament\Staff\Resources\FormsAssignedToHodResource\Pages;
use App\Filament\Staff\Resources\FormsAssignedToHodResource\RelationManagers;
use App\Models\FormsAssignedToHod;
use App\Models\Staff;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class FormsAssignedToHodResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = FormsAssignedToHod::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getPermissionPrefixes(): array
    {
        return [
            'view_any',
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
    // public static function getEloquentQuery(): Builder
    // {
    //     return parent::getEloquentQuery()
    //         ->where('hod_id', Auth::user()->id);
    // }

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
                    ->formatStateUsing(function ($state) {
                        return match ($state) {
                            'pending_staff_appraisal' => 'Pending Staff Appraisal',
                            'pending_assignee_appraisal' => 'Pending Assignee Review',
                            'complete' => 'Completed',
                            default => 'Unknown',
                        };
                    })
                    ->color(fn ($state) => match ($state) {
                        'pending_staff_appraisal' => 'warning',
                        'pending_assignee_appraisal' => 'info',
                        'complete' => 'success',
                        default => 'secondary',
                    })
                    ->sortable()
                    ->searchable(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFormsAssignedToHods::route('/'),
            'create' => Pages\CreateFormsAssignedToHod::route('/create'),
            'edit' => Pages\EditFormsAssignedToHod::route('/{record}/edit'),
        ];
    }
}
