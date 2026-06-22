<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

class PermissionPresenter
{
    public static function groupedForGuard(string $guard): array
    {
        return Permission::query()
            ->where('guard_name', $guard)
            ->orderBy('name')
            ->get()
            ->groupBy(fn (Permission $permission) => self::groupKey($permission->name))
            ->map(fn (Collection $permissions, string $group) => [
                'key' => $group,
                'label' => self::groupLabel($group),
                'permissions' => $permissions->map(fn (Permission $permission) => [
                    'id' => $permission->id,
                    'name' => $permission->name,
                    'label' => self::permissionLabel($permission->name),
                ])->values()->all(),
            ])
            ->sortBy('label')
            ->values()
            ->all();
    }

    public static function groupKey(string $permission): string
    {
        if (preg_match('/^(?:view_any|view_all|view|create|update|delete_any|delete|assign)_(.+)$/', $permission, $matches)) {
            return $matches[1];
        }

        return $permission;
    }

    public static function groupLabel(string $group): string
    {
        return Str::title(str_replace(['::', '_'], [' — ', ' '], $group));
    }

    public static function permissionLabel(string $permission): string
    {
        if (preg_match('/^(view_any|view_all|view|create|update|delete_any|delete|assign)_(.+)$/', $permission, $matches)) {
            $action = match ($matches[1]) {
                'view_any' => 'View any',
                'view_all' => 'View all',
                'view' => 'View',
                'create' => 'Create',
                'update' => 'Update',
                'delete_any' => 'Delete any',
                'delete' => 'Delete',
                'assign' => 'Assign',
                default => Str::title(str_replace('_', ' ', $matches[1])),
            };

            return $action;
        }

        return Str::title(str_replace(['::', '_'], [' — ', ' '], $permission));
    }
}
