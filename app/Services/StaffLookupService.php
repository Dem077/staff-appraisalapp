<?php

namespace App\Services;

use App\Models\Staff;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class StaffLookupService
{
    public static function groupedStaffOptions(): array
    {
        return Cache::remember('grouped_staff_options_v1', 60, function () {
            $all = Shortcuts::callgetapi('/users/active', [])->json();
            if (! is_array($all)) {
                return [];
            }

            $deptNameCache = [];

            return collect($all)
                ->filter(fn ($u) => is_array($u) && isset($u['id']))
                ->groupBy(function ($u) use (&$deptNameCache) {
                    $deptId = $u['department_id'] ?? null;
                    if (! $deptId) {
                        return 'Unknown Department';
                    }
                    if (! array_key_exists($deptId, $deptNameCache)) {
                        $resp = Shortcuts::callgetapi('/department', ['dep_id' => $deptId])->json();
                        $deptNameCache[$deptId] = (is_array($resp) && isset($resp['name']))
                            ? (string) $resp['name']
                            : 'Unknown Department';
                    }

                    return $deptNameCache[$deptId];
                })
                ->map(function ($group) {
                    return collect($group)
                        ->filter(fn ($u) => isset($u['name']) && $u['name'] !== '')
                        ->mapWithKeys(function ($u) {
                            $actualId = Staff::where('api_id', $u['id'])->value('id');
                            if (! $actualId) {
                                return [];
                            }
                            $label = (string) $u['name'];
                            if (! empty($u['emp_no'])) {
                                $label .= ' ('.$u['emp_no'].')';
                            }

                            return [$actualId => $label];
                        })
                        ->toArray();
                })
                ->toArray();
        });
    }

    public static function flatStaffOptions(): array
    {
        $grouped = self::groupedStaffOptions();
        $flat = [];
        foreach ($grouped as $staff) {
            foreach ($staff as $id => $label) {
                $flat[$id] = $label;
            }
        }

        return $flat;
    }

    public static function resolveSupervisorId(int $staffId): ?int
    {
        $apiId = Staff::where('id', $staffId)->value('api_id');
        if (! $apiId) {
            return null;
        }

        $response = Http::withHeaders([
            'X-API-KEY' => config('app.appkey'),
            'Accept' => 'application/json',
        ])->get(config('app.apiurl').'/users/supervisor', ['id' => $apiId]);

        if (! $response->ok()) {
            return null;
        }

        return Staff::where('api_id', $response->json('id'))->value('id');
    }
}
