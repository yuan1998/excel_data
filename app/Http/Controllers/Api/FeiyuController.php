<?php

namespace App\Http\Controllers\Api;

use App\Helpers;
use App\Http\Requests\UploadRequest;
use App\Imports\BaiduImport;
use App\Imports\FeiyuImport;
use App\Models\BaiduClue;
use App\Models\FeiyuData;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;


class FeiyuController extends Controller
{
    public $modelName = '\\App\\Models\\FeiyuData';

    public function uploadExcel(UploadRequest $request)
    {
        $type = $request->get('type');

        Excel::import(new FeiyuImport($type), $request->file('excel'));

        return $this->response->noContent();
    }


    public function checkItem(FeiyuData $feiyuData)
    {
        Helpers::checkIntentionAndArchive($feiyuData);

        return $this->response->array([
            'is_archive' => $feiyuData->is_archive,
            'intention'  => $feiyuData->intention,
        ]);
    }

    public function checkItemArchive(FeiyuData $feiyuData)
    {
        Helpers::checkIsArchive($feiyuData);

        return $this->response->array([
            'is_archive' => $feiyuData->is_archive
        ]);
    }

    public function checkItemIntention(FeiyuData $feiyuData)
    {
        Helpers::checkIntention($feiyuData);

        return $this->response->array([
            'intention' => $feiyuData->intention,
        ]);
    }
}
