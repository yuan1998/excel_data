<?php

namespace App\Models;

use App\Helpers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * @method static YiliaoData updateOrCreate(array $array, $item)
 */
class YiliaoData extends Model
{
    public static $fields = [
        "visitorStaticId"         => '访客静态id',
        "chatId"                  => '对话id',
        "groupId"                 => '对话所在分组id',
        "visitorMsgCount"         => '访客消息量',
        "userFirstRespTime"       => '客服首次回复时间',
        "effective"               => '会话效果：有效对话是1， 无效对话是0',
        "searchHost"              => '搜索引擎',
        "userMsgCount"            => '客服消息量',
        "extColum2"               => '扩展2',
        "extColum1"               => '扩展1',
        "visitorFirstTime"        => '访客首次发消息时间',
        "summarizeName"           => '总结名称',
        "keyword"                 => '关键词',
        "extColum6"               => '扩展6',
        "extColum5"               => '扩展5',
        "visitorLocationProvince" => '访客省份',
        "extColum4"               => '扩展4',
        "userTimeoutTimes"        => '客服超时次数',
        "extColum3"               => '扩展3',
        "chatType"                => '对话平台',
        "visitorLocationCountry"  => '访客国家',
        "visitorIp"               => '访客ip',
        "firstUrl"                => '最初访问页面',
        "summarizeId"             => '总结Id',
        "inviteMode"              => '发起方式（1客服发起2：访客发起3：内部会议室4：会话转移，5外部会议室）',
        "lastMessageTime"         => '最后一条消息时间',
        "closeType"               => '结束方式（1：访客，2：客服，3：会话转移，4：超时）',
        "userId"                  => '客服id',
        "allTime"                 => '对话时间',
        "companyId"               => '公司id',
        "createTime"              => '创建时间',
        "visitorLocationCity"     => '访客城市',
        "chatCategory"            => '对话类型',
        "siteId"                  => '子站点id',
        "msgCount"                => '消息数',
        "chatUrl"                 => '对话页面',
        "endTime"                 => '结束时间',
        "effectTime"              => '有效沟通时间',
        "referPage"               => '详细来源',
        "visitorId"               => '访客id',
    ];

    public static $excelFields = [
        "客服姓名"       => 'userName',
        "客服ID"       => 'userId',
        "会话ID"       => 'chatId',
        "会话请求ID"     => 'chatRequestId',
        "访客标识"       => 'visitorTag',
        "访客静态ID"     => 'visitorStaticId',
        "姓名"         => 'name',
        "开始对话时间"     => 'startChatTime',
        "消息数"        => 'messageCount',
        "访客消息数"      => 'visitorMessageCount',
        "客服消息数"      => 'userMessageCount',
        "访客IP"       => 'visitorIp',
        "地区"         => 'location',
        "国家"         => 'Country',
        "省份"         => 'province',
        "城市"         => 'city',
        "对话总结标签"     => 'chatSummarizeTag',
        "来源地址"       => 'referUrl',
        "会话时间"       => 'chatTime',
        "有效对话时间"     => 'effectTime',
        "搜索词"        => 'keyword',
        "会话效果"       => 'effective',
        "最初访问"       => 'firstUrl',
        "访客评价"       => 'visitorRating',
        "评价原因"       => 'evaluation',
        "子站点"        => 'subSite',
        "手机"         => 'phone',
        "电话"         => 'tel',
        "QQ"         => 'qq',
        "微信"         => 'weixin',
        "公司名称"       => 'companyName',
        "邮箱"         => 'email',
        "对话页面"       => 'chatUrl',
        "客服超时响应次数"   => 'userTimeoutTimes',
        "对话质量"       => 'conversationQuality',
        "接入分组"       => 'group',
        "访客首次发消息时间"  => 'visitorFirstMessageTime',
        "客服首次回复时间"   => 'userFirstMessageTime',
        "客服首次响应时长"   => 'userFirstResponseTime',
        "最后一条消息时间"   => 'lastMessageTime',
        "会话设备"       => 'chatDevice',
        "发起方式"       => 'inviteMode',
        "结束方式"       => 'closeType',
        "会话转移来源会话ID" => 'chatTransferId',
        "会话转移接入方"    => 'chatTransferBy',
        "搜索引擎"       => 'searchEngine',
        "对话结束时间"     => 'chatEndTime',
        "扩展字段1"      => 'extColumn1',

        "总结详细"  => 'summarizeDetail',
        "标签Id"  => 'tagId',
        "会话标签"  => 'chatTag',
        "渠道Id"  => 'channel_id',
        "推广渠道"  => 'promoteChannel',
        "性别"    => 'sex',
        "备注"    => 'remark',
        "名片扩展1" => 'extCard1',
    ];


