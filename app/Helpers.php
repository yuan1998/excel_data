<?php // Code within app\Helpers\Helper.php

namespace App;

use App\Admin\Extensions\Tools\DepartmentDataType;
use App\Clients\BaseClient;
use App\Clients\KqClient;
use App\Clients\SfClient;
use App\Clients\ZxClient;
use App\Models\AccountData;
use App\Models\AccountReturnPoint;
use App\Models\ArchiveType;
use App\Models\ArrivingData;
use App\Models\BaiduClue;
use App\Models\BaiduData;
use App\Models\BaiduSpend;
use App\Models\BillAccountData;
use App\Models\Channel;
use App\Models\Consultant;
use App\Models\DepartmentType;
use App\Models\FeiyuData;
use App\Models\FeiyuSpend;
use App\Models\MediumType;
use App\Models\ProjectType;
use App\Models\TempCustomerData;
use App\Models\WeiboData;
use App\Models\WeiboFormData;
use App\Models\WeiboSpend;
use App\Models\YiliaoData;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use phpDocumentor\Reflection\Types\Callable_;
use PHPHtmlParser\Exceptions\EmptyCollectionException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class Helpers
{

    public static $ExcelFields = [
        // 互动
        'interactive' => 0,
        // 评论
        'comment' => 0,
        // 私信
        'message' => 0,
        // 评论转出
        'comment_turn' => 0,
        // 展现
        'show' => 0,
        // 点击
        'click' => 0,
        // 点击率
        'click_rate' => 0,
        // 点击成本
        'click_spend' => 0,
        // 消费(虚)
        'off_spend' => 0,
        // 消费(实)
        'spend' => 0,
        // 总表单
        'form_count' => 0,
        //
        'effective_form' => 0,
        'turn_weixin' => 0,
        'form_rate' => 0,
        'spend_rate' => 0,
        'effective_form_rate' => 0,
        'form_spend' => 0,
        'turn_spend' => 0,
        'arriving_spend' => 0,
        'arriving_count' => 0,
        'archive_count' => 0,
        'un_archive_count' => 0,
        'new_first_arriving' => 0,
        'new_again_arriving' => 0,
        'old_arriving' => 0,
        'new_first_rate' => 0,
        'arriving_rate' => 0,
        'new_first_transaction' => 0,
        'new_again_transaction' => 0,
        'new_first_transaction_rate' => 0,
        'new_again_transaction_rate' => 0,
        'old_transaction' => 0,
        'old_transaction_rate' => 0,
        'total_transaction' => 0,
        'total_transaction_rate' => 0,
        'new_first_account' => 0,
        'new_again_account' => 0,
        'old_account' => 0,
        'total_account' => 0,
        'new_first_average' => 0,
        'new_again_average' => 0,
        'old_average' => 0,
        'total_average' => 0,
        'proportion_total' => 0,
        'proportion_new' => 0,
    ];

    public static $MediumSourceTypeCode = [
        '微博 (表单)' => '9295C7B6F93E4E51A9C09E1C2198CCB5',
    ];

    public static $MediumSourceCode = [
        '信息流' => '5506D677F2934624A9A8D83E9876C4CA',
    ];

    public static $UserIdCode = [
        '口腔网电公用' => 'BF6CE41EC9204AD9BA30A994016EEDAA',
        '洪诩' => 'B90B67132A564A759E16A98E01269A10',
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
     * @param null $fields
     * @return array
     */
    public static function excelToKeyArray(Collection $row, $fields = null)
    {
        $keys = null;
        $result = [];

        $row->each(function (Collection $value) use (&$keys, &$result, $fields) {
            if ($keys) {
                $data = [];
                $value->each(function ($item, $key) use ($keys, &$data, $fields, $value) {
                    $name = $keys[$key];
                    $name = $fields ? (isset($fields[$name]) ? $fields[$name] : $name) : $name;
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
                $key = str_replace($search, '', $data[0]);

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
        $ths = $head->find('th');
        $trs = $body->find('tr');

        $keys = [];
        foreach ($ths as $th) {
            $keys[] = $th->text;
        }

        $result = [];
        foreach ($trs as $tr) {
            $td = $tr->find('td');
            $arr = [];
            foreach ($td as $index => $value) {
                if ($name = data_get($keys, $index))
                    continue;
                
                $valueText = $value->innerHTML;

                if ($name === '客户' || $name === '客户姓名' || $name == '网电客户') {
                    $aTag = $value->find('a');
                    if ($aTag) {
                        try {
                            $valueText = $aTag->innerHTML;
                        } catch (\Exception $exception) {
                            $valueText = $value->innerHTML;
                        }
                    }
//                    dd($value->outerHTML);
                    preg_match("/CustInfo\(\'(.*?)\'\)/", $value->outerHTML, $match);
                    if (isset($match[1])) {
                        $arr['customer_id'] = $match[1];
                    }
                }

                $field = $_keys ? (isset($_keys[$name]) ? $_keys[$name] : $name) : $name;

                $field && $arr[$field] = trim(strip_tags($valueText));
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

        if (preg_match("/一级/u", $str)) {
            return 2;
        } else if (preg_match("/二级/u", $str)) {
            return 3;
        } else if (preg_match("/三级/u", $str)) {
            return 4;
        } else if (preg_match("/四级/u", $str)) {
            return 5;
        } else if (preg_match("/五级/u", $str)) {
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
        Log::info("Debug 查询手机号码 无法正常工作问题 Step 1 : ", [
            'title' => '方法开始',
            'Model' => $model->phone,
        ]);
        static::checkIntention($model);
        if ($model->intention <= 1 || $isBaidu) {
            static::baiduCheckArchive($model);
            if ($model->is_archive !== 1) {
                static::tempCustInfoArchive($model);
            }
        }

        Log::info("Debug 查询手机号码 无法正常工作问题 Step : 5  ", [
            'title' => '步骤结束',
        ]);
    }

    public static function tempCustInfoArchive($model)
    {
        $clientName = $model->client ?? $model->type;
        if (!$client = static::typeClient($clientName)) {
            return;
        }
        $data = $client::tempCustomerInfoArchiveCheck($model);
        Log::info("Debug 查询手机号码 无法正常工作问题 Step 4 : ", [
            'title' => 'tempCustInfoArchive 查询结果',
            'phone' => $model->phone,
            'data' => $data,
        ]);

        $model->fill($data);
        $model->save();
    }

    /**
     * baiduClue 查询是否已建档 , 是否有会话ID , 是否有url
     * @param BaiduClue $model
     */
    public static function baiduCheckArchive($model)
    {
        $clientName = $model->client ?? $model->type;
        if (!$client = static::typeClient($clientName)) {
            return;
        }
        $data = $client::baiduTempSearch([
            'phone' => $model->phone
        ], $model);

        Log::info("Debug 查询手机号码 无法正常工作问题 Step 3 : ", [
            'title' => 'baiduCheckArchive 查询结果',
            'phone' => $model->phone,
            'data' => $data,
        ]);

        if ($data) {
            $model->fill($data);
            $model->save();
        }
    }


    /**
     * 查询并写入 Model数据 的到院状态
     * @param BaiduClue|WeiboData|FeiyuData $model
     */
    public static function checkArriving($model)
    {
        $clientName = $model->client ?? $model->type;
        if (!$client = static::typeClient($clientName)) {
            return;
        }
        // 获取 模型日期月份 的第一天 和 最后一天
        $date = Carbon::parse($model->getDate());
        $firstDate = $date->firstOfMonth()->toDateString();
        $lastDate = $date->lastOfMonth()->toDateString();

        $data = $client::toHospitalSearchArriving([
            'phone' => $model->phone,
            'DatetimeRegStart' => $firstDate,
            'DatetimeRegEnd' => $lastDate,
            'pageSize' => 1,
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
        $clientName = $model->client ?? $model->type;
        if (!$client = static::typeClient($clientName)) {
            return;
        }
        // 获取并保存 意向度和其他结果
        $data = $client::reservationSearchIntention([
            'phone' => $model->phone
        ], $model);

        Log::info("Debug 查询手机号码 无法正常工作问题 Step 2 : ", [
            'title' => 'checkIntention 查询结束',
            'phone' => $model->phone,
            'data' => $data,
        ]);

        $model->fill($data);
        $model->save();
    }

    /**
     * 查询并写入 Model数据 的建档状态
     * @param $model
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \PHPHtmlParser\Exceptions\ChildNotFoundException
     * @throws \PHPHtmlParser\Exceptions\CircularException
     * @throws \PHPHtmlParser\Exceptions\CurlException
     * @throws \PHPHtmlParser\Exceptions\NotLoadedException
     * @throws \PHPHtmlParser\Exceptions\StrictException
     */
    public static function checkIsArchive($model)
    {
        $clientName = $model->client ?? $model->type;
        if (!$client = static::typeClient($clientName)) {
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
     * @return BaseClient|bool
     */
    public static function typeClient($type)
    {
        if (!in_array($type, ['zx', 'kq', 'sf'])) {
            Log::error("{$type} Not Exists");
            return false;
        }
        switch ($type) {
            case 'sf':
                return SfClient::class;
            case 'zx':
                return ZxClient::class;
            case 'kq':
                return KqClient::class;
        }
        return false;
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
     * @param array $types 病种类型
     * @param string $key 关键词字符串
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
     * @param $date
     * @param $archive_date
     * @return int
     */
    public static function checkIsRepeat($date, $archive_date)
    {
        $startDate = Carbon::parse($date);
        return Carbon::parse($archive_date)->gte($startDate) ? 1 : 2;
    }

    public static function checkTurnWeixin($comment)
    {
        return preg_match('/转微/', $comment) ? 1 : 2;
    }

    // 13192567990
    //
    // 441702199806013818

    /**
     * 获取 媒介类型ID , 如果没有则创建,然后再缓存到redis中
     * @param $name
     * @return mixed
     */
    public static function getMediumTypeId($name)
    {
        $val = Redis::get("_MEDIUM_TITLE_CHECK_ID_" . $name);

        if (!$val) {
            $item = MediumType::firstOrCreate([
                'title' => $name
            ]);
            $val = $item->id;
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
            $val = $item->id;
            Redis::set($name, $val);
        }

        return $val;
    }


    public static function multipleCheckConsultantId($item, $type, $arr)
    {
        $result = [];
        foreach ($arr as $field) {
            $value = Arr::get($item, $field);
            if ($value)
                $result[$field . '_id'] = static::getConsultantId($type,
                    str_replace(' ', '', $value));
        }

        return $result;
    }

    public static function consultantNameParse($name)
    {
        $name = str_replace('）', ')', $name);
        return str_replace('（', '(', $name);


    }

    /**
     * 生成 客服 ID
     * @param        $type
     * @param        $name
     * @param string $prefix
     * @return mixed
     */
    public static function getConsultantId($type, $name, $prefix = 'GET_CONSULTANT_ID_')
    {
        $name = static::consultantNameParse($name);

        $val = Redis::get($prefix . $name);

        if (!$val) {
            $item = Consultant::firstOrCreate([
                'name' => $name,
                'keyword' => $name,
                'type' => $type,
            ]);
            $val = $item->id;
            Redis::set("{$prefix}{$type}_{$name}", $val);
        }

        return $val;
    }


    /**
     * 缓存 咨询数据.
     * @var array
     */
    public static $consultantList = [];

    /**
     * 根据 医院类型  获取 咨询数据.
     * @param $type
     * @return mixed
     */
    public static function getConsultantOfType($type)
    {
        if (!Arr::exists(static::$consultantList, $type)) {
            $consultants = Consultant::query()
                ->where('type', $type)
                ->get(['id', 'keyword'])
                ->toArray();

            Arr::set(static::$consultantList, $type, $consultants);
        }
        return Arr::get(static::$consultantList, $type);

    }

    /**
     * 获取咨询数据,判断该数据 属于哪个 咨询人员.
     * @param $type
     * @param $name
     * @return null
     */
    public static function checkConsultantNameOf($type, $name)
    {
        if (!$name || $name === 'None' || $name === '未分组') return null;

        $consultants = static::getConsultantOfType($type);
        foreach ($consultants as $consultant) {
            $keywords = preg_replace('/(\,)/', '|', $consultant['keyword']);

            try {
                if (preg_match("/{$keywords}/", $name))
                    return $consultant['id'];

            } catch (\Exception $exception) {

            }
        }

        return null;
    }

    /**
     * 验证 电话号码 是否正确
     * @param string|int $value 电话号
     * @param bool $str
     * @return bool
     */
    public static function validatePhone($value, $str = false)
    {
        $data = collect();
        preg_match_all('/1[3456789]\d{9}/', $value, $matches);
        if (isset($matches[0])) {
            $data = $data->merge($matches[0]);
        }

        $data = $data->unique();

        return $data->isEmpty() ? false : ($str ? $data->join(',') : true);
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
     * 迭代 日期范围, 在回调中传入当前日期
     * @param array $dates 日期范围
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
     * @param string $str 关键词字符串
     * @param string $field 关键词字段
     * @return Collection|array
     */
    public static function checkDepartmentProject($department, $str, $field = 'keyword')
    {
        // 获取 科室ID, 使用ID来获取科室下的病种
        $id = $department->id;
        $projectTypes = static::getProjectOfDepartmentId($id);

        // 如果没有病种,返回null
        if (!$projectTypes) return null;

        // 判断并返回与 关键词字符串 匹配的病种,没有则返回null
        $result = static::projectTypeCheck($projectTypes, $str, $field);


        return $result ? ($result->count() > 1 ? [] : $result->pluck('id')) : [];
    }

    /**
     * 将 数字序号 转换成 Excel的序号
     * @param integer $num 数字序号
     * @return string Excel的序号
     */
    public static function getNameFromNumber($num)
    {
        $numeric = ($num - 1) % 26;
        $letter = chr(65 + $numeric);
        $num2 = intval(($num - 1) / 26);
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

    public static function accountValidationString($accounts, $str, $field = 'keyword', $t = false)
    {
        $default = null;
        foreach ($accounts as $account) {
            if (!$default && $account['is_default']) $default = $account;
            $keyword = $account[$field];
            if ($keyword && preg_match(static::explodeKeywordToRegex($keyword), $str)) {
                return $t ? $account : $account['id'];
            }
        }
        return $default ? ($t ? $default : $default['id']) : null;
    }

    public static function crmDataCheckAccount($data, $type)
    {
        $mediumId = $data['medium_id'];
        $item = Channel::query()
            ->whereHas('mediums', function ($query) use ($mediumId) {
                $query->where('id', $mediumId);
            })
            ->first();

        if ($item) {
            $accounts = AccountData::query()
                ->where('channel_id', $item->id)
                ->where('type', $type)
                ->get();
            if ($accounts) {
                return static::accountValidationString($accounts, $data['visitor_id'], 'crm_keyword');
            }
        }
        return null;
    }

    public static function formDataCheckAccount($item, $field, $typeField = 'form_type', $t = false)
    {
        $type = $item['type'];
        $typeId = $item[$typeField];
        $keyword = $item[$field];
//        dd($type , $typeId , $keyword);
        if (!$keyword || !$typeId || !$type) return null;

        $channel = Channel::query()
            ->where('form_type', 'like', "%{$typeId}%")
            ->first();

        if ($channel) {
            $accounts = AccountData::query()
                ->where('channel_id', $channel->id)
                ->where('type', $type)
                ->get();
            if ($accounts) {
                return static::accountValidationString($accounts, $keyword, 'keyword', $t);
            }
        }
        return null;
    }

    public static function array_depth(array $array)
    {
        $max_depth = 1;

        foreach ($array as $value) {
            if (is_array($value)) {
                $depth = static::array_depth($value) + 1;

                if ($depth > $max_depth) {
                    $max_depth = $depth;
                }
            }
        }

        return $max_depth;
    }

    public static function makeHeaders($headers)
    {
        $rows = [
            [], []
        ];
        foreach ($headers as $key => $value) {

            array_push($rows[0], $key);
            if ($value) {
                foreach ($value as $item) {
                    array_push($rows[1], $item);
                }
                if (($count = count($value)) > 1) {
                    for ($i = 1; $i < $count; $i++) {
                        array_push($rows[0], '');
                    }
                }
            } else {
                array_push($rows[1], '');
            }

        }
        return $rows;

    }


    public static function divisionOfSelf($val, $div)
    {
        return !$div
            ? $val
            : $val / $div;
    }

    public static function toRate($value)
    {
        if (!$value) return '0%';
        return round($value * 100, 2) . '%';
    }


    public static function toRatio($num1, $num2)
    {
        if (!$num1) {
            return $num1 . ":" . $num2;
        }
        return "1:" . round($num2 / $num1, 2);
    }

    public static function gcd($a, $b)
    {
        if ($a == 0 || $b == 0)
            return abs(max(abs($a), abs($b)));

        $r = $a % $b;
        return ($r != 0) ?
            static::gcd($b, $r) :
            abs($b);
    }


    public static function is_utf8($str)
    {
        if ($str === iconv('UTF-8', 'UTF-8//IGNORE', $str)) {
            return 'UTF-8';
        }
    }

    /**
     * @param UploadedFile $file
     */
    public static function checkUTF8($file)
    {
        $filename = $file->getClientOriginalName();
        if (!preg_match('/\.(xls)/', $filename)) {
            $path = $file->path();
            $ctx = \File::get($path);

            if (!static::is_utf8($ctx)) {
                $ctx = mb_convert_encoding($ctx, 'UTF-8', 'gb2312');
                \File::put($path, $ctx);
            }
        }
    }

    public static function timeBetween($start, $end, $testTime = null)
    {
        // 获取用于对比的时间
        $now = $testTime ? Carbon::parse($testTime) : Carbon::now();
        $start = Carbon::parse($start);
        $end = Carbon::parse($end);

        // 转换时间格式, 例: 09:00:00 => 90000
        $startTime = (int)$start->format('His');
        $endTime = (int)$end->format('His');
        $nowTime = (int)$now->format('His');

        // 返回对比结果   start_time <=  now <= $end_time
        return $startTime <= $nowTime && $nowTime <= $endTime;

    }


    public static function validateFormat($date, $format)
    {

        // parse date with current format
        $parsed = date_parse_from_format($format, $date);

        // if value matches given format return true=validation succeeded
        if ($parsed['error_count'] === 0 && $parsed['warning_count'] === 0) {
            return true;
        }
        return false;
    }

    public static function validateUploadExcelType($sheetName, $headers)
    {

    }

    public static function isDate($value)
    {
        if (!$value) {
            return false;
        }

        try {
            new \DateTime($value);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public static function isUrl($url)
    {
        $urlParse = parse_url($url);

        if (isset($urlParse['host']) && isset($urlParse['query'])) {
            parse_str($urlParse['query'], $get_array);
            return implode('-', array_keys($get_array));
        }
        return false;
    }

}




