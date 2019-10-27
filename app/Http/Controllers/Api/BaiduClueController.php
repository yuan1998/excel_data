<?php

namespace App\Http\Controllers\Api;

use App\Helpers;
use App\Models\BaiduClue;
use Illuminate\Http\Request;

class BaiduClueController extends Controller
{

    public function checkItem(BaiduClue $baiduClue)
    {
        Helpers::checkIntentionAndArchive($baiduClue, true);

        return $this->response->array([
            'is_archive'    => $baiduClue->is_archive,
            'intention'     => $baiduClue->intention,
            'has_dialog_id' => $baiduClue->has_dialog_id,
            'has_url'       => $baiduClue->has_url,
        ]);
    }

    public function checkItemArriving(BaiduClue $baiduClue)
    {
        Helpers::checkArriving($baiduClue);
        return $this->response->array([
            'intention'     => $baiduClue->intention,
            'is_archive'    => $baiduClue->is_archive,
            'arriving_type' => $baiduClue->arriving_type,
        ]);

    }

    public function checkItemArchive(BaiduClue $baiduClue)
    {
        Helpers::checkIsArchive($baiduClue);

        return $this->response->array([
            'is_archive' => $baiduClue->is_archive
        ]);
    }

    public function checkItemIntention(BaiduClue $baiduClue)
    {
        Helpers::checkIntention($baiduClue);

        return $this->response->array([
            'intention' => $baiduClue->intention,
        ]);
    }
}
