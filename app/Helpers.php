<?php // Code within app\Helpers\Helper.php

namespace App;

use App\Admin\Extensions\Tools\DepartmentDataType;
use App\Clients\BaseClient;
use App\Clients\KqClient;
use App\Clients\ZxClient;
use App\Models\AccountReturnPoint;
use App\Models\ArchiveType;
use App\Models\ArrivingData;
use App\Models\BaiduClue;
use App\Models\BillAccountData;
use App\Models\DepartmentType;
use App\Models\FeiyuData;
use App\Models\MediumType;
use App\Models\ProjectType;
use App\Models\TempCustomerData;
use App\Models\WeiboData;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use phpDocumentor\Reflection\Types\Callable_;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class Helpers
{

    public static $ExcelFields = [
        'show'                       => 0,
        'click'                      => 0,
        'spend'                      => 0,
        'form_count'                 => 0,
        'effective_form'             => 0,
        'turn_weixin'                => 0,
        'click_rate'                 => 0,
        'form_rate'                  => 0,
        'spend_rate'                 => 0,
        'effective_form_rate'        => 0,
        'click_spend'                => 0,
        'form_spend'                 => 0,
        'turn_spend'                 => 0,
        'arriving_spend'             => 0,
        'arriving_count'             => 0,
        'archive_count'              => 0,
        'un_archive_count'           => 0,
        'new_first_arriving'         => 0,
        'new_again_arriving'         => 0,
        'old_arriving'               => 0,
        'new_first_rate'             => 0,
        'arriving_rate'              => 0,
        'new_first_transaction'      => 0,
        'new_again_transaction'      => 0,
        'new_first_transaction_rate' => 0,
        'new_again_transaction_rate' => 0,
        'old_transaction'            => 0,
        'old_transaction_rate'       => 0,
        'total_transaction'          => 0,
        'total_transaction_rate'     => 0,
        'new_first_account'          => 0,
        'new_again_account'          => 0,
        'old_account'                => 0,
        'total_account'              => 0,
        'new_first_average'          => 0,
        'new_again_average'          => 0,
        'old_average'                => 0,
        'total_average'              => 0,
        'proportion_total'           => 0,
        'proportion_new'             => 0,
    ];

    public static $MediumSourceTypeCode = [
        '微博 (表单)' => '9295C7B6F93E4E51A9C09E1C2198CCB5',
    ];

    public static $MediumSourceCode = [
        '信息流' => '5506D677F2934624A9A8D83E9876C4CA',
    ];

    public static $UserIdCode = [
        '口腔网电公用' => 'BF6CE41EC9204AD9BA30A994016EEDAA',
        '洪诩'     => 'B90B67132A564A759E16A98E01269A10',
    ];

    public static $ArchiveTypeCode = [
        '成人-矫正-口腔' => '3705079BD641454FAAFDAA4600E14BAD',
    ];


    /**
     * @var \Illuminate\Database\Eloquent\Collection|static[]
     */
    public static $DepartmentTypes;

    /**
     * @var  \Illuminate\Database\Eloquent\Collection|static[]
     */
    public static $ProjectTypes;

    /**
     * @var  \Illuminate\Database\Eloquent\Collection|static[]
     */
    public static $AccountRebate;

    /**
     * @var  \Illuminate\Database\Eloquent\Collection|static[]
     */
    public static $ArchivesTypes;

    public static function shout(string $string)
    {
        return strtoupper($string);
    }

    /**
     * array 的 key 和 value 倒置
     * @param array $arr
     * @return array
     */
    public static function reverseKeyValue(array $arr)
    {
        $result = [];
        foreach ($arr as $key => $value) {
            $result[$value] = $key;
        }
        return $result;
    }

    /**
     * 将excel 转换成 array
     * @param Collection $row
     * @param null       $fields
     * @return array
     */
    public static function excelToKeyArray(Collection $row, $fields = null)
    {
        $keys   = null;
        $result = [];

        $row->each(function (Collection $value) use (&$keys, &$result, $fields) {
            if ($keys) {
                $data = [];
                $value->each(function ($item, $key) use ($keys, &$data, $fields, $value) {
                    $name = $keys[$key];
                    $name = $fields ? (isset($fields[$name]) ? $fields[$name] : null) : $name;
                    $name && ($data[$name] = (string)$item);
                });
                array_push($result, $data);
            } else {
                $keys = $value->toArray();
            }
        });

        return $result;
    }

    /**
     * 将 Set-Cookie 转换为 Array Cookies
     * @param        $arr
     * @param string $search
     * @return array
     */
    public static function parseCookie($arr, $search = '')
    {
        $result = [];
        foreach ($arr as $value) {
            $cookies = explode('; ', $value);

            foreach ($cookies as $cookie) {
                $data = explode('=', $cookie);
                $key  = str_replace($search, '', $data[0]);

                $result[$key] = $data[1];
            }
        }

        return $result;
    }

    /**
     * 将 array Cookie 转换为 string cookie
     * @param $arr
     * @return string
     */
    public static function cookiesString($arr)
    {
        return collect($arr)->map(function ($value, $key) {
            return "{$key}={$value}";
        })->implode('; ');
    }

    /**
     * 将HTML Table 转换为 Array
     * @param      $dom
     * @param null $_keys
     * @return array
     */
    public static function parserHtmlTable($dom, $_keys = null)
    {
        $head = $dom->find('thead');
        $body = $dom->find('tbody');
        $ths  = $head->find('th');
        $trs  = $body->find('tr');

        $keys = [];
        foreach ($ths as $th) {
            $keys[] = $th->text;
        }

        $result = [];
        foreach ($trs as $tr) {
            $td  = $tr->find('td');
            $arr = [];
            foreach ($td as $key => $value) {
                $name = $keys[$key];

                if ($name === '客户' || $name === '客户姓名' || $name == '网电客户') {
//                    dd($value->outerHTML);
                    preg_match("/CustInfo\(\'(.*?)\'\)/", $value->outerHTML, $match);
                    if (isset($match[1])) {
                        $arr['customer_id'] = $match[1];
                    }
                }

                $field = $_keys ? (isset($_keys[$name]) ? $_keys[$name] : null) : $name;

                $field && $arr[$field] = strip_tags($value->innerHTML);
            }
            array_push($result, $arr);
        }

        return $result;
    }

    /**
     * 解析意向度, 中文 => int
     * @param string $str
     * @return int
     */
    public static function intentionCheck(string $str): int
    {
        if (!$str) return 1;

        if (preg_match("/[\x{4e00}]/u", $str)) {
            return 2;
        } else if (preg_match("/[\x{4e8c}]/u", $str)) {
            return 3;
        } else if (preg_match("/[\x{4e09}]/u", $str)) {
            return 4;
        } else if (preg_match("/[\x{56db}]/u", $str)) {
            return 5;
        } else if (preg_match("/[\x{4e94}]/u", $str)) {
            return 6;
        }

        return 1;
    }


    /**
     * 解析到院状态
     * 0 : 未查询
     * 1 : 未到院
     * 2 : 新客首次
     * 3 : 新客二次
     * 4 : 老客二次
     * @param $item
     * @return int
     */
    public static function arrivingTypeCheck($item)
    {
        $name = trim($item['customer_status']) . trim($item['again_arriving']);
        if (!isset(BaseClient::$arriving_status[$name])) return 1;

        return BaseClient::$arriving_status[$name];
    }

    /**
     * 查询Model 数据的意向度,到院状态 和是否已建档
     * @param $model
     */
    public static function checkIntentionAndArchiveAndArriving($model)
    {
        static::checkArriving($model);
        if ($model->arriving_type <= 1) {
            static::checkIntention($model);
            if ($model->intention <= 1) {
                static::checkIsArchive($model);
            }
        }
    }


    /**
     * 查询Model 数据的意向度和是否已建档
     * @param $model
     * @param $isBaidu
     */
    public static function checkIntentionAndArchive($model, $isBaidu = false)
    {
        static::checkIntention($model);
        if ($model->intention <= 1 || $isBaidu) {
            static::baiduCheckArchive($model);
        }
    }


    /**
     * baiduClue 查询是否已建档 , 是否有会话ID , 是否有url
     * @param BaiduClue $model
     */
    public static function baiduCheckArchive($model)
    {
        $client = static::typeClient($model->type);
        if (!$client) {
            return;
        }
        $data = $client::baiduTempSearch([
            'phone' => $model->phone
        ]);
        $model->fill($data);
        $model->save();
    }


    /**
     * 查询并写入 Model数据 的到院状态
     * @param BaiduClue|WeiboData|FeiyuData $model
     */
    public static function checkArriving($model)
    {
        // 获取 Crm客户端
        if (!$client = static::typeClient($model->type)) {
            return;
        }
        // 获取 模型日期月份 的第一天 和 最后一天
        $date      = Carbon::parse($model->getDate());
        $firstDate = $date->firstOfMonth()->toDateString();
        $lastDate  = $date->lastOfMonth()->toDateString();

        //
        $data = $client::toHospitalSearchArriving([
            'phone'            => $model->phone,
            'DatetimeRegStart' => $firstDate,
            'DatetimeRegEnd'   => $lastDate,
            'pageSize'         => 1,
        ]);
        $model->fill($data);
        $model->save();
    }

    /**
     * 查询并写入 Model数据 的意向度
     * @param $model
     */
    public static function checkIntention($model)
    {
        // 获取 Crm客户端
        if (!$client = static::typeClient($model->type)) {
            return;
        }
        // 获取并保存 意向度和其他结果
        $data = $client::reservationSearchIntention([
            'phone' => $model->phone
        ]);
        $model->fill($data);
        $model->save();
    }

    /**
     * 查询并写入 Model数据 的建档状态
     * @param $model
     */
    public static function checkIsArchive($model)
    {
        $client = static::typeClient($model->type);
        if (!$client) {
            return;
        }
        // 获取 建档状态 结果,并保存
        $model->is_archive = $client::tempSearchExists([
            'phone' => $model->phone
        ]);
        $model->save();
    }

    /**
     * 获取 Crm 客户端类型.
     * @param string $type 类型名称
     * @return string|bool
     */
    public static function typeClient($type)
    {
        if (!in_array($type, ['zx', 'kq'])) {
            Log::error("{$type} Not Exists");
            return false;
        }
        return $type === 'zx' ? ZxClient::class : KqClient::class;
    }


    /**
     * 将带有 | 的字符串装换成 正则
     * @param string $str
     * @return string
     */
    public static function explodeKeywordToRegex($str)
    {
        $regex = preg_replace('/(\,)/', '|', $str);

        return "/({$regex})/";
    }

    /**
     *  查看传参中的 病种类型 是否匹配 关键词字符串.
     * @param array  $types 病种类型
     * @param string $key   关键词字符串
     * @param string $field
     * @return Collection|null
     */
    public static function projectTypeCheck($types, $key, $field = 'keyword')
    {
        // 定义 结果
        $result = [];
        foreach ($types as $type) {
            // 获取 匹配词
            $value = $type[$field];
            // 将匹配词转换成 正则
            $regex = static::explodeKeywordToRegex($value);

            // 使用正则匹配 关键词字符串
            if (preg_match($regex, $key)) {
                array_push($result, $type);
            }
        }
        return count($result) ? collect($result) : null;
    }


    /**
     * 获取 媒介类型ID , 如果没有则创建,然后再缓存到redis中
     * @param $name
     * @return mixed
     */
    public static function getMediumTypeId($name)
    {
        $val = Redis::get($name);

        if (!$val) {
            $item = MediumType::firstOrCreate([
                'title' => $name
            ]);
            $val  = $item->id;
            Redis::set($name, $val);
        }

        return $val;

    }

    /**
     * 获取 建档类型ID , 如果没有则创建,然后再缓存到redis中
     * @param $name
     * @return mixed
     */
    public static function getArchiveTypeId($name)
    {
        $val = Redis::get($name);

        if (!$val) {
            $item = ArchiveType::firstOrCreate([
                'title' => $name
            ]);
            $val  = $item->id;
            Redis::set($name, $val);
        }

        return $val;
    }

    /**
     * 验证 电话号码 是否正确
     * @param string|int $value 电话号
     * @return bool
     */
    public static function validatePhone($value)
    {
        return $value && preg_match("/^1[34578]\d{9}$/", $value);
    }

    /**
     * 获取 账户模型 ,如果平台ID 为空则返回所有 账户模型,否则返回对应平台的 账户数据
     * @param string|int|null $id 平台ID
     * @return Helpers[]|\Illuminate\Database\Eloquent\Collection
     */
    public static function getAccountRebate($id = null)
    {
        if (!static::$AccountRebate) {
            // 缓存 所有账户数据
            static::$AccountRebate = AccountReturnPoint::all();
        }
        return $id ? static::$AccountRebate->filter(function ($data) use ($id) {
            return $data->form_type == $id;
        }) : static::$AccountRebate;
    }

    /**
     * 获取 所有 科室模型
     * @return  \Illuminate\Database\Eloquent\Collection|static[]
     */
    public static function getDepartmentTypes()
    {
        if (!static::$DepartmentTypes) {
            // 缓存 所有科室
            static::$DepartmentTypes = DepartmentType::all();
        }
        return static::$DepartmentTypes;
    }

    /**
     * 获取 科室ID 下所有的病种模型
     * @param string|int $id 科室ID
     * @return mixed
     */
    public static function getProjectOfDepartmentId($id)
    {
        if (!static::$ProjectTypes) {
            // 缓存 所有病种, 并用科室ID 进行分类
            static::$ProjectTypes = ProjectType::all()->groupBy('department_id');
        }

        return static::$ProjectTypes->get($id);
    }

    /**
     * 判断 平台的回扣
     * @param string|integer $id     平台ID
     * @param string         $search 关键词字符串
     * @return float|int
     */
    public static function checkFormTypeRebate($id, $search)
    {
        $data = static::getAccountRebate($id);

        if ($data->isEmpty()) {
            return 0;
        }

        $result = $data->first(function ($data) use ($search) {
            return preg_match("/{$data->keyword}/", $search);
        });

        if ($result) {
            return (float)$result->rebate;
        }

        $result = $data->first(function ($data) {
            return $data->is_default;
        });
        return $result ? (float)$result->rebate : 0;
    }

    /**
     * 迭代 日期范围, 在回调中传入当前日期
     * @param array   $dates    日期范围
     * @param Closure $callBack 回调
     */
    public static function dateRangeForEach($dates, Closure $callBack)
    {
        $date = CarbonPeriod::create($dates[0], $dates[1]);

        foreach ($date as $item) {
            $callBack($item);
        }
    }


    /**
     * 获取所有科室,判断 关键词字符串 是否有匹配的科室
     * @param string $str 关键词字符串
     * @return mixed
     */
    public static function checkDepartment($str)
    {
        // 获取所有科室
        $types = static::getDepartmentTypes();

        // 找到并返回第一个匹配的科室
        return $types->first(function ($item) use ($str) {
            // 使用 科室的keyword 匹配 关键词字符串
            $keywords = preg_replace('/(\,)/', '|', $item->keyword);
            return !!preg_match("/{$keywords}/", $str);
        });
    }


    /**
     * 获取科室下的所有病种,判断 关键词字符串 是否有匹配的病种
     * @param DepartmentDataType $department 科室模型
     * @param string             $str        关键词字符串
     * @param string             $field      关键词字段
     * @return Collection|null
     */
    public static function checkDepartmentProject($department, $str, $field = 'keyword')
    {
        // 获取 科室ID, 使用ID来获取科室下的病种
        $id           = $department->id;
        $projectTypes = static::getProjectOfDepartmentId($id);

        // 如果没有病种,返回null
        if (!$projectTypes) return null;

        // 判断并返回与 关键词字符串 匹配的病种,没有则返回null
        return static::projectTypeCheck($projectTypes, $str, $field);
    }

    /**
     * 将 数字序号 转换成 Excel的序号
     * @param integer $num 数字序号
     * @return string Excel的序号
     */
    public static function getNameFromNumber($num)
    {
        $numeric = ($num - 1) % 26;
        $letter  = chr(65 + $numeric);
        $num2    = intval(($num - 1) / 26);
        if ($num2 > 0) {
            return static::getNameFromNumber($num2) . $letter;
        } else {
            return $letter;
        }
    }

    /**
     * 判断Crm数据的类型,并返回对应的Class
     * @param $type
     * @return string
     */
    public static function getDataModel($type)
    {
        if ($type === 'billAccountData') {
            return BillAccountData::class;
        }
        if ($type === 'arrivingData') {
            return ArrivingData::class;
        }
        if ($type === 'tempCustomerData') {
            return TempCustomerData::class;
        }
    }

}


