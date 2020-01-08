<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class BaiduPlanExport implements WithMultipleSheets
{

    public $parser;

    /**
     * BaiduPlanExport constructor.
     * @param $parser
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
