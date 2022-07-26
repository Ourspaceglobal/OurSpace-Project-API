<?php

namespace Database\Seeders;

use App\Models\Datatype;
use App\Models\SystemData;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SystemDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $systemData = [
            [
                'title' => 'Agreement Note',
                'content' => 'Find a good apartment.',
                'hint' => 'Agreement Note for Tenants',
                'datatype_id' => Datatype::where('name', 'longtext')->pluck('id')->sole(),
            ],
            [
                'title' => 'Service Charge',
                'content' => '10',
                'hint' => 'What percentage should be accrued to rental payments?',
                'datatype_id' => Datatype::where('name', 'percentage')->pluck('id')->sole(),
            ],
            [
                'title' => 'Bank Account',
                'content' => 'N/A',
                'hint' => 'Bank account details for manual payments.',
                'datatype_id' => Datatype::where('name', 'longtext')->pluck('id')->sole(),
            ],
            [
                'title' => 'Booking Period',
                'content' => '183',
                'hint' => 'How many days should tenants be able to book apartments?',
                'datatype_id' => Datatype::where('name', 'integer')->pluck('id')->sole(),
            ],
            [
                'title' => 'Booking Cancellation Penalty',
                'content' => '10',
                'hint' => 'What percentage should be reserved for ' . config('app.name')
                    . ' upon booking cancellation?',
                'datatype_id' => Datatype::where('name', 'percentage')->pluck('id')->sole(),
            ],
            [
                'title' => 'Booking Cancellation Penalty for Landlord',
                'content' => '5',
                'hint' => 'What percentage should be reserved for the landlord upon booking cancellation?',
                'datatype_id' => Datatype::where('name', 'percentage')->pluck('id')->sole(),
            ],
        ];

        foreach ($systemData as $systemDatum) {
            $sd = SystemData::query()
                ->where('title', $systemDatum['title'])
                ->firstOrNew([]);
            $sd->title = $systemDatum['title'];
            $sd->content = ($sd->content == $systemDatum['content'] || $sd->content == null)
                ? $systemDatum['content']
                : $sd->content;
            $sd->datatype_id = $systemDatum['datatype_id'];
            $sd->hint = $systemDatum['hint'];
            $sd->save();
        }
    }
}
