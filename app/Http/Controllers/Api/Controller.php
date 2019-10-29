<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;
use Dingo\Api\Routing\Helpers;
use Illuminate\Support\Facades\Auth;

class Controller extends BaseController
{
    use Helpers;

    public $modelName;

    public function weiboUser()
    {
        return Auth::guard('weibo')->user();
    }

    protected function respondWithToken($token, $guardName = 'weibo')
    {
        return $this->response->array([
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'expires_in'   => Auth::guard($guardName)->factory()->getTTL() * 60
        ]);
    }

    public function index(Request $request)
    {
        $model      = $this->modelName;
        $page_count = $request->get('page_count', 15);
        $data       = $model::query()->paginate($page_count);
        return $this->response->array($data);
    }

    /**
     * @param Request $request
     * @param         $model
     * @return mixed
     */
    public function update(Request $request, $model)
    {

        $model = $this->modelName::find($model);
        if (!$model) $this->response->errorBadRequest();

        $data = $request->all();
        $model->update($data);
        return $this->response->array($model);
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $item = $this->modelName::create($data);
        return $this->response->array($item);
    }

    public function destroy($ids)
    {
        return $this->modelName::destroy(explode(',', $ids));
    }

}
