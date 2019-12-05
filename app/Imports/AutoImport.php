<?php

namespace App\Imports;

use App\Helpers;
use App\Models\BaiduData;
use App\Models\BaiduSpend;
use App\Models\FeiyuData;
use App\Models\FeiyuSpend;
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
        'baidu-spend' => '百度消费数据',
        'feiyu-spend' => '飞鱼消费数据',
        'weibo-spend' => '微博消费数据',
        'oppo-spend'  => 'oppo消费数据',
    ];

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
        }
        return null;
    }

    /**
     * @param Collection $collection
     * @throws \Exception
     */
    public function collection(Collection $collection)
    {
        $model = Helpers::checkExcelModel($collection);
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
        return $model && isset(static::$modelType[$model]) ? static::$modelType[$model] : '没有匹配';
    }
}
