<?php

namespace App\Jobs;

use App\Helpers;
use App\Models\BaiduClue;
use App\Models\FormDataPhone;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class ClueDataCheck implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public $model;
    public $type;
    public $isBaidu;
    public $timeout = 0;

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
            Helpers::checkIntentionAndArchive($model, $model->isBaidu);
            Redis::del($model->getTable() . '_' . $model->id . '_queue_clue_loading');
        }
    }
}
