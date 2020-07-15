<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\DataOriginRequest;
use App\Models\DataOrigin;
use Illuminate\Http\Request;

class DataOriginController extends Controller
{

    public $modelName = '\\App\\Models\\DataOrigin';

    public function create(DataOriginRequest $request)
    {
        $data      = $request->only(['sheet_name', 'file_name', 'title', 'data_type', 'property_field', 'data_field', 'excel_snap']);
        $channelId = $request->get('channel_id');


        $item = DataOrigin::create($data);

        $item->channels()->sync($channelId);


        return $this->response->noContent();
    }

    public function change(Request $request, DataOrigin $model)
    {

        if (!$model) $this->response->errorBadRequest();

        $data = $request->only(['sheet_name', 'file_name', 'title', 'data_type', 'property_field', 'data_field', 'excel_snap']);

        $model->update($data);
        $channelId = $request->get('channel_id');

        if ($channelId && count($channelId)) {
            $model->channels()->sync($channelId);
        }

        return $this->response->noContent();
    }

}
