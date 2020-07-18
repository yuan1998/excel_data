<?php

namespace App\Observers;

use App\Jobs\ExportJob;
use App\Models\ExportDataLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ExportDataLogObserver
{
    /**
     * Handle the export data log "created" event.
     *
     * @param \App\Models\ExportDataLog $exportDataLog
     * @return void
     */
    public function created(ExportDataLog $exportDataLog)
    {
        Log::info('生成 导出 Job', [
            'exportDataLog' => $exportDataLog
        ]);
        $queueName = 'data_exports';
        if ($exportDataLog['data_type'] === 'sanfang_data_excel')
            $queueName = 'sanfang_data_export';

        ExportJob::dispatch($exportDataLog->id)->onQueue($queueName);
    }

    /**
     * Handle the export data log "updated" event.
     *
     * @param \App\Models\ExportDataLog $exportDataLog
     * @return void
     */
    public function updated(ExportDataLog $exportDataLog)
    {
        //
    }

    /**
     * Handle the export data log "deleted" event.
     *
     * @param \App\Models\ExportDataLog $exportDataLog
     * @return void
     */
    public function deleted(ExportDataLog $exportDataLog)
    {
        Storage::disk('public')->delete($exportDataLog->path . $exportDataLog->file_name);
    }

    /**
     * Handle the export data log "restored" event.
     *
     * @param \App\Models\ExportDataLog $exportDataLog
     * @return void
     */
    public function restored(ExportDataLog $exportDataLog)
    {
        //
    }

    /**
     * Handle the export data log "force deleted" event.
     *
     * @param \App\Models\ExportDataLog $exportDataLog
     * @return void
     */
    public function forceDeleted(ExportDataLog $exportDataLog)
    {
        //
    }
}
