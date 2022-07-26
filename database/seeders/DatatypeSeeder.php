<?php

namespace Database\Seeders;

use App\Models\Datatype;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatatypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $datatypes = [
            [
                'id' => Str::orderedUuid()->toString(),
                'name' => 'string',
                'hint' => 'short text',
                'developer_hint' => 'text input',
                'rule' => 'string|max:191',
            ],
            [
                'id' => Str::orderedUuid()->toString(),
                'name' => 'datetime',
                'hint' => 'date and time',
                'developer_hint' => 'datetime (Y-m-d H:i:s)',
                'rule' => 'date_format:Y-m-d H:i:s',
            ],
            [
                'id' => Str::orderedUuid()->toString(),
                'name' => 'integer',
                'hint' => 'number',
                'developer_hint' => 'int',
                'rule' => 'numeric',
            ],
            [
                'id' => Str::orderedUuid()->toString(),
                'name' => 'boolean',
                'hint' => 'true or false; 0 or 1',
                'developer_hint' => 'boolean',
                'rule' => 'boolean',
            ],
            [
                'id' => Str::orderedUuid()->toString(),
                'name' => 'date',
                'hint' => 'date',
                'developer_hint' => 'date (Y-m-d)',
                'rule' => 'date_format:Y-m-d',
            ],
            [
                'id' => Str::orderedUuid()->toString(),
                'name' => 'time',
                'hint' => 'time',
                'developer_hint' => 'time (H:i:s)',
                'rule' => 'date_format:H:i:s',
            ],
            [
                'id' => Str::orderedUuid()->toString(),
                'name' => 'text',
                'hint' => 'text',
                'developer_hint' => 'text (max 1000 characters)',
                'rule' => 'string|max:1000',
            ],
            [
                'id' => Str::orderedUuid()->toString(),
                'name' => 'file',
                'hint' => 'file',
                'developer_hint' => 'file',
                'rule' => 'file',
            ],
            [
                'id' => Str::orderedUuid()->toString(),
                'name' => 'image',
                'hint' => 'image',
                'developer_hint' => 'image',
                'rule' => 'image|max:2000',
            ],
            [
                'id' => Str::orderedUuid()->toString(),
                'name' => 'date_after_today',
                'hint' => 'date after today',
                'developer_hint' => 'date after today',
                'rule' => 'date|after:today',
            ],
            [
                'id' => Str::orderedUuid()->toString(),
                'name' => 'date_before_today',
                'hint' => 'date before today',
                'developer_hint' => 'date before today',
                'rule' => 'date|before:today',
            ],
            [
                'id' => Str::orderedUuid()->toString(),
                'name' => 'longtext',
                'hint' => 'infinite text',
                'developer_hint' => 'text, no limit',
                'rule' => 'string',
            ],
            [
                'id' => Str::orderedUuid()->toString(),
                'name' => 'url',
                'hint' => 'website link',
                'developer_hint' => 'url',
                'rule' => 'url',
            ],
            [
                'id' => Str::orderedUuid()->toString(),
                'name' => 'percentage',
                'hint' => 'percentage',
                'developer_hint' => 'number between 0 and 100',
                'rule' => 'numeric|between:0,100',
            ],
        ];

        Datatype::query()->upsert(
            $datatypes,
            ['name'],
            ['hint', 'developer_hint', 'rule']
        );
    }
}
