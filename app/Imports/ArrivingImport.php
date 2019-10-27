<?php

namespace App\Imports;

use App\Helpers;
use App\Models\ArrivingData;
use App\Models\FeiyuData;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;

class ArrivingImport implements ToCollection
{
    public $type;

    /**
     * ArrivingImport constructor.
     * @param $type
     */
    public function __construct($type)
    {
        $this->type = $type;
    }


    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        $data = Helpers::excelToKeyArray($collection);

    }
}
