<?php

namespace App\Parsers;

use Illuminate\Support\Collection;

class ParserToExcelData
{
    /**
     * @var Collection
     */
    public $formData;
    /**
     * @var Collection
     */
    public $arrivingData;
    /**
     * @var Collection
     */
    public $billAccountData;
    /**
     * @var Collection
     */
    public $spendData;

    public static $FormCountDataFormat = [
        'form_count'   => 0,
        'is_archive-0' => 0,
        'is_archive-1' => 0,
        'intention-0'  => 0,
        'intention-1'  => 0,
        'intention-2'  => 0,
        'intention-3'  => 0,
        'intention-4'  => 0,
        'intention-5'  => 0,
        'intention-6'  => 0,
    ];

    public static $SpendCountDataFormat = [
        'spend' => 0,
        'click' => 0,
        'show'  => 0,
    ];

    public static $ArrivingCountDataFormat = [
        'new_first'             => 0,
        'new_again'             => 0,
        'new_total'             => 0,
        'old'                   => 0,
        'count'                 => 0,
        'new_transaction'       => 0,
        'new_first_transaction' => 0,
        'new_again_transaction' => 0,
        'old_transaction'       => 0,
    ];

    public static $BillAccountCountDataFormat = [
        'total_account'     => 0,
        'new_account'       => 0,
        'new_again_account' => 0,
        'new_first_account' => 0,
        'old_account'       => 0,
    ];

    /**
     * ParserToExcelData constructor.
     * @param $formData
     * @param $arrivingData
     * @param $billAccountData
     * @param $spendData
     */
    public function __construct($formData, $arrivingData, $billAccountData, $spendData)
    {
        $this->formData        = $formData;
        $this->arrivingData    = $arrivingData;
        $this->billAccountData = $billAccountData;
        $this->spendData       = $spendData;

        $formData        = $this->parserFormData();
        $spendData       = $this->parserSpendData();
        $billAccountData = $this->parserBillAccountData();
        $arrivingData = $this->parserArrivingData();
        dd($arrivingData);
    }


    public function parserFormData()
    {
        $groupData = $this->formData->groupBy('date');
        return $groupData->map(function ($data) {
            $totalForm    = static::$FormCountDataFormat;
            $projectCount = $this->groupForProject($data)
                ->map(function ($item) use (&$totalForm) {
                    $result               = static::$FormCountDataFormat;
                    $result['form_count'] = count($item);

                    foreach ($item as $value) {
                        $phone = $value->phones->first();
                        $result["is_archive-{$phone['is_archive']}"]++;
                        $result["intention-{$phone['intention']}"]++;
                    }

                    $totalForm['form_count']   += $result['form_count'];
                    $totalForm['is_archive-0'] += $result['is_archive-0'];
                    $totalForm['is_archive-1'] += $result['is_archive-1'];
                    $totalForm['intention-0']  += $result['intention-0'];
                    $totalForm['intention-1']  += $result['intention-1'];
                    $totalForm['intention-2']  += $result['intention-2'];
                    $totalForm['intention-3']  += $result['intention-3'];
                    $totalForm['intention-4']  += $result['intention-4'];
                    $totalForm['intention-5']  += $result['intention-5'];
                    $totalForm['intention-6']  += $result['intention-6'];
                    return $result;
                });
            $projectCount->put('total', $totalForm);
            return $projectCount;
        });
    }

    public function parserSpendData()
    {
        $dateGroupData = $this->spendData->groupBy('date');
        $projectSpend  = $dateGroupData
            ->map(function ($data) {
                $totalResult = static::$SpendCountDataFormat;
                $result      = $this->groupForProject($data)
                    ->map(function ($item) use (&$totalResult) {
                        $result = static::$SpendCountDataFormat;
                        foreach ($item as $value) {
                            $result['spend'] += $value['spend'] ?? 0;
                            $result['click'] += $value['click'] ?? 0;
                            $result['show']  += $value['show'] ?? 0;
                        }

                        $totalResult['spend'] += $result['spend'];
                        $totalResult['click'] += $result['click'];
                        $totalResult['show']  += $result['show'];

                        return $result;
                    });

                $result->put('total', $totalResult);
                return $result;
            });

        return $projectSpend;
    }

