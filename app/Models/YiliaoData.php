<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Schema\Builder;

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
//        "扩展字段2"      => 'extColumn2',
//        "扩展字段3"      => 'extColumn3',
//        "扩展字段4"      => 'extColumn4',
//        "扩展字段5"      => 'extColumn5',
//        "扩展字段6"      => 'extColumn6',
        "总结详细"       => 'summarizeDetail',
        "标签Id"       => 'tagId',
        "会话标签"       => 'chatTag',
        "渠道Id"       => 'channelId',
        "推广渠道"       => 'promoteChannel',
        "性别"         => 'sex',
        "备注"         => 'remark',
        "名片扩展1"      => 'extCard1',
//        "名片扩展2"      => 'extCard2',
//        "名片扩展3"      => 'extCard3',
//        "名片扩展4"      => 'extCard4',
//        "名片扩展5"      => 'extCard5',
//        "名片扩展6"      => 'extCard6',
//        "名片扩展7"      => 'extCard7',
//        "名片扩展8"      => 'extCard8',
//        "名片扩展9"      => 'extCard9',
//        "名片扩展10"     => 'extCard10',
    ];


    protected $fillable = [
        "visitorStaticId",
        "chatId",
        "groupId",
        "visitorMsgCount",
        "userFirstRespTime",
        "effective",
        "searchHost",
        "userMsgCount",
        "extColum2",
        "extColum1",
        "visitorFirstTime",
        "summarizeName",
        "keyword",
        "extColum6",
        "extColum5",
        "visitorLocationProvince",
        "extColum4",
        "userTimeoutTimes",
        "extColum3",
        "chatType",
        "visitorLocationCountry",
        "visitorIp",
        "firstUrl",
        "summarizeId",
        "inviteMode",
        "lastMessageTime",
        "closeType",
        "userId",
        "allTime",
        "companyId",
        "createTime",
        "visitorLocationCity",
        "chatCategory",
        "siteId",
        "msgCount",
        "chatUrl",
        "endTime",
        "effectTime",
        "referPage",
        "visitorId",
        "type",
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
        $item['chatUrl']   = substr($item['chatUrl'] ?? '', 0, Builder::$defaultStringLength);
        $item['firstUrl']  = substr($item['firstUrl'] ?? '', 0, Builder::$defaultStringLength);
        $item['referPage'] = substr($item['referUrl'] ?? '', 0, Builder::$defaultStringLength);

        $url = urldecode($item['chatUrl']);
        preg_match("/\?A[0-9](.{12,20})/", $url, $match);
        $item['code']      = (isset($match[0]) ? $match[0] : '') . '-' . $item['extCard1'];
        $item['form_type'] = BaiduData::checkCodeIs($item['code']);
        return $item;
    }


    public static function generateYiliaoData($data)
    {
        $data = collect($data)->filter(function ($item) {
            return isset($item['chatId']) &&
                isset($item['visitorId']);
        });

        foreach ($data as $item) {
            $item = static::parserData($item);
            $item = static::create($item);

            if ($item == 1) {

            }


        }

    }

}