    protected $fillable = [
        'userName',
        'userId',
        'chatId',
        'chatRequestId',
        'visitorTag',
        'visitorStaticId',
        'name',
        'startChatTime',
        'messageCount',
        'visitorMessageCount',
        'userMessageCount',
        'visitorIp',
        'location',
        'Country',
        'province',
        'city',
        'chatSummarizeTag',
        'referUrl',
        'chatTime',
        'effectTime',
        'keyword',
        'effective',
        'firstUrl',
        'visitorRating',
        'evaluation',
        'subSite',
        'phone',
        'tel',
        'qq',
        'weixin',
        'companyName',
        'email',
        'chatUrl',
        'userTimeoutTimes',
        'conversationQuality',
        'group',
        'visitorFirstMessageTime',
        'userFirstMessageTime',
        'userFirstResponseTime',
        'lastMessageTime',
        'chatDevice',
        'inviteMode',
        'closeType',
        'chatTransferId',
        'chatTransferBy',
        'searchEngine',
        'chatEndTime',
        'extColumn1',
        'summarizeDetail',
        'tagId',
        'chatTag',
        'channel_id',
        'promoteChannel',
        'sex',
        'remark',
        'extCard1',
        'type',
        'form_type',
        'department_id',
        'code',
    ];

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

    public static function formTypeCheck($code)
    {
        if (!$code) return null;
    }

    public static function parserData($item)
    {
        $url = urldecode($item['chatUrl']);
        preg_match("/\?A[0-9](.{12,20})/", $url, $match);
        $item['code'] = (isset($match[0]) ? $match[0] : '') . '-' . $item['extCard1'];
        $code         = $item['code'];

        if (!$item['form_type'] || !$departmentType = Helpers::checkDepartment($code)) {
            Log::info('无法判断科室', [
                'code' => $code,
            ]);
            throw new \Exception('无法判断科室: ' . $code);
        }


        $item['chatUrl']   = substr($item['chatUrl'] ?? '', 0, Builder::$defaultStringLength);
        $item['firstUrl']  = substr($item['firstUrl'] ?? '', 0, Builder::$defaultStringLength);
        $item['referPage'] = substr($item['referUrl'] ?? '', 0, Builder::$defaultStringLength);
        $item['date']      = $item['startChatTime'];
        $item['form_type'] = BaiduData::checkCodeIs($item['code']);


        $item['type']            = $departmentType->type;
        $item['department_id']   = $departmentType->id;
        $item['department_type'] = $departmentType;
        $item['project_type']    = Helpers::checkDepartmentProject($departmentType, $code);

        $item['consultant_code'] = $item['name'];


        return $item;
    }

    /**
     * @param Collection $data
     * @return bool
     */
    public static function isModel($data)
    {
        $first = $data->get(0);
        if ($first[2] === null && preg_match('/对话查询统计/', $first[0])) {
            return true;
        }
        return $first
            && $first->contains('名片扩展1')
            && $first->contains('会话请求ID')
            && $first->contains('访客静态ID')
            && $first->contains('手机')
            && $first->contains('电话')
            && $first->contains('会话ID');
    }

    /**
     * @param Collection $collection
     * @return int
     * @throws \Exception
     */
    public static function excelCollection($collection)
    {
        $collection = $collection->filter(function ($item) {
            return isset($item[2]) && $item[2];
        });
        $data       = Helpers::excelToKeyArray($collection, static::$excelFields);

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
            if (!in_array($item['form_type'], [1, 8])) continue;

            FormData::baseMakeFormData(static::class, $item, [
                'chatId' => $item['chatId'],
            ]);
            $count++;
        }
        return $count;
    }

}
