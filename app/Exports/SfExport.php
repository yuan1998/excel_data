<?php

namespace App\Exports;


use App\Helpers;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SfExport implements FromCollection, WithTitle, WithHeadings, WithStrictNullComparison
{
    public $data;
    public $title;
    public $days;
    public $channelCount;
    public $headers;
    public $rows = 2;

    public $colorList = [
        'f8cbad',
        '8497b0',
        'a9d08e',
        'fed966',
    ];
    public $accountCount = 0;
    public $accountArr = [];

    /**
     * TestSheet constructor.
     * @param array  $data
     * @param string $title
     * @param array  $headers
     */
    public function __construct($data, $title, $headers)
    {
        $this->data    = $data;
        $this->title   = $title;
        $this->headers = $headers;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return $this->headers;
    }

    /**
     * @return Collection|array
     */
    public function collection()
    {

        $result = collect($this->data);
        return $result;
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return $this->title;
    }
}
