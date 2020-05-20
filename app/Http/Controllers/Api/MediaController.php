<?php

namespace App\Http\Controllers\Api;

use Encore\Admin\Facades\Admin;
use Encore\Admin\Media\MediaManager;
use Illuminate\Http\Request;

class MediaController extends Controller
{

    public static function hasDeletePermission()
    {
        return Admin::user()->isRole('administrator') || Admin::user()->can('media-delete');
    }

    public static function toData($path)
    {
        $manager = new MediaManager($path);

        return [
            'list' => $manager->ls(),
            'nav'  => $manager->navigation(),
            'url'  => $manager->urls(),
        ];
    }

    public function list(Request $request)
    {
        $path = $request->get('path', '/');

        return $this->response->array(static::toData($path));
    }

    public function upload(Request $request)
    {
        $files = $request->file('files');
        $dir   = $request->get('dir', '/');
        if (!is_array($files)) {
            $files = [$files];
        }

        $manager = new MediaManager($dir);

        try {
            if ($manager->upload($files)) {
                return $this->response->noContent();
            }
        } catch (\Exception $e) {
            $this->response->error($e->getMessage(), 404);
        }

    }

    public function move(Request $request)
    {
        $path = $request->get('path');
        $new  = $request->get('new');

        $manager = new MediaManager($path);

        try {
            if ($manager->move($new)) {
                return $this->response->noContent();

            }
        } catch (\Exception $e) {
            $this->response->error($e->getMessage(), 404);
        }
    }

    public function delete(Request $request)
    {
//        if (!static::hasDeletePermission()) {
//            $this->response->errorUnauthorized();
//        }

        $files   = $request->get('files');
        $manager = new MediaManager();

        try {
            if ($manager->delete($files)) {
                return $this->response->noContent();

            }
        } catch (\Exception $e) {
            $this->response->error($e->getMessage(), 404);
        }

    }

    public function makeFolder(Request $request)
    {

        $dir  = $request->get('dir');
        $name = $request->get('name');

        $manager = new MediaManager($dir);

        try {
            if ($manager->newFolder($name)) {
                return $this->response->noContent();
            }
        } catch (\Exception $e) {
            $this->response->error($e->getMessage(), 404);
        }


    }

}
