<?php

namespace App\Exports;

use App\Helpers;
use App\Parsers\BaiduPlanData;
use App\Parsers\ParserConsultantGroup;
use App\Parsers\ParserStart;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class ReportExport implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize, WithStrictNullComparison
{
    public $data;

    /**
     * ReportExport constructor.
     * @param Collection $data
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
        $result = collect();

        $this->data->groupBy('二级分类')
            ->each(function ($item, $key) use ($result) {
                $sum = [
                    '新客' => '',
                    '老客' => '',
                    '合计' => '',
                ];

                collect($item)->groupBy('日期')
                    ->each(function ($dateData, $date) use ($result, $key, &$sum) {
                        $typeData = collect($dateData)->groupBy('新老客');

                        foreach ($sum as $k => $v) {
                            $data = $typeData->get($k);

                            $arrivingCount = $data->sum('门诊量');
                            $billCount     = $data->sum('成交量');
                            $total         = $data->sum(function ($item) {
                                return (float)str_replace(',', '', $item['成交金额']);
                            });

                            $result->push([
                                $key,
                                $date,
                                $k,
                                $arrivingCount,
                                $billCount,
                                $total
                            ]);
                        }
                    });
            });
        return $result;
    }


    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            '二级分类',
            '日期',
            '新老客',
            '到诊量',
            '成交量',
            '成交金额',
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'sheet1';
    }
}
