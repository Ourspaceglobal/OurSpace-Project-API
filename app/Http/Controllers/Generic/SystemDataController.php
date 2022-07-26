<?php

namespace App\Http\Controllers\Generic;

use App\Http\Controllers\Controller;
use App\Models\SystemData;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;

class SystemDataController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @param \App\Models\SystemData $systemData
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show(mixed $systemData)
    {
        $systemData = SystemData::query()
            ->select(['title', 'content', 'updated_at'])
            ->where('title', $systemData)
            ->firstOrFail();

        return ResponseBuilder::asSuccess()
            ->withMessage('Here you go')
            ->withData([
                'system_data' => $systemData,
            ])
            ->build();
    }
}
