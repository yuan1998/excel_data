<?php

namespace App\Admin\Controllers;

use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Encore\Admin\Media\MediaManager;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class MediaController extends Controller
{

    public function initVue()
    {
        Admin::script(<<<EOF
        const app = new Vue({
            el: '#app'
        });
EOF
        );
    }

    public static function permissionList()
    {
        return [
            'delete' => \App\Http\Controllers\Api\MediaController::hasDeletePermission()
        ];
    }

    public function index(Request $request)
    {

        $this->initVue();
        return Admin::content(function (Content $content) use ($request) {
            $content->header('Media manager');

            $path        = $request->get('path', '/');
            $view        = $request->get('view', 'table');
            $permissions = static::permissionList();

            $manager = new MediaManager($path);

//            dd($manager->ls());
            $content->body(view("admin.media.vue-list", [
                'list'        => $manager->ls(),
                'nav'         => $manager->navigation(),
                'url'         => $manager->urls(),
                'csrf'        => csrf_token(),
                'permissions' => $permissions,
                'router'      => [
                    'media-index' => route('media-index'),
                ]
            ]));
        });
    }

    public function download(Request $request)
    {
        $file = $request->get('file');

        $manager = new MediaManager($file);

        return $manager->download();
    }

    public function upload(Request $request)
    {
        $files = $request->file('files');
        $dir   = $request->get('dir', '/');

        $manager = new MediaManager($dir);

        try {
            if ($manager->upload($files)) {
                admin_toastr(trans('admin.upload_succeeded'));
            }
        } catch (\Exception $e) {
            admin_toastr($e->getMessage(), 'error');
        }

        return back();
    }

    public function delete(Request $request)
    {
        $files = $request->get('files');

        $manager = new MediaManager();

        try {
            if ($manager->delete($files)) {
                return response()->json([
                    'status'  => true,
                    'message' => trans('admin.delete_succeeded'),
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status'  => true,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function move(Request $request)
    {
        $path = $request->get('path');
        $new  = $request->get('new');

        $manager = new MediaManager($path);

        try {
            if ($manager->move($new)) {
                return response()->json([
                    'status'  => true,
                    'message' => trans('admin.move_succeeded'),
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status'  => true,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function newFolder(Request $request)
    {
        $dir  = $request->get('dir');
        $name = $request->get('name');

        $manager = new MediaManager($dir);

        try {
            if ($manager->newFolder($name)) {
                return response()->json([
                    'status'  => true,
                    'message' => trans('admin.move_succeeded'),
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status'  => true,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
