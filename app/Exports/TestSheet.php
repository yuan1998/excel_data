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

class TestSheet implements FromCollection, WithTitle, WithHeadings, WithEvents, ShouldAutoSize, WithStrictNullComparison
{
    public $data;
    public $title;
    public $days;
    public $projectsCount;

    /**
     * TestSheet constructor.
     * @param $data
     * @param $title
     */
    public function __construct($data, $title)
    {
        $this->data  = $data;
        $this->title = $title;
    }

    public function headings(): array
    {
        return [
            [
                '日期', '科室',
                '推广数据', '', '', '', '', '', '', '', '', '', '', '', '', '',
                '咨询数据', '', '', '', '', '', '',
                '院内数据', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '',
                '投产比', '',
            ],
            [
                '', '',
                '展现/曝光', '点击', '投入/消耗', '总表单', '有效表单', '无效对话', '点击率', '留表率', '投入/消耗占比', '有效对话占比', '点击成本', '表单成本', '转出成本', '到院成本',
                '建档', '未建档', '新客首次', '新客二次', '老客到院', '新客首次占比', '到院率',
                '新客首次成交', '新客二次成交', '老客成交', '总成交', '新客首次业绩', '新客二次业绩', '老客业绩', '总业绩', '业绩占比', '新客首次成交率', '新客二次成交率', '老客成交率', '总成交率', '新客首次单体', '新客二次单体', '老客单体', '总单体',
                '总', '新客'
            ],
        ];
    }

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $delegate = $event->sheet->getDelegate();

                $delegate->mergeCells('A1:A2');
                $delegate->mergeCells('B1:B2');
                $delegate->mergeCells('C1:P1');
                $delegate->mergeCells('Q1:W1');
                $delegate->mergeCells('X1:AN1');
                $delegate->mergeCells('AO1:AP1');
                $this->mergeDayCell($delegate);
            },
        ];
    }

    public function setColumnsWidth($delegate)
    {
        $count = count($this->headings()[0]);

//        Log::info('test Count', [Helpers::getNameFromNumber($count)]);
        for ($i = $count; $i > 0; $i--) {
            $delegate->getColumnDimension(Helpers::getNameFromNumber($i))->setWidth(88);
        }
    }

    public function mergeDayCell($delegate)
    {
        $count = ($this->days * $this->projectsCount) + 2;

        for ($i = $count; $i > 2; $i -= $this->projectsCount) {
            $start = $i - $this->projectsCount + 1;
            if ($start < 0) {
                break;
            }
            $str = "A{$start}:A{$i}";
            $delegate->mergeCells($str);
        }

    }

    /**
     * @return Collection|array
     */
    public function collection()
    {
        $result     = collect();
        $this->days = $this->data->count();

        $this->data->each(function ($dateData, $dateString) use ($result) {
            if (!$this->projectsCount) {
                $this->projectsCount = $dateData->count();
            }
            $dateData->each(function ($department, $departmentName) use ($result, $dateString) {
                $result->push([
                    $dateString,
                    $departmentName,
                    $department['show'],
                    $department['click'],
                    $department['spend'],
                    $department['form_count'],
                    $department['effective_form'],
                    $department['uneffective_form'],
                    $department['click_rate'],
                    $department['form_rate'],
                    '-',
                    $department['effective_form_rate'],
                    $department['click_spend'],
                    $department['form_spend'],
                    '-',
                    $department['arriving_spend'],
                    $department['archive_count'],
                    $department['un_archive_count'],
                    $department['new_first_arriving'],
                    $department['new_again_arriving'],
                    $department['old_arriving'],
                    $department['new_first_rate'],
                    $department['arriving_rate'],
                    $department['new_first_transaction'],
                    $department['new_again_transaction'],
                    $department['old_transaction'],
                    $department['total_transaction'],
                    $department['new_first_account'],
                    $department['new_again_account'],
                    $department['old_account'],
                    $department['total_account'],
                    '-',
                    $department['new_first_transaction_rate'],
                    $department['new_again_transaction_rate'],
                    $department['old_transaction_rate'],
                    $department['total_transaction_rate'],
                    $department['new_first_average'],
                    $department['new_again_average'],
                    $department['old_average'],
                    $department['total_average'],
                    $department['proportion_total'],
                    $department['proportion_new'],
                ]);

            });
        });

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
