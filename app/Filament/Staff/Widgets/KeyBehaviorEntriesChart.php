<?php

namespace App\Filament\Staff\Widgets;

use App\Models\Department;
use App\Models\Staff;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Filters\SelectFilter;
use Illuminate\Support\Facades\DB;

class KeyBehaviorEntriesChart extends ChartWidget
{
    protected ?string $heading = 'Entries by Category';
    protected ?string $maxHeight = 'full';
    protected int | string | array $columnSpan = 'full';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getFilters(): array
    {
        return [
//            \Filament\Tables\Filters\SelectFilter::make('mode')
//                ->label('Display')
//                ->options([
//                    'category' => 'Category',
//                    'behavior' => 'Behavior',
//                ])
//                ->default('category'),

//            \Filament\Tables\Filters\SelectFilter::make('department_id')
//                ->label('Department')
//                ->options(Department::orderBy('name')->pluck('name', 'id')->toArray()),

//            \Filament\Tables\Filters\SelectFilter::make('staff_id')
//                ->label('Staff')
//                ->options(Staff::orderBy('name')->pluck('name', 'id')->toArray()),
//
//            \Filament\Tables\Filters\SelectFilter::make('form_level')
//                ->label('Form Level')
//                ->options([
//                    '1' => '1',
//                    '2' => '2',
//                    '3' => '3',
//                ]),
        ];
    }

    // Helper: read filter value from request (supports "filters" array or top-level query param)
    protected function getFilterValue(string $name)
    {
        $filters = request()->query('filters', []);
        if (is_array($filters) && array_key_exists($name, $filters)) {
            return $filters[$name];
        }

        // some setups may use nested arrays or direct query params
        $nested = request()->query('filters') ? data_get(request()->query('filters'), $name) : null;
        if ($nested !== null) {
            return $nested;
        }

        return request()->query($name);
    }

    protected function getData(): array
    {
        // Read filters via helper (avoids non-existent getFilterState)
        $mode = $this->getFilterValue('mode') ?? 'category';
        $departmentId = $this->getFilterValue('department_id');
        $staffId = $this->getFilterValue('staff_id');
        $formLevel = $this->getFilterValue('form_level');

        self::$heading = $mode === 'behavior' ? 'Entries by Behavior' : 'Entries by Category';

        $query = DB::table('appraisal_form_entries as e')
            ->join('appraisal_form_questions as q', 'q.id', '=', 'e.question_id')
            ->join('appraisal_form_key_behaviors as kb', 'kb.id', '=', 'q.appraisal_form_key_behavior_id')
            ->join('appraisal_form_categories as c', 'c.id', '=', 'kb.appraisal_form_category_id');

        $needStaffJoin = $departmentId !== null || $staffId !== null;
        if ($needStaffJoin) {
            $query->leftJoin('staff as s', 's.id', '=', 'e.staff_id');
            if ($departmentId !== null) {
                $query->where('s.department_id', $departmentId);
            }
            if ($staffId !== null) {
                $query->where('e.staff_id', $staffId);
            }
        }

        if ($formLevel !== null) {
            $query->leftJoin('appraisal_forms as f', 'f.id', '=', 'e.appraisal_form_id')
                ->where('f.level', $formLevel);
        }

        $query->where('e.hidden', false);

        if ($mode === 'behavior') {
            $selectId = 'kb.id';
            $selectLabel = 'kb.name';
            $groupCols = ['kb.id', 'kb.name'];
            $orderCol = 'kb.name';
        } else {
            $selectId = 'c.id';
            $selectLabel = 'c.name';
            $groupCols = ['c.id', 'c.name'];
            $orderCol = 'c.name';
        }

        $rows = $query
            ->select(
                DB::raw($selectId . ' as id'),
                DB::raw($selectLabel . ' as label'),
                DB::raw('AVG(e.supervisor_score) as supervisor_avg'),
                DB::raw('AVG(e.staff_score) as staff_avg')
            )
            ->groupBy(...$groupCols)
            ->orderBy($orderCol)
            ->get();

        $fullLabels = $rows->pluck('label')->all();

        $max = 40;
        $labels = array_map(function ($label) use ($max) {
            if (mb_strlen($label) > $max) {
                return mb_substr($label, 0, $max - 3) . '...';
            }
            return $label;
        }, $fullLabels);

        $supervisor = $rows->pluck('supervisor_avg')->map(fn ($v) => $v !== null ? round($v, 2) : 0)->all();
        $staff = $rows->pluck('staff_avg')->map(fn ($v) => $v !== null ? round($v, 2) : 0)->all();

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Supervisor Avg',
                    'data' => $supervisor,
                    'backgroundColor' => 'rgba(255, 99, 132, 0.7)',
                    'borderColor' => 'rgba(255, 99, 132, 1)',
                    'borderWidth' => 1,
                ],
                [
                    'label' => 'Staff Avg',
                    'data' => $staff,
                    'backgroundColor' => 'rgba(54, 162, 235, 0.7)',
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'borderWidth' => 1,
                ],
            ],
            'meta' => [
                'fullLabels' => $fullLabels,
                'filters' => [
                    'department_id' => $departmentId,
                    'staff_id' => $staffId,
                    'form_level' => $formLevel,
                    'mode' => $mode,
                ],
            ],
        ];
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'maintainAspectRatio' => false,
            'responsive' => true,
            'plugins' => [
                'legend' => ['position' => 'top'],
                'tooltip' => ['mode' => 'index', 'intersect' => false],
            ],
            'scales' => [
                'x' => ['beginAtZero' => true],
                'y' => [
                    'ticks' => [
                        'autoSkip' => true,
                        'maxRotation' => 0,
                        'font' => ['size' => 12],
                    ],
                ],
            ],
        ];
    }
}
