<?php

namespace App\Filament\Staff\Resources\AppraisalFormAssignedToStaffResource\Pages;

use App\Enum\AssignedFormStatus;
use App\Filament\Staff\Resources\AppraisalFormAssignedToStaffResource;
use App\Models\AppraisalFormAssignedToStaff;
use App\Models\AppraisalFormEntries;
use App\Services\Shortcuts;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\Actions;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\Summarizers\Count;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Resources\Concerns\HasTabs;
use Filament\Resources\Pages\Page;
use Filament\Tables\Grouping\Group;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Table;

class ViewResults extends Page implements HasTable
{
    use HasTabs;
    use InteractsWithTable;

    protected static string $resource = AppraisalFormAssignedToStaffResource::class;

    protected static string $view = 'filament.staff.resources.appraisal-form-assigned-to-staff-resource.pages.view-results';

    public $record;

    public function mount($record): void
    {
        $this->record = AppraisalFormAssignedToStaff::where('id', $record)->firstOrFail();
    }

    protected function getTableQuery(): Builder
    {
        // Query the Slot model and filter by doctor_roster_id
        return AppraisalFormEntries::where('appraisal_assigned_to_staff_id', $this->record->id)->where('hidden', false);
    }

    public function infolist(InfoList $infoList): Infolist
    {
        return $infoList
            ->record($this->record)
            ->schema([
                Section::make('Appraisal Details')
                    ->columns([
                        'md' => 5,
                        'lg' => 5,
                        'sm' => 1,
                    ])
                    ->schema([
                        TextEntry::make('appraisalForm.name')
                            ->icon('heroicon-o-document-text'),
                        TextEntry::make('staff.name')
                            ->icon('heroicon-o-user'),
                        TextEntry::make('supervisor.name')
                            ->icon('heroicon-o-user-circle'),
                        TextEntry::make('assigned_date')
                            ->icon('heroicon-o-calendar'),
                        TextEntry::make('status')
                            ->badge()
                            ->icon('heroicon-o-information-circle'),
                    ]),
                Section::make('Comments')
                    ->columns([
                        'md' => 1,
                        'lg' => 1,
                        'sm' => 1,
                    ])
                    ->schema([
                        TextEntry::make('staff_comment')
                            ->label('Staff Comment')
                            ->icon('heroicon-o-chat-bubble-left-right'),
                        TextEntry::make('supervisor_comment')
                            ->label("Supervisor's Comment")
                            ->icon('heroicon-o-chat-bubble-left-right'),
                        TextEntry::make('hr_comment')
                            ->label("HR Comment")
                            ->icon('heroicon-o-chat-bubble-left-right'),
                    ])->headerActions([
                        Actions\Action::make('add_hr_comment')
                        ->label('Add HR Comment')
                        ->icon('heroicon-o-plus')
                            ->visible(fn ($record) => $record->status === AssignedFormStatus::HRComment && in_array('HR', Shortcuts::callgetapi('/user/roles', ['id' => auth('staff')->user()->id])->json() ?? []))
                        ->form([
                            Textarea::make('hr_comment'),
                        ])
                        ->action(function ($record , $data){

                            if($data['hr_comment']){
                                $record->update([
                                    'hr_comment'=> $data['hr_comment'],
                                    'status' => AssignedFormStatus::Complete->value,
                                ]);
                                Notification::make('comment_added')
                                    ->body('Comment added Successfully')
                                    ->icon('heroicon-o-check')
                                    ->color('success');
                            }

                        }),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Behavioral Indicators and Scores')
            ->groups([
                Group::make('question.appraisalFormKeyBehavior.appraisalFormCategory.name')
                    ->label('')
                    ->collapsible(),
            ])
            ->defaultGroup('question.appraisalFormKeyBehavior.appraisalFormCategory.name')
            ->query($this->getTableQuery()) // Use the corrected query
            ->columns([

                TextColumn::make('question.behavioral_indicators')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('staff_score')
                    ->label('Self Score')
                    ->numeric()
                    ->suffix('/5')
                    ->summarize(Summarizer::make()
                        ->label('')
                        ->numeric( decimalPlaces: 1,)
                        ->suffix('%')
                        ->using(fn (\Illuminate\Database\Query\Builder $query) =>  (($query->where('hidden', false)->sum('staff_score')) / ($query->where('hidden', false)->count() * 5))*100)
                    ),
                TextColumn::make('supervisor_score')
                    ->numeric()
                    ->suffix('/5')
                    ->summarize(Summarizer::make()
                        ->label('')
                        ->numeric( decimalPlaces: 1,)
                        ->suffix('%')
                        ->using(fn (\Illuminate\Database\Query\Builder $query) => (($query->where('hidden', false)->sum('supervisor_score')) / ($query->where('hidden', false)->count() * 5))*100)
                    ),

            ])
            ->actions([
                Action::make('view_comments')
                    ->form([
                        Textarea::make('supervisor_comment')
                            ->label("Supervisor's Comment")
                            ->default(fn($record) => $record->supervisor_comment)
                            ->readOnly()
                            ->autosize()
                            ->columnSpanFull(),
                    ])
                    ->label('View Comment')
                    ->modalSubmitAction(false)
                    ->hidden(fn($record) => !$record->supervisor_comment)
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->button(),
            ]);
    }
}
