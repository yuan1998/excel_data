<?php

namespace App\Imports;

use App\Helpers;
use App\Models\BaiduData;
use App\Models\BaiduSpend;
use App\Models\DataOrigin;
use App\Models\FeiyuData;
use App\Models\FeiyuSpend;
use App\Models\KuaiShouData;
use App\Models\KuaiShouSpend;
use App\Models\MeiyouData;
use App\Models\MeiyouSpend;
use App\Models\VivoData;
use App\Models\VivoSpend;
use App\Models\WeiboFormData;
use App\Models\WeiboSpend;
use App\Models\YiliaoData;
use App\OppoSpend;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeSheet;

class AutoImport implements ToCollection, WithEvents
{

    public $count = 0;
    public $model = null;
    public $models = [];

    public $importFailLog = [];
    public $importSuccessLog = [];

    public static $modelType = [
        'baidu'          => BaiduData::class,
        'weibo'          => WeiboFormData::class,
        'feiyu'          => FeiyuData::class,
        'yiliao'         => YiliaoData::class,
        'vivo'           => VivoData::class,
        'kuaishou'       => KuaiShouData::class,
        'meiyou'         => MeiyouData::class,
        'meiyou-spend'   => MeiyouSpend::class,
        'kuaishou-spend' => KuaiShouSpend::class,
        'vivo-spend'     => VivoSpend::class,
        'baidu-spend'    => BaiduSpend::class,
        'feiyu-spend'    => FeiyuSpend::class,
        'weibo-spend'    => WeiboSpend::class,
        'oppo-spend'     => OppoSpend::class,
    ];

    public static $modelName = [
        'baidu'          => '快商通数据',
        'weibo'          => '微博表单数据',
        'feiyu'          => '飞鱼表单数据',
        'yiliao'         => '易聊数据',
        'vivo'           => 'vivo数据',
        'kuaishou'       => '快手表单数据',
        'meiyou'         => '美柚表单数据',
        'meiyou-spend'   => '美柚消费数据',
        'kuaishou-spend' => '快手表单数据',
        'vivo-spend'     => 'vivo消费数据',
        'baidu-spend'    => '百度消费数据',
        'feiyu-spend'    => '飞鱼消费数据',
        'weibo-spend'    => '微博消费数据',
        'oppo-spend'     => 'oppo消费数据',
    ];
    /**
     * @var string
     */
    public $sheetName;


    public static function checkExcelModel($data)
    {

        foreach (static::$modelType as $modelName => $model) {
            if (method_exists($model, 'isModel')) {
                if (call_user_func_array([$model, 'isModel'], [$data]))
                    return $modelName;
            }
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
        $modelClass = static::$modelType[$model];
        if (method_exists($modelClass, 'excelCollection'))
            return call_user_func_array([$modelClass, 'excelCollection'], [$data]);
        return null;
    }

    /**
     * @param Collection $collection
     * @throws \Exception
     */
    public function collection(Collection $collection)
    {
        $this->dataOriginCheck($collection);
    }

    /**
     * @param $collection Collection
     */
    public function dataOriginCheck($collection)
    {
        $dataOrigin = DataOrigin::validateExcelType($this->sheetName, $collection[0]);
        if ($dataOrigin) {
            $dataOrigin->makeFormData($collection);

            array_push($this->models, $dataOrigin['title']);
            $this->mergeLog($dataOrigin, ['importSuccessLog', 'importFailLog']);
        }
    }

    public function mergeLog($dataOrigin, $names)
    {
        foreach ($names as $name) {
            foreach ($dataOrigin->{$name} as $key => $value) {
                if (is_array($value)) {
                    if (!isset($this->{$name}[$key])) $this->{$name}[$key] = [];

                    $this->{$name}[$key] = array_merge($this->{$name}[$key], $value);
                } else if (is_int($value)) {

                    if (!isset($this->{$name}[$key])) $this->{$name}[$key] = 0;
                    $this->{$name}[$key] += $value;
                }
            }
        }
    }

    /**
     * @param $collection Collection
     * @throws \Exception
     */
    public function modelTypeCheck($collection)
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
        return $this->model ? Arr::get(static::$modelName, $this->model, null) : null;
    }

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function (BeforeSheet $event) {
                $this->sheetName = $event->getSheet()->getTitle();
            }
        ];
    }
}
