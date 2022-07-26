<?php

if (!function_exists('getGeocodeByAddress')) {
    function getGeocodeByAddress($address)
    {
        $apiKey = config('google.geocode_api_key');

        try {
            $response = \Illuminate\Support\Facades\Http::retry(2, 100)
                ->get("https://maps.googleapis.com/maps/api/geocode/json?address={$address}&key={$apiKey}");

            if ($response->successful()) {
                $data = json_decode($response->body());

                if ($data->status == 'OK') {
                    $location = $data->results[0]->geometry->location;

                    return [
                        'latitude' => $location->lat,
                        'longitude' => $location->lng,
                    ];
                }

                return false;
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }
}