    public function parserArrivingData()
    {
        $dateArrivingData = $this->arrivingData->groupBy('reception_date');
        return $dateArrivingData->map(function ($data) {
            $totalArriving = static::$ArrivingCountDataFormat;

            $result = $this->groupForProject($data)
                ->map(function ($item) use (&$totalArriving) {

                    $result          = static::$ArrivingCountDataFormat;
                    $result['count'] = count($item);

                    foreach ($item as $value) {
                        $transaction = $value['is_transaction'] == ' 是 ';

                        if ($value['customer_status'] == ' 新客户 ') {
                            if ($value['arriving_again'] == '二次') {
                                $result['new_again']++;
                                $transaction && $result['new_again_transaction']++;
                            } else {
                                $result['new_first']++;
                                $transaction && $result['new_first_transaction']++;
                            }
                        } else {
                            $result['old']++;
                            $transaction && $result['old_transaction']++;
                        }
                    }
                    $result['new_total']       = $result['new_first'] + $result['new_again'];
                    $result['new_transaction'] = $result['new_again_transaction'] + $result['new_first_transaction'];

                    $totalArriving['count']                 += $result['count'];
                    $totalArriving['old']                   += $result['old'];
                    $totalArriving['old_transaction']       += $result['old_transaction'];
                    $totalArriving['new_total']             += $result['new_total'];
                    $totalArriving['new_first']             += $result['new_first'];
                    $totalArriving['new_again']             += $result['new_again'];
                    $totalArriving['new_transaction']       += $result['new_transaction'];
                    $totalArriving['new_first_transaction'] += $result['new_first_transaction'];
                    $totalArriving['new_again_transaction'] += $result['new_again_transaction'];


                    return $result;

                });

            $result->put('total', $totalArriving);

            return $result;
        });

    }

    public function parserBillAccountData()
    {
        $dateBillAccount = $this->billAccountData->groupBy('pay_date');
        return $dateBillAccount->map(function ($data) {
            $totalBillAccount = static::$BillAccountCountDataFormat;

            $result = $this->groupForProject($data)
                ->map(function ($item, $key) use (&$totalBillAccount) {
                    $result = static::$BillAccountCountDataFormat;

                    foreach ($item as $value) {
                        $customerStatus          = $value['customer_status'];
                        $account                 = (float)($value['order_account'] ?? 0);
                        $result['total_account'] += $account;

                        if ($customerStatus) {
                            if ($customerStatus == ' 新客户 ') {
                                if ($value['arriving_again'] == '二次') {
                                    $result['new_again_account'] += $account;
                                } else {
                                    $result['new_first_account'] += $account;
                                }
                            } else {
                                $result['old_account'] += $account;
                            }
                        }
                    }
                    $result['new_account'] = $result['new_first_account'] + $result['new_again_account'];

                    $totalBillAccount['total_account']     += $result['total_account'];
                    $totalBillAccount['new_first_account'] += $result['new_first_account'];
                    $totalBillAccount['new_again_account'] += $result['new_again_account'];
                    $totalBillAccount['old_account']       += $result['old_account'];
                    $totalBillAccount['new_account']       += $result['new_account'];

                    return $result;
                });

            $result->put('total', $totalBillAccount);
            return $result;
        });
    }


    /**
     * @param Collection $data
     * @return Collection
     */
    public function groupForProject($data)
    {
        $projectData = [];
        $data->each(function ($item) use (&$projectData) {
            $project = $item->projects->first();
            $key     = $project->id . '-' . $project->title;

            if (!isset($projectData[$key])) {
                $projectData[$key] = [];
            }
            $projectData[$key][] = $item;
        });
        return collect($projectData);
    }


}
