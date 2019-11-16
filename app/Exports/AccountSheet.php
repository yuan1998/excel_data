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

class AccountSheet implements FromCollection, WithTitle, WithHeadings, WithEvents, WithStrictNullComparison
{
    public $data;
    public $title;
    public $days;
    public $channelCount;
    public $headers;


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
     * @param Collection $data
     * @param string     $title
     * @param array      $headers
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
        return Helpers::makeHeaders($this->headers);
    }


    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getDelegate()->getStyle("A1:BJ1234")->getAlignment()->setVertical('center');
                $event->sheet->getDelegate()->getStyle("A1:BJ1234")->getAlignment()->setHorizontal('center');
                for ($i = 0; $i <= 1265; $i++) {
                    $event->sheet->getDelegate()->getRowDimension($i)->setRowHeight(25);
                }

                $index      = 1;
                $colorIndex = 0;
                foreach ($this->headers as $header => $value) {
                    $name   = Helpers::getNameFromNumber($index);
                    $second = 3 + $this->accountCount + 1;

                    if (is_array($value) && count($value) > 1) {
                        $index = count($value) + $index - 1;
                        $last  = Helpers::getNameFromNumber($index);
                        $event->sheet->getDelegate()->mergeCells("{$name}1:{$last}1");
                        $event->sheet->getDelegate()->mergeCells("{$name}{$second}:{$last}{$second}");

                        $event->sheet->getDelegate()->getStyle("{$name}1:{$last}2")
                            ->applyFromArray([
                                'fill' => [
                                    'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                    'startColor' => [
                                        'rgb' => $this->colorList[$colorIndex],
                                    ]
                                ]
                            ]);
                        $event->sheet->getDelegate()->getStyle("{$name}{$second}:{$last}" . ($second + 1))
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
                        $str  = "{$name}1:{$name}2";
                        $str2 = "{$name}{$second}:{$name}" . ($second + 1);

                        $event->sheet->getDelegate()->mergeCells($str);
                        $event->sheet->getDelegate()->mergeCells($str2);
                        $event->sheet->getDelegate()->getStyle($str)
                            ->applyFromArray([
                                'fill' => [
                                    'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                    'startColor' => [
                                        'rgb' => 'b4c6e7',
                                    ]
                                ]
                            ]);
                        $event->sheet->getDelegate()->getStyle($str2)
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
                $this->mergeDayCell($event);
                $this->setColumnsWidth($event);
                $event->sheet->getDelegate()->freezePaneByColumnAndRow(4 ,6 + $this->accountCount);

            },
        ];
    }

    public function setColumnsWidth($event)
    {
        $count = count($this->headings()[0]);

//        Log::info('test Count', [Helpers::getNameFromNumber($count)]);
        for ($i = 1; $i <= $count; $i++) {
            $event->sheet->getDelegate()->getColumnDimension(Helpers::getNameFromNumber($i))->setWidth(15);
        }
    }

    public function mergeDayCell($event)
    {
        $start        = 3;
        $channelStart = 3;
        $first        = true;
        for ($i = $this->days; $i > 0; $i--) {
            $str = "A{$start}:A" . ($start + $this->accountCount - 1);
            $event->sheet->getDelegate()->mergeCells($str);
            $start = $start + $this->accountCount;

            foreach ($this->accountArr as $value) {
                $accountStr = "B{$channelStart}:B" . ($channelStart + $value - 1);
                $event->sheet->getDelegate()->mergeCells($accountStr);
                $channelStart = $channelStart + $value;
            }

            if ($first) {
                $first        = false;
                $start        += 3;
                $channelStart += 3;
            }

        }

    }

    /**
     * @return Collection|array
     */
    public function collection()
    {
        $result     = collect();
        $this->days = $this->data->count();

        $first = true;
        foreach ($this->data as $dateString => $dateData) {
            if (!$this->channelCount) {
                $this->channelCount = $dateData->count();
            }

            foreach ($dateData as $channelName => $channelData) {
                if ($first) {
                    $this->accountCount += $channelData->count();
                    array_push($this->accountArr, $channelData->count());
                }

                foreach ($channelData as $accountName => $accountData) {
                    $baseData = array_values($accountData->toBaseExcel());
                    $values   = array_merge([
                        $dateString,
                        $channelName,
                        $accountName,
                    ], $baseData);
                    $result->push($values);
                }
            }
            if ($first) {
                $result->push(['']);
                $result = $result->merge(Helpers::makeHeaders($this->headers));
                $first  = false;
            }
        }
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
