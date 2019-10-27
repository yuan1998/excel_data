<?php

namespace App\Parsers;

use App\Helpers;
use App\Models\ArrivingData;
use App\Models\BillAccountData;
use App\Models\Channel;
use App\Models\DepartmentType;
use App\Models\FormData;
use App\Models\SpendData;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ParserChannel
{
    public $channelItem;
    public $dates;

    /**
     * @var \Illuminate\Database\Eloquent\Collection|static[]
     */
    public $departments;

    /**
     * @var \Illuminate\Database\Eloquent\Collection|static[]
     */
    public $mediums;

    /**
     * @var array
     */
    public $formTypes;
    /**
     * @var \Illuminate\Database\Eloquent\Collection|static[]
     */
    public $arrivingData;

    /**
     * @var \Illuminate\Database\Eloquent\Collection|static[]
     */
    public $billAccountData;
    /**
     * @var \Illuminate\Database\Eloquent\Collection|static[]
     */
    public $formData;
    /**
     * @var \Illuminate\Database\Eloquent\Collection|static[]
     */
    public $spendData;
    /**
     * @var \Illuminate\Support\Collection|static[]
     */
    public $departmentData;
    /**
     * @var ParserStart
     */
    public $base;

    /**
     * BaseParserChannel constructor.
     * @param             $channel
     * @param ParserStart $base
     */
    public function __construct($channel, $base)
    {
        $this->channelItem = $channel;
        $this->base        = $base;
    }

    public function getTitle()
    {
        return $this->channelItem->title;
    }


    /**
     * @return ParserChannel[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getMediums()
    {
        if (!$this->mediums) {
            $this->mediums = $this->channelItem->mediums;
        }
        return $this->mediums;
    }

    /**
     * @return Collection
     */
    public function getMediumsId()
    {
        return $this->getMediums()->pluck('id');
    }

    /**
     * @return Collection
     */
    public function getFormType()
    {
        return collect(explode(',', $this->channelItem->form_type ?? ''));
    }

    public function getDepartmentId()
    {
        return $this->departments->map(function ($item) {
            return $item->id;
        })->unique()->toArray();

    }

    public function dataGroup()
    {
        return [
            'arrivingData'    => $this->getArrivingData(),
            'formData'        => $this->getFormData(),
            'spendData'       => $this->getSpendData(),
            'billAccountData' => $this->getBillAccountData(),
        ];
    }

    /**
     * @return ParserBase[]|ParserChannel[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|Collection|null
     */
    public function getArrivingData()
    {
        if (!$this->arrivingData) {
            $arrivingData       = $this->base->arrivingData;
            $this->arrivingData = $arrivingData->filter(function ($arriving) {
                return $this->getMediumsId()->contains($arriving->medium_id);
            });
        }
        return $this->arrivingData;
    }

    /**
     * @return ParserBase[]|ParserChannel[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|Collection|null
     */
    public function getBillAccountData()
    {
        if (!$this->billAccountData) {
            $billAccountData       = $this->base->billAccountData;
            $this->billAccountData = $billAccountData->filter(function ($data) {
                return $this->getMediumsId()->contains($data->medium_id);
            });
        }
        return $this->billAccountData;
    }

    /**
     * @return ParserBase[]|ParserChannel[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|Collection|null
     */
    public function getFormData()
    {
        if (!$this->formData) {
            $formData       = $this->base->formData;
            $this->formData = $formData->filter(function ($data) {
                return $this->getFormType()->contains($data->form_type);
            });
        }
        return $this->formData;
    }

    /**
     * @return ParserBase[]|ParserChannel[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|Collection|null
     */
    public function getSpendData()
    {
        if (!$this->spendData) {
            $spendData       = $this->base->spendData;
            $this->spendData = $spendData->filter(function ($data) {
                return $this->getFormType()->contains($data->spend_type);
            });
        }
        return $this->spendData;
    }

}
