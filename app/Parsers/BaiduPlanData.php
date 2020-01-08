<?php

namespace App\Parsers;

use App\Models\AccountData;
use Carbon\Carbon;

class BaiduPlanData
{
    /**
     * @var \Illuminate\Database\Eloquent\Collection|static[]
     */
    public $accountData;


    /**
     * BaiduPlanData constructor.
     * @param $data
     */
    public function __construct($data)
    {
        $dates = $data['dates'];
        $type  = $data['type'];

        $startDate = Carbon::parse($dates[0])->toDateString();
        $endDate   = Carbon::parse($dates[1])->toDateString();

        $this->accountData = AccountData::query()
            ->with([
                'spendData'   => function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('date', [$startDate, $endDate]);
                }, 'formData' => function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('date', [$startDate, $endDate]);
                },
                'formData.formModel',
            ])
            ->where('type', $type)->where('channel_id', 3)->get();
    }


    public function mapToPlanData()
    {
        return $this->accountData->map(function ($data) {
            $spendData = $data->spendData->groupBy('date');
            $formData  = $data->formData->groupBy('date');

            $dateKeyword = [];
            foreach ($spendData as $dateString => $dateSpendData) {
                $codeArr = [];
                foreach ($dateSpendData as $item) {
                    preg_match("/(?<=-)[0-9]{6}$/", $item->account_keyword, $matches);
                    if ($matches && count($matches)) {
                        $code = $matches[0];
                        if (!isset($codeArr[$code])) {
                            $codeArr[$code] = [
                                'account_keyword' => [],
                                'spendData'       => [],
                                'formData'        => [],
                            ];
                        }
                        array_push($codeArr[$code]['account_keyword'], $item->account_keyword);
                        array_push($codeArr[$code]['spendData'], $item->toArray());
                    }
                }

                if (count($codeArr) && $dateFormData = $formData->get($dateString, null)) {
                    foreach ($dateFormData as $item) {
                        if ($item->model && $url = $item->model->dialog_url) {
                            foreach ($codeArr as $key => &$codeItem) {
                                if (preg_match("/\/{$key}/", $url)) {
                                    array_push($codeItem['formData'], $item->toArray());
                                    break;
                                }
                            }
                        }
                    }
                }
                $dateKeyword[$dateString] = $codeArr;
            }

            return [
                'accountName' => $data['name'],
                'data'        => $dateKeyword,
            ];
        });
    }

    protected function parserSpendData($spendData)
    {
        $spend     = 0;
        $off_spend = 0;
        foreach ($spendData as $spendItem) {
            $spend     += $spendItem['spend'];
            $off_spend += (float)$spendItem['spend'];
        }
        return [
            'spend'     => round($spend, 2),
            'off_spend' => round($off_spend, 2)
        ];
    }

    public function mapToExcelFieldArray()
    {
        $data = $this->mapToPlanData();

        return collect($data)->map(function ($item) {
            $item['count'] = $this->mapToCountField($item['data']);
            return $item;
        });

    }

    public function mapToCountField($data)
    {
        $result = collect();

        foreach ($data as $dateString => $dateItem) {
            foreach ($dateItem as $accountItem) {
                $spend     = $this->parserSpendData($accountItem['spendData']);
                $parseData = [
                    '日期'  => $dateString,
                    '计划名' => implode('\n', $accountItem['account_keyword']),
                    '虚消费' => $spend['spend'],
                    '实消费' => $spend['off_spend'],
                    '表单数' => count($accountItem['formData'])
                ];
                $result->push($parseData);
            }
        }

        return $result;
    }


}
