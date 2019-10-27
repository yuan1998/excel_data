<?php

namespace App\Exports;

use App\Parsers\ParserStart;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Events\AfterSheet;

class TestExport implements WithMultipleSheets
{
    use Exportable;
    /**
     * @var ParserStart
     */
    public $parser;

    /**
     * TestExport constructor.
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

        if (count($this->parser->channels_id) != 1) {
            $allData  = $this->parser->allDataExcelData(true);
            $sheets[] = new TestSheet($allData['total'], '总-汇总');
            foreach ($allData['total_department'] as $key => $department) {
                $sheets[] = new TestSheet($department, '总-' . $key);
            }
        }


        $channelData = $this->parser->channelDataExcelData(true);

        foreach ($channelData as $channelName => $channel) {
            $sheets[] = new TestSheet($channel['total'], $channelName . '-汇总');
            foreach ($channel['total_department'] as $key => $department) {
                $sheets[] = new TestSheet($department, $channelName . '-' . $key);
            }
        }


        return $sheets;
    }
}
