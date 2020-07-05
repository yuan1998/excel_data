<?php

namespace App\Exports;

use App\Parsers\BaiduPlanData;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class BaiduPlanExport implements WithMultipleSheets
{

    /**
     * @var BaiduPlanData
     */
    public $parser;

    /**
     * BaiduPlanExport constructor.
     * @param $parser BaiduPlanData
     */
    public function __construct($parser)
    {
        $this->parser = $parser;
    }


    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];
        $data   = $this->parser->mapToExcelFieldArray();

        foreach ($data as $accountData) {
            $sheets[] = new BaiduPlanSheet($accountData);
        }

        return $sheets;
    }
}
