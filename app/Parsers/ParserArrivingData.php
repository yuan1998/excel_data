<?php

namespace App\Parsers;

use App\Helpers;
use App\Models\Type;
use Illuminate\Support\Facades\DB;

class ParserArrivingData
{
    public static $customer_type = [
        1 => '新客首次',
        2 => '新客二次',
        0 => '老客',
    ];


    public $query;
    public $type;
    public $types;
    public $originData;
    public $filterData;
    public $uniqueData;

    /**
     * ParserArrivingData constructor.
     * @param $query
     */
    public function __construct($query)
    {
        $this->query = $query;

        $this->getData();
        $this->filterOriginData();
        $this->parserData();
    }

    public function getData()
    {
        $this->originData = $this->query
            ->select(
                DB::raw('CASE WHEN is_transaction = " 是 " THEN 1 ELSE 0 END AS is_transaction'),
                'customer_id',
                'reception_date',
                DB::raw('CASE WHEN customer_status = " 新客户 " AND again_arriving = "首次" THEN 1 WHEN customer_status = " 新客户 " AND again_arriving = "二次" THEN 2 ELSE 0 END AS customer_type'),
                DB::raw('CASE 
                WHEN intention like "%一级%" THEN 1
                WHEN intention like "%二级%" THEN 2
                WHEN intention like "%三级%" THEN 3
                WHEN intention like "%四级%" THEN 4
                WHEN intention like "%五级%" THEN 5
                ELSE 0 END AS intention')
            )
            ->get();
    }

    public function filterOriginData()
    {
        $this->filterData = $this->originData->groupBy('reception_date');
    }

    public function parserData()
    {
        $this->uniqueData = $this->filterData->map(function ($data) {
            $data = $data->unique('customer_id');
            return $data;
        });
    }

    public function toCountData()
    {
        return $this->uniqueData->map(function ($data) {
            $result = [
                'total'                 => $data->count(),
                'new_first_transaction' => 0,
                'new_again_transaction' => 0,
                'new_first'             => 0,
                'new_again'             => 0,
                'old'                   => 0,
                'old_transaction'       => 0,
            ];
            foreach ($data as $item) {
                $transaction = $item['is_transaction'];
                if (!$item['customer_type']) {
                    $result['old']++;
                    $transaction && $result['old_transaction']++;
                } else {
                    if ($item['customer_type'] == 1) {
                        $result['new_first']++;
                        $transaction && $result['new_first_transaction']++;
                    } else {
                        $result['new_again']++;
                        $transaction && $result['new_again_transaction']++;
                    }
                }
            }

            $result['new_total']         = $result['new_first'] + $result['new_again'];
            $result['new_transaction']   = $result['new_first_transaction'] + $result['new_again_transaction'];
            $result['transaction_total'] = $result['new_transaction'] + $result['old_transaction'];

            return $result;
        });
    }


}
