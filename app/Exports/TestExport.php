<?php

namespace App\Exports;

use App\Parsers\ParserStart;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Events\AfterSheet;

class TestExport implements WithMultipleSheets
{
    use Exportable;
    /**
     * @var ParserStart
     */
    public $parser;

    public static $AccountHeaders = [
        '日期'   => '',
        '渠道'   => '',
        '账户'   => '',
        '推广数据' => [
            '互动', '评论', '私信', '评论转出', '展现', '点击', '点击率', '点击成本', '消费(实)', '消费（虚）', '总表单', "有效表单", "无效表单",
            '空号', '重复表单', '有效表单占比', '无效表单占比', "空号占比", '重复表单占比', '转微', '总预约'
        ],
        '咨询数据' => [
            '总建档', '一级建档', '二级建档', '三级建档', '四级建档', '五级建档', '未建档', '新客到院', '二次到院', '老客到院',
            '新客首次占比', '新客二次占比', '老客占比'
        ],
        '院内数据' => [
            '首次成交', '二次成交', "老客成交", '总成交', '首次业绩', '二次业绩', '老客业绩', '总业绩', "首次单体", "二次单体", "老客单体", '总单体', '首次成交占比', '二次成交占比', '老客成交占比', '总成交率', '首次业绩占比', '二次业绩占比', '老客业绩占比'
        ],
        '价值数据' => [
            "表单成本", "有效表单成本", "转微成本", "预约成本", "到诊成本", "新客投产", "总投产"
        ],
    ];

    public static $BaseHeaders = [
        '日期'   => '',
        '渠道'   => '',
        '互动'   => '',
        '评论'   => '',
        '私信'   => '',
        '评论转出' => '',
        '推广数据' => [
            '展现', '点击',  '点击率', '点击成本', '消费(实)', '消费（虚）', '总表单', "有效表单", '空号',
            "无效表单", '重复表单', '未跟踪', '有效表单占比', "空号占比", '无效表单占比',
            '重复表单占比',
            '转微', '总预约'
        ],
        '咨询数据' => [
            '建档', '未建档', '新客到院', '二次到院', '老客到院', '新客首次占比', '新客二次占比', '老客占比'
        ],
        '院内数据' => [
            '首次成交', '二次成交', "老客成交", '总成交', '首次业绩', '二次业绩', '老客业绩', '总业绩', "首次单体", "二次单体", "老客单体", '总单体', '首次成交占比', '二次成交占比', '老客成交占比', '总成交率', '首次业绩占比', '二次业绩占比', '老客业绩占比'
        ],
        '价值数据' => [
            "表单成本", "有效表单成本", "转微成本", "预约成本", "到诊成本", "新客投产", '总投产'
        ]
    ];

    public static $ChannelHeaders = [
        '日期'   => '',
        '科室'   => '',
        '项目'   => '',
        '互动'   => '',
        '评论'   => '',
        '私信'   => '',
        '评论转出' => '',
        '推广数据' => [
            '展现', '点击', '点击率', '点击成本', '消费(实)', '消费（虚）', '总表单', "有效表单", '空号',
            "无效表单", '重复表单', '未跟踪', '有效表单占比', "空号占比", '无效表单占比',
            '重复表单占比',
            '转微', '总预约'
        ],
        '咨询数据' => [
            '建档', '未建档', '新客到院', '二次到院', '老客到院', '新客首次占比', '新客二次占比', '老客占比'
        ],
        '院内数据' => [
            '首次成交', '二次成交', "老客成交", '总成交', '首次业绩', '二次业绩', '老客业绩', '总业绩', "首次单体", "二次单体", "老客单体", '总单体', '首次成交占比', '二次成交占比', '老客成交占比', '总成交率', '首次业绩占比', '二次业绩占比', '老客业绩占比'
        ],
        '价值数据' => [
            "表单成本", "有效表单成本", "转微成本", "预约成本", "到诊成本", "新客投产", '总投产'
        ]
    ];

    public static $WeiboHeaders = [
        '日期'     => '',
        '科室'     => '',
        '项目'     => '',
        '曝光量'    => '',
        '账户消费'   => '',
        '实际消费'   => '',
        '千次曝光成本' => '',
        '推广数据'   => [
            '互动数', '导流数', '评论', '表单提交', '点赞', '转发', '收藏', '加关注数', '互动成本', '导流成本', '评论成本', '表单成本', '点赞成本', '转发成本', '收藏成本', '加关注成本'
        ],
        '咨询数据'   => [
            '有效对话', '有效对话成本', '微私微评', '私评转出', '转出率', '评论首次到院', '表单首次到院', '私信首次到院', '关注首次到院', '其他首次到院', '首次到院', '评论二次到院', '表单二次到院', '私信二次到院',
            '关注二次到院', '其他二次到院', '二次到院', '评论老客到院', '表单老客到院', '私信老客到院', '关注老客到院', '其他老客到院', '老客到院', '总到院', '首次目标', '首次目标完成率', '首次目标差', '新客首次成本', '首次到院率'
        ],
        '院内数据'   => [
            '评论总业绩', '表单总业绩', '私信总业绩', '关注总业绩', '其他总业绩', '总业绩', '新客首次单价', '业绩目标',
            '业绩目标完成率', '业绩目标差'
        ],
        '价值数据'   => [
            '新客投产比',
            'ROI',
        ]
    ];


    /**
     * TestExport constructor.
     * @param $parser
     */
    public function __construct($parser)
    {
        $this->parser = $parser;
    }


    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];

        $channelExcel = $this->parser->toArray("channel");
        $sheets[]     = new BaseSheet($channelExcel, '信息流数据汇总表', static::$BaseHeaders);

        $accountExcel = $this->parser->toArray("account");
        $sheets[]     = new AccountSheet($accountExcel, '计划账户数据汇总表', static::$AccountHeaders);

        $channelDepartment = $this->parser->toArray('channel-department');
        foreach ($channelDepartment as $channelName => $channel) {
            if (preg_match("/微博/", $channelName)) {
                $sheets[] = new WeiboSheet($channel, $channelName, static::$WeiboHeaders);
            } else {
                $sheets[] = new ChannelSheet($channel, $channelName, static::$ChannelHeaders);
            }
        }


        return $sheets;
    }
}
