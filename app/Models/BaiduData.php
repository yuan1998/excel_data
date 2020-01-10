<?php

namespace App\Models;

use App\Helpers;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class BaiduData extends Model
{
    public static $excelFields = [
        "本次访问时间"   => 'cur_access_time',
        "来源省市"     => 'city',
        "对话类型"     => 'dialog_type',
        "访客名称"     => 'visitor_name',
        "客户类型"     => 'visitor_type',
        "初始接待客服"   => 'first_customer',
        "本次最初访问网页" => 'first_url',
        "关键词"      => 'keyword',
        "来源ip"     => 'ip',
        "来源网页"     => 'url',
        "会话ID"     => 'dialog_id',
        "访客ID"     => 'visitor_id',
        "首次访问时间"   => 'first_access_date',
        "上次访问时间"   => 'previous_access_date',
        "开始对话时间"   => 'start_dialog_date',
        "所有关键词"    => 'all_keyword',
        "搜索引擎"     => 'search_engine',
        "对话网址"     => 'dialog_url',
        "对话关键词"    => 'dialog_keyword',
        "竞价词"      => 'bidding_keyword',
        "站点"       => 'site',
        "线索"       => 'clue',
        "数据类型"     => 'type',
    ];

    protected $fillable = [
        'cur_access_time',
        'city',
        'dialog_type',
        'visitor_name',
        'visitor_type',
        'first_customer',
        'first_url',
        'keyword',
        'ip',
        'url',
        'dialog_id',
        'visitor_id',
        'first_access_date',
        'previous_access_date',
        'start_dialog_date',
        'all_keyword',
        'search_engine',
        'dialog_url',
        'dialog_keyword',
        'bidding_keyword',
        'site',
        'type',
        'form_type',
        'department_id',
        'code',
    ];

    public static $ChannelCategory = [
        'A10' => '百度竞价',
        'A20' => '搜狗',
        'A30' => '神马',
        'A60' => '百度信息流',
        'A8'  => 'oppo',
    ];


    public static function checkCodeIs($str)
    {
        if (!$str) return null;

        if (preg_match('/A6/', $str)) {
            return 1;
        }
        if (preg_match('/A30/', $str)) {
            return 7;
        }
        if (preg_match('/A20/', $str)) {
            return 6;
        }
        if (preg_match('/A10/', $str)) {
            return 5;
        }
        if (preg_match('/A8/', $str)) {
            return 8;
        }

    }


    public function clues()
    {
        return $this->hasMany(BaiduClue::class, 'baidu_id', 'id');
    }

    /**
     * 关联 项目
     * @return MorphToMany
     */
    public function projects()
    {
        return $this->morphToMany(ProjectType::class, 'model', 'project_list', 'model_id', 'project_id');
    }

    /**
     * 关联 科室
     * @return BelongsTo
     */
    public function department()
    {
        return $this->belongsTo(DepartmentType::class, 'department_id', 'id');

    }

    /**
     * @param string $clue
     * @return Collection
     */
    public static function parseClue(string $clue)
    {
        return collect(explode(',', $clue))
            ->filter(function ($value) {
                return Helpers::validatePhone($value);
            })
            ->map(function ($value) {
                return $value;
            });
    }

    /**
     * @param array $item
     * @return array|null
     * @throws \Exception
     */
    public static function parserData($item)
    {
        $item['url']        = substr($item['url'] ?? '', 0, Builder::$defaultStringLength);
        $item['first_url']  = substr($item['first_url'] ?? '', 0, Builder::$defaultStringLength);
        $item['dialog_url'] = substr($item['dialog_url'] ?? '', 0, Builder::$defaultStringLength);
        $item['date']       = $item['cur_access_time'] = Carbon::parse($item['cur_access_time'])->toDateString();

        $url = urldecode($item['dialog_url']);
        preg_match("/\?A[0-9](.{12,20})/", $url, $match);
        $code = $item['code'] = (isset($match[0]) ? $match[0] : '') . '-' . $item['visitor_type'];

        $item['form_type'] = BaiduData::checkCodeIs($code);
        if (!$item['form_type']) return null;


        if (!$departmentType = Helpers::checkDepartment($code)) {
            Log::info('无法判断科室', [
                'name' => $code,
            ]);
            throw new \Exception('无法判断科室: ' . $code);
        }
        $item['type']            = $departmentType->type;
        $item['department_id']   = $departmentType->id;
        $item['department_type'] = $departmentType;
        $item['project_type']    = Helpers::checkDepartmentProject($departmentType, $code);

        return $item;
    }

    /**
     * @param $data
     * @return int
     * @throws \Exception
     */
    public static function excelCollection($data)
    {
        $originData = Helpers::excelToKeyArray($data, static::$excelFields);
        $data       = collect($originData)->filter(function ($item) {
            return isset($item['dialog_url'])
                && isset($item['cur_access_time'])
                && isset($item['visitor_name'])
                && isset($item['visitor_id'])
                && $item['dialog_url'];
        });

        return static::handleExcelData($data);
    }

    /**
     * @param $data
     * @return int
     * @throws \Exception
     */
    public static function handleExcelData($data)
    {
        $count = 0;
        foreach ($data as $item) {
            $item = static::parserData($item);
            if (!$item) continue;

            $baidu = static::updateOrCreate([
                'visitor_id' => $item['visitor_id']
            ], $item);
            $baidu->projects()->sync($item['project_type']);


            $clue = static::parseClue($item['clue']);

            if (in_array($baidu['form_type'], [1, 8]) && $clue->isNotEmpty()) {
                $form = FormData::updateOrCreate([
                    'model_id'   => $baidu->id,
                    'model_type' => static::class,
                ], FormData::parseFormData($item));

                FormDataPhone::createOrUpdateItem($form, $clue);
                $form->projects()->sync($item['project_type']);
                $count++;
            } else {
//                dump($item);
            }
        }
        return $count;
    }


    /**
     * @param Collection $data
     * @return bool
     */
    public static function isModel($data)
    {
        $first = $data->get(0);
        return $first
            && $first->contains('访客ID')
            && $first->contains('客户类型')
            && $first->contains('对话网址')
            && $first->contains('线索');
    }

}
