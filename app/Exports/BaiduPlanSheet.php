<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithTitle;

class BaiduPlanSheet implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize, WithStrictNullComparison
{
    public $data;

    /**
     * BaiduPlanSheet constructor.
     * @param $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }


    /**
     * @return Collection
     */
    public function collection()
    {
        $result     = collect();





        return $result;
        // TODO: Implement collection() method.
    }


    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            '日期',
            '计划名',
            '虚消',
            '实消',
            '数量',
        ];

        // TODO: Implement headings() method.
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return '计划报告';
    }
}
