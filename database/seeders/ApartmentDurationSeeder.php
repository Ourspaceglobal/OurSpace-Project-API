<?php

namespace Database\Seeders;

use App\Models\ApartmentDuration;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ApartmentDurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $durations = [
            [
                'id' => Str::orderedUuid()->toString(),
                'duration_in_days' => 1,
                'period' => 'night',
            ],
            [
                'id' => Str::orderedUuid()->toString(),
                'duration_in_days' => 7,
                'period' => 'week',
            ],
            [
                'id' => Str::orderedUuid()->toString(),
                'duration_in_days' => 30,
                'period' => 'month',
            ],
            [
                'id' => Str::orderedUuid()->toString(),
                'duration_in_days' => 365,
                'period' => 'year',
            ],
        ];

        ApartmentDuration::query()->upsert(
            $durations,
            ['period'],
            ['duration_in_days']
        );
    }
}
