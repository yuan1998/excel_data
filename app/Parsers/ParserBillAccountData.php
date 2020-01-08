<?php

namespace App\Parsers;

use Illuminate\Database\Eloquent\Builder;

class ParserBillAccountData
{
    public $query;

    public $originData;
    public $groupData;

    /**
     * ParserBillAccountData constructor.
     * @param Builder $query
     */
    public function __construct($query)
    {
        $this->query = $query;
        $this->getData();
        $this->getGroupData();
        $this->getAccount();
    }

    public function getData()
    {
        $this->originData = $this->query->get();
    }

    public function getGroupData()
    {
        $this->groupData = $this->originData->groupBy('pay_date');
    }

    public function getAccount()
    {
        $accountData = $this->groupData->map(function ($data) {
            $account = 0;
            foreach ($data as $item) {
                $account += (int)$item['order_account'];
            }
            return $account;
        });
    }

    public function toCount()
    {


    }


}
