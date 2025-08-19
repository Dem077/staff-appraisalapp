<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use BezhanSalleh\FilamentShield\Support\Utils;
use Spatie\Permission\PermissionRegistrar;

class ShieldSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $rolesWithPermissions = '[{"name":"super_admin","guard_name":"web","permissions":["view_activity","view_any_activity","create_activity","update_activity","delete_activity","delete_any_activity","view_appraisal::form","view_any_appraisal::form","create_appraisal::form","update_appraisal::form","delete_appraisal::form","delete_any_appraisal::form","view_appraisal::form::key::behavior","view_any_appraisal::form::key::behavior","create_appraisal::form::key::behavior","update_appraisal::form::key::behavior","delete_appraisal::form::key::behavior","delete_any_appraisal::form::key::behavior","view_role","view_any_role","create_role","update_role","delete_role","delete_any_role","view_staff","view_any_staff","create_staff","update_staff","delete_staff","delete_any_staff","view_user","view_any_user","create_user","update_user","delete_user","delete_any_user","widget_OverlookWidget","widget_LatestAccessLogs"]},{"name":"super_admin2","guard_name":"staff","permissions":["view_role","view_any_role","create_role","update_role","delete_role","delete_any_role"]}]';
        $directPermissions = '{"38":{"name":"view_any_appraisal::form::assigned::to::staff","guard_name":"staff"},"39":{"name":"view_appraisal::form::assigned::to::staff","guard_name":"staff"},"40":{"name":"create_appraisal::form::assigned::to::staff","guard_name":"staff"},"41":{"name":"update_appraisal::form::assigned::to::staff","guard_name":"staff"},"42":{"name":"delete_appraisal::form::assigned::to::staff","guard_name":"staff"},"43":{"name":"assign_appraisal_appraisal::form::assigned::to::staff","guard_name":"staff"},"44":{"name":"delete_any_appraisal::form::assigned::to::staff","guard_name":"staff"},"45":{"name":"force_delete_appraisal::form::assigned::to::staff","guard_name":"staff"},"46":{"name":"restore_appraisal::form::assigned::to::staff","guard_name":"staff"},"47":{"name":"force_delete_any_appraisal::form::assigned::to::staff","guard_name":"staff"}}';

        static::makeRolesWithPermissions($rolesWithPermissions);
        static::makeDirectPermissions($directPermissions);

        $this->command->info('Shield Seeding Completed.');
    }

    protected static function makeRolesWithPermissions(string $rolesWithPermissions): void
    {
        if (! blank($rolePlusPermissions = json_decode($rolesWithPermissions, true))) {
            /** @var Model $roleModel */
            $roleModel = Utils::getRoleModel();
            /** @var Model $permissionModel */
            $permissionModel = Utils::getPermissionModel();

            foreach ($rolePlusPermissions as $rolePlusPermission) {
                $role = $roleModel::firstOrCreate([
                    'name' => $rolePlusPermission['name'],
                    'guard_name' => $rolePlusPermission['guard_name'],
                ]);

                if (! blank($rolePlusPermission['permissions'])) {
                    $permissionModels = collect($rolePlusPermission['permissions'])
                        ->map(fn ($permission) => $permissionModel::firstOrCreate([
                            'name' => $permission,
                            'guard_name' => $rolePlusPermission['guard_name'],
                        ]))
                        ->all();

                    $role->syncPermissions($permissionModels);
                }
            }
        }
    }

    public static function makeDirectPermissions(string $directPermissions): void
    {
        if (! blank($permissions = json_decode($directPermissions, true))) {
            /** @var Model $permissionModel */
            $permissionModel = Utils::getPermissionModel();

            foreach ($permissions as $permission) {
                if ($permissionModel::whereName($permission)->doesntExist()) {
                    $permissionModel::create([
                        'name' => $permission['name'],
                        'guard_name' => $permission['guard_name'],
                    ]);
                }
            }
        }
    }
}
