<?php

namespace App\Imports;

use App\Helpers;
use App\Models\BaiduSpend;
use App\OppoSpend;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class OppoSpendImport implements ToCollection
{

    public $count = 0;

    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        $data = Helpers::excelToKeyArray($collection, OppoSpend::$excelFields);

        $this->count = OppoSpend::handleExcelData($data);
    }
}
