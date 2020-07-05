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

class ConsultantGroupExport implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize, WithStrictNullComparison,WithEvents
{

    /**
     * @var ParserStart
     */
    public $parser;

    public $colorList = [
        'f8cbad',
        '8497b0',
        'a9d08e',
        'fed966',
    ];

    public $headers = [
        '渠道'   => '',
        '网电客服' => '',
        '建档数据' => [
            '总表单数', '建档数', '重复建档', '未下单', '预约单总数', '一级预约', '二级预约', '三级预约', '四级预约', '五级预约', '一级占比', '二级占比', '三级占比', '四级占比', '五级占比'
        ],
        '到院数据' => [
            '新客首次', '新客二次', '老客', '到院总数', '新客首次到院率', '除去5级首次到院率', '除去4、5级首次到院率', '新客二次到院占比', '老客到院占比', '总到院率'
        ],
        '成交数据' => [
            '新客首次成交', '新客二次成交', '老客成交', '成交总数', '新客首次成交率', '新客二次成交率', '老客成交率', '总成交率'
        ],
        '业绩数据' => [
            '新客首次业绩', '新客二次业绩', '老客业绩', '业绩小计', '新客首次成交单体', '新客二次成交单体', '老客成交单体', '总成交单体', '新客首次挂号单体', '新客二次挂号单体', '老客挂号单体', '总挂号单体'
        ]
    ];
    private $data;
    /**
     * @var int
     */
    private $channels;
    private $consultantCount;
    private $rows = 2;


    /**
     * BaiduPlanExport constructor.
     * @param $parser ParserConsultantGroup
     */
    public function __construct($parser)
    {
        $this->parser = $parser;
        $this->data   = $parser->toArray();
    }


    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $index      = 1;
                $colorIndex = 0;
                foreach ($this->headers as $header => $value) {
                    $name = Helpers::getNameFromNumber($index);

                    if (is_array($value) && count($value) > 1) {
                        $index = count($value) + $index - 1;
                        $last  = Helpers::getNameFromNumber($index);
                        $event->sheet->getDelegate()->mergeCells("{$name}1:{$last}1");

                        $event->sheet->getDelegate()->getStyle("{$name}1:{$last}2")
                            ->applyFromArray([
                                'fill' => [
                                    'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                    'startColor' => [
                                        'rgb' => $this->colorList[$colorIndex],
                                    ]
                                ]
                            ]);
                        $colorIndex++;


                    } else {
                        $str = "{$name}1:{$name}2";

                        $event->sheet->getDelegate()->mergeCells($str);
                        $event->sheet->getDelegate()->getStyle($str)
                            ->applyFromArray([
                                'fill' => [
                                    'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                    'startColor' => [
                                        'rgb' => 'b4c6e7',
                                    ]
                                ]
                            ]);
                    }

                    $index++;
                }

                $name = Helpers::getNameFromNumber($index);
                $event->sheet->getDelegate()->getStyle("A1:{$name}{$this->rows}")->getAlignment()->setVertical('center');
                $event->sheet->getDelegate()->getStyle("A1:{$name}{$this->rows}")->getAlignment()->setHorizontal('center');

                $this->mergeDayCell($event);
                $this->setColumnsWidth($event);
                $event->sheet->getDelegate()->freezePaneByColumnAndRow(3, 3);
            },
        ];
    }


    public function mergeDayCell($event)
    {
        $start = 3;
        for ($i = $this->channels; $i > 0; $i--) {
            $str = "A{$start}:A" . ($start + $this->consultantCount - 1);
            $event->sheet->getDelegate()->mergeCells($str);

            $start = $start + $this->consultantCount;
        }
    }

    public function setColumnsWidth($event)
    {
//        $count = count($this->headings()[0]);

        for ($i = 0; $i <= $this->rows; $i++) {
            $event->sheet->getDelegate()->getRowDimension($i)->setRowHeight(25);
        }
//        for ($i = 1; $i <= $count; $i++) {
//            $event->sheet->getDelegate()->getColumnDimension(Helpers::getNameFromNumber($i))->setWidth(8);
//        }
    }


    /**
     * @return Collection
     */
    public function collection()
    {
        $result         = collect();
        $this->channels = $this->data->count();


        foreach ($this->data as $channelName => $channelData) {
            if (!$this->consultantCount) {
                $this->consultantCount = $channelData->count();
            }


            foreach ($channelData as $consultantName => $excel) {
                $this->rows++;
                $baseData = array_values($excel->toConsultantData());
                $values   = array_merge([
                    $channelName,
                    $consultantName,
                ], $baseData);
                $result->push($values);
            }
        }
        return $result;
        // TODO: Implement collection() method.
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return Helpers::makeHeaders($this->headers);
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return '咨询数据转化表';
    }
}
