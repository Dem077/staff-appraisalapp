<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class ShieldSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $rolesWithPermissions = json_decode('[{"name":"super_admin","guard_name":"web","permissions":["view_appraisal::form","view_any_appraisal::form","create_appraisal::form","update_appraisal::form","delete_appraisal::form","delete_any_appraisal::form","view_appraisal::form::key::behavior","view_any_appraisal::form::key::behavior","create_appraisal::form::key::behavior","update_appraisal::form::key::behavior","delete_appraisal::form::key::behavior","delete_any_appraisal::form::key::behavior","view_role","view_any_role","create_role","update_role","delete_role","delete_any_role","view_staff","view_any_staff","create_staff","update_staff","delete_staff","delete_any_staff","view_user","view_any_user","create_user","update_user","delete_user","delete_any_user","view_any_appraisal::form::assigned::to::staff","view_all_appraisal::form::assigned::to::staff","assign_appraisal_appraisal::form::assigned::to::staff","view_any_forms::assigned::to::hod","view_all_forms::assigned::to::hod"]},{"name":"normal_user","guard_name":"staff","permissions":["view_appraisal::form::assigned::to::staff","view_forms::assigned::to::hod"]}]', true);

        foreach ($rolesWithPermissions as $roleData) {
            $role = Role::firstOrCreate([
                'name' => $roleData['name'],
                'guard_name' => $roleData['guard_name'],
            ]);

            $permissions = collect($roleData['permissions'] ?? [])
                ->map(fn ($name) => Permission::firstOrCreate([
                    'name' => $name,
                    'guard_name' => $roleData['guard_name'],
                ]))
                ->all();

            $role->syncPermissions($permissions);
        }

        $staffPermissions = [
            'view_any_appraisal::form::assigned::to::staff',
            'view_appraisal::form::assigned::to::staff',
            'create_appraisal::form::assigned::to::staff',
            'update_appraisal::form::assigned::to::staff',
            'delete_appraisal::form::assigned::to::staff',
            'assign_appraisal_appraisal::form::assigned::to::staff',
            'delete_any_appraisal::form::assigned::to::staff',
            'view_all_appraisal::form::assigned::to::staff',
            'view_any_forms::assigned::to::hod',
            'view_forms::assigned::to::hod',
            'view_all_forms::assigned::to::hod',
        ];

        foreach ($staffPermissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'staff']);
        }

        $this->command?->info('Permissions seeded.');
    }
}
