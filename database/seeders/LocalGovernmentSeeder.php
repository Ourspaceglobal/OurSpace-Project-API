<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\LocalGovernment;
use Illuminate\Database\Seeder;

class LocalGovernmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $localGovernment = new LocalGovernment();
        $localGovernment->city_id = City::pluck('id')->sole();
        $localGovernment->name = 'Ebute';
        $localGovernment->save();
    }
}
