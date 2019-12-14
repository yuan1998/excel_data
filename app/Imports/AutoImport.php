<?php

namespace App\Imports;

use App\Helpers;
use App\Models\BaiduData;
use App\Models\BaiduSpend;
use App\Models\FeiyuData;
use App\Models\FeiyuSpend;
use App\Models\VivoData;
use App\Models\VivoSpend;
use App\Models\WeiboFormData;
use App\Models\WeiboSpend;
use App\Models\YiliaoData;
use App\OppoSpend;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class AutoImport implements ToCollection
{

    public $count = 0;
    public $model = null;

    public static $modelType = [
        'baidu'       => '快商通数据',
        'weibo'       => '微博表单数据',
        'feiyu'       => '飞鱼表单数据',
        'yiliao'      => '易聊数据',
        'vivo'        => 'vivo数据',
        'vivo-spend'  => 'vivo消费数据',
        'baidu-spend' => '百度消费数据',
        'feiyu-spend' => '飞鱼消费数据',
        'weibo-spend' => '微博消费数据',
        'oppo-spend'  => 'oppo消费数据',
    ];


    public static function checkExcelModel($data)
    {
        if (FeiyuData::isModel($data)) {
            return 'feiyu';
        }
        if (BaiduData::isModel($data)) {
            return 'baidu';
        }

        if (FeiyuSpend::isModel($data)) {
            return 'feiyu-spend';
        }
        if (BaiduSpend::isModel($data)) {
            return 'baidu-spend';
        }

        if (YiliaoData::isModel($data)) {
            return 'yiliao';
        }
        if (VivoData::isModel($data)) {
            return 'vivo';
        }
        if (OppoSpend::isModel($data)) {
            return 'oppo-spend';
        }
        if (VivoSpend::isModel($data)) {
            return 'vivo-spend';
        }
        if (WeiboSpend::isModel($data)) {
            return 'weibo-spend';
        }
        if (WeiboFormData::isModel($data)) {
            return 'weibo';
        }
    }


    /**
     * @param string     $model
     * @param Collection $data
     * @return int|null
     * @throws \Exception
     */
    public static function excelModel($model, $data)
    {
        switch ($model) {
            case 'baidu':
                // pass
                return BaiduData::excelCollection($data);
            case 'weibo':
                // pass
                return WeiboFormData::excelCollection($data);
            case 'feiyu':
                // pass
                return FeiyuData::excelCollection($data);
            case 'yiliao':
                // pass
                return YiliaoData::excelCollection($data);
            case 'vivo':
                // pass
                return VivoData::excelCollection($data);
            case 'baidu-spend':
                // pass
                return BaiduSpend::excelCollection($data);
            case 'feiyu-spend':
                // pass
                return FeiyuSpend::excelCollection($data);
            case 'weibo-spend':
                // pass
                return WeiboSpend::excelCollection($data);
            case 'oppo-spend':
                // pass
                return OppoSpend::excelCollection($data);
            case 'vivo-spend':
                // pass
                return VivoSpend::excelCollection($data);
        }
        return null;
    }

    /**
     * @param Collection $collection
     * @throws \Exception
     */
    public function collection(Collection $collection)
    {
        $model = static::checkExcelModel($collection);

        if (!$this->model && isset(static::$modelType[$model])) {
            $this->model = $model;
        }

        if ($this->model && $model === $this->model) {
            $this->count += static::excelModel($this->model, $collection);
        }
    }

    public function getModelType()
    {
        $model = $this->model;
        return $model && isset(static::$modelType[$model]) ? static::$modelType[$model] : null;
    }
}
