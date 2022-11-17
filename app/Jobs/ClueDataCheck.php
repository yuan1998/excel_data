<?php

namespace App\Jobs;

use App\Models\FormDataPhone;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ClueDataCheck implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public $model;
    public $type;
    public $isBaidu;
    public $timeout = 120;

    /**
     * Create a new job instance.
     * @param $model
     */
    public function __construct($model)
    {
        $this->model = $model;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $model = $this->model;
        if ($model && $model = FormDataPhone::find($this->model)) {
            $model->checkCrmInfo();
        }
    }
}
