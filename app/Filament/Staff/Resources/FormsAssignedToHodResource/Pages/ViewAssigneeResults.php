<?php

namespace App\Filament\Staff\Resources\FormsAssignedToHodResource\Pages;

use App\Enum\HODFormassigneeType;
use App\Filament\Staff\Resources\FormsAssignedToHodResource;
use App\Models\FormsAssignedToHod;
use App\Models\HodFormassignee;
use App\Models\HodFormAssigneeEntry;
use App\Models\HodFormEntries;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Components\Tab;
use Filament\Resources\Concerns\HasTabs;
use Filament\Resources\Pages\Page;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\SelectFilter;

class ViewAssigneeResults extends Page implements HasTable
{
    use HasTabs;
    use InteractsWithTable;

    protected static string $resource = FormsAssignedToHodResource::class;

    protected static string $view = 'filament.staff.resources.forms-assigned-to-hod-resource.pages.view-assignee-results';

    public $record;

    public function mount($record): void
    {
        $this->record = FormsAssignedToHod::where('id', $record)->firstOrFail();
    }


    protected function getTableQuery(): Builder
    {
        $assigned = HodFormassignee::where('forms_assigned_to_hod_id' , $this->record->id);
        return HodFormAssigneeEntry::whereIn('hod_form_assignee_id', $assigned->pluck('id')->where('hidden', false));
    }


    public function infolist(InfoList $infoList): Infolist
    {
        return $infoList
            ->record($this->record)
            ->schema([
                Section::make('Assignee Details')
                    ->columns([
                        'md' => 2,
                        'lg' => 2,
                        'sm' => 2,
                    ])
                    ->schema([
                        TextEntry::make('hodFormAssignees.staff.name')
                            ->label('Name')
                            ->listWithLineBreaks()
                            ->icon('heroicon-o-user'),
                        TextEntry::make('hodFormAssignees.assignee_type')
                            ->label('Type')
//                            ->badge()
                            ->listWithLineBreaks(),
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

                TextColumn::make('hodFormAssignee.staff.name')
                    ->label('Assignee Name')
                    ->sortable()
                    ->searchable()
                    ->alignCenter(),

                TextColumn::make('hodFormAssignee.assignee_type')
                    ->label('Assignee Type')
                    ->sortable()
                    ->badge()
                    ->searchable()
                    ->alignCenter(),
                TextColumn::make('score')
                    ->label('Score')
                    ->sortable()
                    ->suffix('/5')
                    ->summarize(Summarizer::make()
                        ->label('')
                        ->numeric(decimalPlaces: 1)
                        ->suffix('%')
                        ->using(fn (\Illuminate\Database\Query\Builder $query) => (
                                $query->where('hidden', false)->sum('score')
                                /
                                (max($query->where('hidden', false)->count(), 1) * 5)
                            ) * 100)
                    )
                    ->alignCenter(),


            ])
            ->actions([

                Action::make('view_comments')
                    ->form([
                        Textarea::make('comment')
                            ->label("Comment")
                            ->default(fn($record) => $record->comment)
                            ->readOnly()
                            ->autosize()
                            ->columnSpanFull(),
                    ])
                    ->label('View Comment')
                    ->modalSubmitAction(false)
                    ->hidden(fn($record) => !$record->comment)
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->button(),
            ])
            ->filters([
                SelectFilter::make('hod_form_assignee_id')
                    ->label('Assignee Name')
                    ->options(
                        \App\Models\HodFormassignee::where('forms_assigned_to_hod_id', $this->record->id)
                            ->with('staff')
                            ->get()
                            ->pluck('staff.name', 'id')
                            ->toArray()
                    ),
//                    QueryBuilder::make()
//                        ->constraints([
//                            QueryBuilder\Constraints\SelectConstraint::make('assignee_type')
//                                ->relationship('hodFormAssignee' , 'assignee_type')
//                                ->options(
//                                    HODFormassigneeType::class,
//                                ),
//
//                        ]),
                // Alternatively, for text search:
                // TextInputFilter::make('hodFormAssignee.staff.name')->label('Assignee Name'),
            ])
            ->headerActions([
            ])
            ->defaultPaginationPageOption('all');
    }

}
