<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        activity()->disableLogging();

        DB::beginTransaction();

        $admin = new Admin();
        $admin->first_name = 'Admin';
        $admin->last_name = 'OurSpace';
        $admin->email = 'admin@ourspace.com';
        $admin->email_verified_at = now();
        $admin->password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'; // password
        $admin->phone_number = '00000000000';
        $admin->save();

        $role = new Role();
        $role->name = 'SUPERADMIN';
        $role->guard_name = 'api_admin';
        $role->save();

        $admin->assignRole($role);

        DB::commit();
    }
}
