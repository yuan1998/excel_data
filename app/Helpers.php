<?php // Code within app\Helpers\Helper.php

namespace App;

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
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
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
     * 查询Model 到院状态
     * @param BaiduClue|WeiboData|FeiyuData $model
     */
    public static function checkArriving($model)
    {
        $client = static::typeClient($model->type);
        if (!$client) {
            return;
        }
        $date = Carbon::parse($model->getDate());

        $firstDate = $date->firstOfMonth()->toDateString();
        $lastDate  = $date->lastOfMonth()->toDateString();

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
     * 查询 Model 数据的意向度
     * @param $model
     */
    public static function checkIntention($model)
    {
        $client = static::typeClient($model->type);
        if (!$client) {
            return;
        }
        $data = $client::reservationSearchIntention([
            'phone' => $model->phone
        ]);
        $model->fill($data);
        $model->save();
    }

    /**
     * 查看Model 数据是否已建档
     * @param $model
     */
    public static function checkIsArchive($model)
    {
        $client = static::typeClient($model->type);
        if (!$client) {
            return;
        }
        $model->is_archive = $client::tempSearchExists([
            'phone' => $model->phone
        ]);
        $model->save();
    }

    public static function typeClient($type)
    {
        if (!in_array($type, ['zx', 'kq'])) {
            Log::error("{$type} Not Exists");
            return false;
        }
        return $type === 'zx' ? ZxClient::class : KqClient::class;
    }

    public static function generateDateRange(Carbon $start_date, Carbon $end_date)
    {
        $dates = [];

        for ($date = $start_date->copy(); $date->lte($end_date); $date->addDay()) {
            $dates[] = $date->toDateString();
        }

        return $dates;
    }

    public static function explodeKeywordToRegex($str)
    {
        $regex = preg_replace('/(\,)/', '|', $str);

        return "/({$regex})/";
    }

    public static function projectTypeCheck($types, $key, $field = 'keyword')
    {
        $result = [];

        foreach ($types as $type) {
            $value = $type[$field];
            $id    = $type->id;

            $regex = static::explodeKeywordToRegex($value);
            if (preg_match($regex, $key)) {
                array_push($result, $id);
            }
        }
        return $result;
    }

    public static function archiveTypeCheck($types, $key)
    {
        $result = [];
        foreach ($types as $id => $value) {
            if ($key == $value) {
                array_push($result, $id);
            }
        }
        return $result;
    }

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


    public static function validatePhone($value)
    {
        return $value && preg_match("/^1[34578]\d{9}$/", $value);
    }

    /**
     * @param null $id
     * @return Helpers[]|\Illuminate\Database\Eloquent\Collection
     */
    public static function getAccountRebate($id = null)
    {
        if (!static::$AccountRebate) {
            static::$AccountRebate = AccountReturnPoint::all();
        }
        return $id ? static::$AccountRebate->filter(function ($data) use ($id) {
            return $data->form_type == $id;
        }) : static::$AccountRebate;
    }

    /**
     * @return  \Illuminate\Database\Eloquent\Collection|static[]
     */
    public static function getDepartmentTypes()
    {
        if (!static::$DepartmentTypes) {
            static::$DepartmentTypes = DepartmentType::all();
        }
        return static::$DepartmentTypes;
    }

    public static function getProjectOfDepartmentId($id)
    {
        if (!static::$ProjectTypes) {
            static::$ProjectTypes = ProjectType::all()->groupBy('department_id');
        }

        return static::$ProjectTypes->get($id);
    }

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

    public static function dateRangeForEach($dates, $callBack)
    {
        $date = CarbonPeriod::create($dates[0], $dates[1]);

        foreach ($date as $item) {
            $callBack($item);
        }
    }


    public static function checkDepartment($str)
    {
        $types = static::getDepartmentTypes();
        $res   = $types->first(function ($item) use ($str) {
            $keywords = preg_replace('/(\,)/', '|', $item->keyword);
            return !!preg_match("/{$keywords}/", $str);
        });


        return $res;
    }


    public static function checkDepartmentProject($department, $str, $field = 'keyword')
    {
        $id           = $department->id;
        $projectTypes = static::getProjectOfDepartmentId($id);

        if (!$projectTypes) return [];

        return static::projectTypeCheck($projectTypes, $str, $field);
    }


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

    public static function getWeiboData($startDate, $endDate, $count = 2000)
    {
        $cmd     = base_path('PythonScript/weibo_test.py');
        $process = new Process(['python3', $cmd, $startDate, $endDate, $count]);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        try {
            $test  = json_decode($process->getOutput(), true);
            $total = $test['result']['total'];
            if ($total > $count) {
                return static::getWeiboData($startDate, $endDate, $total);
            }
            $data = $test['result']['data'];
            return $data;
        } catch (\Exception $exception) {
            Log::info('getWeiboData Exception', [$exception->getMessage()]);
        }
        return null;
    }

}


