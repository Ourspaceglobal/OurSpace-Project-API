<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PopulateGeocodeForAddress implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     *
     * @param Model $model
     * @return void
     */
    public function __construct(public Model $model)
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $model = $this->model;

        if ($model->latitude && $model->longitude) {
            return;
        }

        if ($data = getGeocodeByAddress($model->full_address)) {
            $model->latitude = $data['latitude'];
            $model->longitude = $data['longitude'];
            $model->saveQuietly();
        }

        return;
    }
}
