<?php

namespace App\Filament\Staff\Resources\FormsAssignedToHodResource\Pages;

use App\Enum\HODFormassigneeType;
use App\Filament\Staff\Resources\FormsAssignedToHodResource;
use App\Models\FormsAssignedToHod;
use App\Models\HodFormAssigneeEntry;
use App\Models\HodFormEntries;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Concerns\HasTabs;
use Filament\Resources\Pages\Page;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Contracts\HasTable;

class ViewResults extends Page implements HasTable
{
    use HasTabs;
    use InteractsWithTable;

    protected static string $resource = FormsAssignedToHodResource::class;

    protected static string $view = 'filament.staff.resources.forms-assigned-to-hod-resource.pages.view-results';

    public $record;

    public function mount($record): void
    {
        $this->record = FormsAssignedToHod::where('id', $record)->firstOrFail();
    }

    protected function getTableQuery(): Builder
    {
        return HodFormEntries::where('forms_assigned_to_hod_id' , $this->record->id);
    }

    public function infolist(InfoList $infoList): Infolist
    {
        return $infoList
            ->record($this->record)
            ->schema([
                Section::make('Appraisal Details')
                    ->columns([
                        'md' => 4,
                        'lg' => 4,
                        'sm' => 1,
                    ])
                    ->schema([
                        TextEntry::make('appraisalForm.name')
                            ->icon('heroicon-o-document-text'),
                        TextEntry::make('staff.name')
                            ->icon('heroicon-o-user'),
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
                        TextEntry::make('hod_comment')
                            ->label('Staff Comment')
                            ->icon('heroicon-o-chat-bubble-left-right'),
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
                TextColumn::make('self_score')
                    ->label('Self Score')
                    ->numeric()
                    ->suffix('/5')
                    ->summarize(Summarizer::make()
                        ->label('')
                        ->numeric( decimalPlaces: 1,)
                        ->suffix('%')
                        ->using(fn (\Illuminate\Database\Query\Builder $query) =>  (($query->where('hidden', false)->where('forms_assigned_to_hod_id',$this->record->id)->sum('self_score')) / ($query->where('hidden', false)->count() * 5))*100)
                    ),
                TextColumn::make('assignee_subordinate.score')
                    ->label('Subordniates Score')
                    ->getStateUsing(function ($record) {
                        return \App\Models\HodFormAssigneeEntry::whereHas('hodFormAssignee', function ($q) use ($record) {
                            $q->where('forms_assigned_to_hod_id', $record->forms_assigned_to_hod_id)
                                ->where('assignee_type', HODFormassigneeType::Subordinate->value);
                        })->where('hidden', false)
                            ->where('question_id' , $record->question_id)->sum('score');
                    })
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->suffix('/15')
                    ->summarize(Summarizer::make()
                        ->label('')
                        ->numeric( decimalPlaces: 1,)
                        ->suffix('%')
                        ->using(fn (\Illuminate\Database\Query\Builder $query) => (
                                \App\Models\HodFormAssigneeEntry::whereHas('hodFormAssignee', function ($q) {
                                    $q->where('forms_assigned_to_hod_id', $this->record->id)
                                        ->where('assignee_type', \App\Enum\HODFormassigneeType::Subordinate->value);
                                })
                                    ->where('hidden', false)
                                    ->sum('score')
                                /
                                (\App\Models\HodFormAssigneeEntry::whereHas('hodFormAssignee', function ($q) {
                                        $q->where('forms_assigned_to_hod_id', $this->record->id)
                                            ->where('assignee_type', \App\Enum\HODFormassigneeType::Subordinate->value);
                                    })
                                        ->where('hidden', false)
                                        ->count() * 15)
                            ) * 100)
                    ),

                TextColumn::make('assignee_manager.score')
                    ->label('Managers Score')
                    ->getStateUsing(function ($record) {
                        return \App\Models\HodFormAssigneeEntry::whereHas('hodFormAssignee', function ($q) use ($record) {
                            $q->where('forms_assigned_to_hod_id', $record->forms_assigned_to_hod_id)
                                ->where('assignee_type', HODFormassigneeType::Manager->value);
                        })->where('hidden', false)
                            ->where('question_id' , $record->question_id)->sum('score');
                    })
                    ->numeric()
                    ->suffix('/15')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->summarize(Summarizer::make()
                        ->label('')
                        ->numeric( decimalPlaces: 1,)
                        ->suffix('%')
                        ->using(fn (\Illuminate\Database\Query\Builder $query) => (
                                \App\Models\HodFormAssigneeEntry::whereHas('hodFormAssignee', function ($q) {
                                    $q->where('forms_assigned_to_hod_id', $this->record->id)
                                        ->where('assignee_type', \App\Enum\HODFormassigneeType::Manager->value);
                                })
                                    ->where('hidden', false)
                                    ->sum('score')
                                /
                                (\App\Models\HodFormAssigneeEntry::whereHas('hodFormAssignee', function ($q) {
                                        $q->where('forms_assigned_to_hod_id', $this->record->id)
                                            ->where('assignee_type', \App\Enum\HODFormassigneeType::Manager->value);
                                    })
                                        ->where('hidden', false)
                                        ->count() * 15)
                            ) * 100)
                    ),


                TextColumn::make('assignee_coworker.score')
                    ->label('CoWorkers Score')
                    ->getStateUsing(function ($record) {
                        return \App\Models\HodFormAssigneeEntry::whereHas('hodFormAssignee', function ($q) use ($record) {
                            $q->where('forms_assigned_to_hod_id', $record->forms_assigned_to_hod_id)
                                ->where('assignee_type', HODFormassigneeType::CoWorker->value);
                        })->where('hidden', false)
                            ->where('question_id' , $record->question_id)->sum('score');
                    })
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->suffix('/15')
                    ->summarize(Summarizer::make()
                        ->label('')
                        ->numeric( decimalPlaces: 1,)
                        ->suffix('%')
                        ->using(fn (\Illuminate\Database\Query\Builder $query) => (
                                \App\Models\HodFormAssigneeEntry::whereHas('hodFormAssignee', function ($q) {
                                    $q->where('forms_assigned_to_hod_id', $this->record->id)
                                        ->where('assignee_type', \App\Enum\HODFormassigneeType::CoWorker->value);
                                })
                                    ->where('hidden', false)
                                    ->sum('score')
                                /
                                (\App\Models\HodFormAssigneeEntry::whereHas('hodFormAssignee', function ($q) {
                                        $q->where('forms_assigned_to_hod_id', $this->record->id)
                                            ->where('assignee_type', \App\Enum\HODFormassigneeType::CoWorker->value);
                                    })
                                        ->where('hidden', false)
                                        ->count() * 15)
                            ) * 100)
                    ),
                TextColumn::make('total_score')
                    ->label('Total Score')
                    ->getStateUsing(function ($record) {
                        return \App\Models\HodFormAssigneeEntry::whereHas('hodFormAssignee', function ($q) use ($record) {
                            $q->where('forms_assigned_to_hod_id', $record->forms_assigned_to_hod_id);
                        })
                            ->where('hidden', false)
                            ->where('question_id', $record->question_id)
                            ->sum('score');
                    })
                    ->numeric()
                    ->suffix('/45')
                    ->summarize(Summarizer::make()
                        ->label('')
                        ->numeric( decimalPlaces: 1,)
                        ->suffix('%')
                        ->using(fn (\Illuminate\Database\Query\Builder $query) => (
                                \App\Models\HodFormAssigneeEntry::whereHas('hodFormAssignee', function ($q) {
                                    $q->where('forms_assigned_to_hod_id', $this->record->id)
                                        ->orwhere('assignee_type', \App\Enum\HODFormassigneeType::Manager->value)
                                        ->orwhere('assignee_type', \App\Enum\HODFormassigneeType::CoWorker->value)
                                        ->orwhere('assignee_type', \App\Enum\HODFormassigneeType::Subordinate->value);
                                })
                                    ->where('hidden', false)
                                    ->sum('score')
                                /
                                (\App\Models\HodFormAssigneeEntry::whereHas('hodFormAssignee', function ($q) {
                                        $q->where('forms_assigned_to_hod_id', $this->record->id)
                                            ->orwhere('assignee_type', \App\Enum\HODFormassigneeType::Manager->value)
                                            ->orwhere('assignee_type', \App\Enum\HODFormassigneeType::CoWorker->value)
                                            ->orwhere('assignee_type', \App\Enum\HODFormassigneeType::Subordinate->value);
                                    })
                                        ->where('hidden', false)
                                        ->count() * 45)
                            ) * 100)
                    ),
            ])
            ->actions([
                Action::make('view_comments')
                    ->form([
                        Textarea::make('comment')
                            ->label("Supervisor's Comment")
                            ->default(fn($record) => $record->comment)
                            ->hidden(fn ($record) => !$record->comment)
                            ->readOnly()
                            ->autosize()
                            ->columnSpanFull(),
                        Textarea::make('manager_comment')
                            ->label("Manager's Comment")
                            ->default(function ($record) {
                                return $this->getassigneecomment($record , HODFormassigneeType::Manager->value);
                            })
                            ->readOnly()
                            ->autosize()
                            ->hidden(function ($record) {
                                return !\App\Models\HodFormAssigneeEntry::whereHas('hodFormAssignee', function ($q) use ($record) {
                                    $q->where('forms_assigned_to_hod_id', $record->forms_assigned_to_hod_id)
                                        ->where('assignee_type', HODFormassigneeType::Manager->value);
                                })->where('hidden', false)
                                    ->where('question_id', $record->question_id)
                                    ->whereNotNull('comment')
                                    ->where('comment', '!=', '')
                                    ->exists();
                            })
                            ->columnSpanFull(),
                        Textarea::make('sub_comment')
                            ->label("Subordinate's Comment")
                            ->default(function ($record) {
                                return $this->getassigneecomment($record , HODFormassigneeType::Subordinate->value);
                            })
                            ->readOnly()
                            ->autosize()
                            ->hidden(function ($record) {
                                return !\App\Models\HodFormAssigneeEntry::whereHas('hodFormAssignee', function ($q) use ($record) {
                                    $q->where('forms_assigned_to_hod_id', $record->forms_assigned_to_hod_id)
                                        ->where('assignee_type', HODFormassigneeType::Subordinate->value);
                                })->where('hidden', false)
                                    ->where('question_id', $record->question_id)
                                    ->whereNotNull('comment')
                                    ->where('comment', '!=', '')
                                    ->exists();
                            })
                            ->columnSpanFull(),
                        Textarea::make('coworker-comment')
                            ->label("Corworker's Comment")
                            ->default(function ($record) {
                                return $this->getassigneecomment($record , HODFormassigneeType::CoWorker->value);
                            })
                            ->readOnly()
                            ->autosize()
                            ->hidden(function ($record) {
                                return !\App\Models\HodFormAssigneeEntry::whereHas('hodFormAssignee', function ($q) use ($record) {
                                    $q->where('forms_assigned_to_hod_id', $record->forms_assigned_to_hod_id)
                                        ->where('assignee_type', HODFormassigneeType::CoWorker->value);
                                })->where('hidden', false)
                                    ->where('question_id', $record->question_id)
                                    ->whereNotNull('comment')
                                    ->where('comment', '!=', '')
                                    ->exists();
                            })
                            ->columnSpanFull(),
                    ])
                    ->label('')
                    ->modalSubmitAction(false)
                    ->hidden(function ($record) {
                        return $this->checkcommentfieldsnull($record);
                    })
                    ->icon('heroicon-o-chat-bubble-bottom-center-text'),
            ])
            ->headerActions([
                Action::make('view_assignee_details')
                    ->url(fn () => route('filament.staff.resources.forms-assigned-to-hods.assignee-results', ['record' => $this->record->id]))
                    ->label('View Assignees Results')
                    ->icon('heroicon-o-users'),
            ])
            ->defaultPaginationPageOption('all');
    }

    /**
     * @param $record
     * @return bool
     */
    function checkcommentfieldsnull($record): bool
    {
        $managerComment = \App\Models\HodFormAssigneeEntry::whereHas('hodFormAssignee', function ($q) use ($record) {
            $q->where('forms_assigned_to_hod_id', $record->forms_assigned_to_hod_id)
                ->where('assignee_type', \App\Enum\HODFormassigneeType::Manager->value);
        })->where('hidden', false)
            ->where('question_id', $record->question_id)
            ->whereNotNull('comment')
            ->where('comment', '!=', '')
            ->exists();

        $subComment = \App\Models\HodFormAssigneeEntry::whereHas('hodFormAssignee', function ($q) use ($record) {
            $q->where('forms_assigned_to_hod_id', $record->forms_assigned_to_hod_id)
                ->where('assignee_type', \App\Enum\HODFormassigneeType::Subordinate->value);
        })->where('hidden', false)
            ->where('question_id', $record->question_id)
            ->whereNotNull('comment')
            ->where('comment', '!=', '')
            ->exists();

        $coworkerComment = \App\Models\HodFormAssigneeEntry::whereHas('hodFormAssignee', function ($q) use ($record) {
            $q->where('forms_assigned_to_hod_id', $record->forms_assigned_to_hod_id)
                ->where('assignee_type', \App\Enum\HODFormassigneeType::CoWorker->value);
        })->where('hidden', false)
            ->where('question_id', $record->question_id)
            ->whereNotNull('comment')
            ->where('comment', '!=', '')
            ->exists();

        $supervisorComment = !empty($record->comment);

        return !($managerComment || $subComment || $coworkerComment || $supervisorComment);
    }

    /**
     * @param $record
     * @return mixed
     */
    function getassigneecomment($record , $assigneetype)
    {
        return \App\Models\HodFormAssigneeEntry::whereHas('hodFormAssignee', function ($q) use ($record ,  $assigneetype) {
            $q->where('forms_assigned_to_hod_id', $record->forms_assigned_to_hod_id)
                ->where('assignee_type', $assigneetype);
        })->where('hidden', false)
            ->where('question_id', $record->question_id)->pluck('comment')->map(fn($comment) => '• ' . $comment)->implode("\n");
    }

}
