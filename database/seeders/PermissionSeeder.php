<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Define permissions
        $permissions = [
            'api_admin' => [
                'config_data' => [
                    'manage states' => 'enable admin to perform all CRUD operations on the state model',
                    'manage cities' => 'enable admin to perform all CRUD operations on the city model',
                    'manage local governments' =>
                        'enable admin to perform all CRUD operations on the local government model',
                    'manage categories' => 'enable admin to perform all CRUD operations on the category model',
                    'manage sub-categories' => 'enable admin to perform all CRUD operations on the sub-category model',
                    'manage amenities' => 'enable admin to perform all CRUD operations on the amenity model',
                    'manage system data' => 'enable admin to update system data',
                    'manage banks' => 'enable admin to perform all CRUD operations on the bank model',
                ],
                'apartment' => [
                    'manage system apartment kycs'
                        => 'enable admin to perform all CRUD operations on the system apartment kycs model',
                    'manage apartment durations'
                        => 'enable admin to perform manage the apartment durations for pricing',
                    'manage apartments' => 'enable admin to perform manage all apartments',
                    'manage apartment bookings' => 'enable admin to perform manage all apartment bookings',
                    'manage apartment rentals' => 'enable admin to perform manage all apartment rentals',
                ],
                'landlord' => [
                    'manage landlord requests' => 'enable admin to manage all landlord requests',
                ],
                'support' => [
                    'manage support tickets' => 'enable admin to manage all support tickets',
                ],
                'blog' => [
                    'manage posts' => 'enable admin to manage blog posts and comments',
                ],
                'finance' => [
                    'manage withdrawal requests' => 'enable admin to manage all withdrawal requests',
                    'manage wallet funding requests' => 'enable admin to manage all wallet funding requests',
                    'manage payment transactions' => 'enable admin to manage payment transactions',
                ],
                'security' => [
                    'manage access control list' => 'enable admin to manage roles and permissions',
                ],
                'management' => [
                    'manage admins' => 'enable admin to manage admins',
                    'manage users' => 'enable admin to manage users',
                    'manage push notifications' => 'enable admin to manage push notifications',
                ],
                'notifications' => [
                    'receive email notifications' => 'enable admin to receive email notifications',
                ],
            ],
            'api_user' => [],
        ];

        // Delete permissions
        $obsoletePermissions = [
            'api_admin' => [
                'apartment' => [
                    'manage apartment' => 'enable admin to perform manage all apartments',
                ],
                'config_data' => [
                    'manage banks' => 'enable admin to perform all CRUD operations on the bank model',
                ],
            ],
            'api_user' => [],
        ];

        DB::beginTransaction();

        // Create permissions
        foreach ($permissions as $guardName => $groups) {
            foreach ($groups as $groupName => $names) {
                foreach ($names as $name => $description) {
                    $permission = Permission::firstOrNew([
                        'name' => $name,
                        'guard_name' => $guardName
                    ]);
                    $permission->description = $description;
                    $permission->group_name = $groupName;
                    $permission->save();

                    $primaryAdminRoles = Role::where('name', 'SUPERADMIN')
                        ->where('guard_name', $guardName)
                        ->get();

                    foreach ($primaryAdminRoles as $primaryAdminRole) {
                        $primaryAdminRole->permissions()->syncWithoutDetaching($permission->id);
                    }
                }
            }
        }

        // Delete permissions
        foreach ($obsoletePermissions as $guardName => $groups) {
            foreach ($groups as $groupName => $names) {
                foreach ($names as $name => $description) {
                    $permission = Permission::where([
                        'name' => $name,
                        'guard_name' => $guardName
                    ])->delete();
                }
            }
        }

        DB::commit();
    }
}
