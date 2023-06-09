<?php

namespace App\Parsers;


use App\Helpers;
use App\Models\ArrivingData;
use App\Models\BillAccountData;
use App\Models\FormData;
use App\Models\SpendData;
use App\Models\WeiboSpend;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ExcelFieldsCount
{
    /**
     * @var Collection
     */
    public $formData;
    public $formDataCount;

    /**
     * @var Collection
     */
    public $spendData;
    public $spendDataCount;

    /**
     * @var Collection
     */
    public $arrivingData;
    public $arrivingDataCount;

    /**
     * @var Collection
     */
    public $billAccountData;
    public $billAccountDataCount;


    /**
     * ExcelFieldsCount constructor.
     * @param Collection|array $data
     */
    public function __construct($data)
    {
        $this->formData        = Arr::get($data, 'formData', []);
        $this->spendData       = Arr::get($data, 'spendData', []);
        $this->arrivingData    = Arr::get($data, 'arrivingData', []);
        $this->billAccountData = Arr::get($data, 'billAccountData', []);
    }

    public function toArray()
    {
        return [
            'formData'        => $this->formData,
            'spendData'       => $this->spendData,
            'arrivingData'    => $this->arrivingData,
            'billAccountData' => $this->billAccountData,
        ];
    }

    public function toBaseExcel()
    {
        $formData        = $this->getCountData('formData');
        $spendData       = $this->getCountData('spendData');
        $billAccountData = $this->getCountData('billAccountData');
        $arrivingData    = $this->getCountData('arrivingData');


        $effectiveForm   = $formData['intention-2'] + $formData['intention-3'] + $formData['intention-4'] + $formData['intention-5'];
        $totalTransition = $arrivingData['new_transaction'] + $arrivingData['old_transaction'];

        $offSpend       = round($spendData['off_spend'], 2);
        $unArchiveCount = Arr::get($formData, 'is_archive-2', 0) + Arr::get($formData, 'is_archive', 0);
        return [
            // 总互动
            'interactive'                      => Arr::get($spendData, 'interactive', 0),
            // 评论
            'comment'                          => 0,
            // 私信
            'message'                          => 0,
            // 评论转出
            'comment_turn'                     => 0,
            // 展现
            'show'                             => $spendData['show'],
            // 点击
            'click'                            => $spendData['click'],
            // 点击率
            'click_rate'                       => Helpers::toRate(Helpers::divisionOfSelf($spendData['click'], $spendData['show'])),
            // 点击单价
            'click_spend'                      => round(Helpers::divisionOfSelf($spendData['spend'], $spendData['click']), 2),
            // 实消费  返点钱
            'off_spend'                        => $offSpend,
            // 虚消费  充值钱
            'spend'                            => $spendData['spend'],
            // 总表单数
            'form_count'                       => $formData['form_count'],
            // 有效表单数    =   意向1 + 意向2 + 意向3 + 意向4
            'effective_form'                   => $effectiveForm,
            // 空号 = 意向5
            'empty_phone_form'                 => $formData['intention-6'],
            // 未接通 =  意向4
            'invalid_form'                     => $formData['intention-5'],
            // 重复表单
            'repeat_form'                      => $formData['is_repeat-2'],
            // 未下预约单
            'un_follow_count'                  => $formData['intention-1'] - $unArchiveCount,
            // 有效表单占比
            'effective_form_rate'              => Helpers::toRate(Helpers::divisionOfSelf($effectiveForm, $formData['form_count'])),
            // 空号占比
            'empty_phone_form_rate'            => Helpers::toRate(Helpers::divisionOfSelf($formData['intention-6'], $formData['form_count'])),
            // 未接通占比
            'invalid_form_rate'                => Helpers::toRate(Helpers::divisionOfSelf($formData['intention-5'], $formData['form_count'])),
            // 重复表单占比
            'repeat_form_rate'                 => Helpers::toRate(Helpers::divisionOfSelf($formData['is_repeat-2'], $formData['form_count'])),
            // 转微
            'turn_weixin'                      => $formData['turn_weixin-1'],
            // 总预约
            'reservation'                      => $formData['intention-2'],
            // 建档数
            'archive_count'                    => $formData['is_archive-1'],
            // 未建档
            'un_archive_count'                 => $unArchiveCount,
            // 新客到院
            'new_first_arriving'               => $arrivingData['new_first'],
            // 二次到院
            'new_again_arriving'               => $arrivingData['new_again'],
            // 老客到院
            'old_arriving'                     => $arrivingData['old'],

            // 新客首次到院率 = 新客首次 / 总建档
            'new_first_rate'                   => Helpers::toRate(Helpers::divisionOfSelf($arrivingData['new_first'], $formData['is_archive-1'])),
            // 新客二次到院率 = 新客二次 / 总建档
            'new_again_rate'                   => Helpers::toRate(Helpers::divisionOfSelf($arrivingData['new_again'], $formData['is_archive-1'])),
            // 老客到院率
            'old_rate'                         => Helpers::toRate(Helpers::divisionOfSelf($arrivingData['old'], $formData['is_archive-1'])),


            // 首次成交
            'new_first_transaction'            => $arrivingData['new_first_transaction'],
            // 二次成交
            'new_again_transaction'            => $arrivingData['new_again_transaction'],
            // 老客成交
            'old_transaction'                  => $arrivingData['old_transaction'],
            // 总成交
            'total_transaction'                => $totalTransition,
            // 首次业绩
            'new_first_account'                => $billAccountData['new_first_account'],
            // 二次业绩
            'new_again_account'                => $billAccountData['new_again_account'],
            // 老客业绩
            'old_account'                      => $billAccountData['old_account'],
            // 总业绩
            'total_account'                    => $billAccountData['total_account'],
            // 首次单体 = 新客首次业绩  / 新客首次成交数
            'new_first_average'                => round(Helpers::divisionOfSelf($billAccountData['new_first_account'], $arrivingData['new_first_transaction']), 2),
            // 二次单体  = 新客二次业绩  / 新客二次成交数
            'new_again_average'                => round(Helpers::divisionOfSelf($billAccountData['new_again_account'], $arrivingData['new_again_transaction']), 2),
            // 老客单体 = 老客业绩  / 老客成交数
            'old_average'                      => round(Helpers::divisionOfSelf($billAccountData['old_account'], $arrivingData['old_transaction']), 2),
            // 总单体 = 总业绩 / 总成交
            'total_average'                    => round(Helpers::divisionOfSelf($billAccountData['total_account'], $totalTransition), 2),
            // 首次成交占比 = 首次成交 / 总成交
            'new_first_transaction_proportion' => Helpers::toRate(Helpers::divisionOfSelf($arrivingData['new_first_transaction'], $totalTransition)),
            // 二次成交占比 = 二次成交 / 总成交
            'new_again_transaction_proportion' => Helpers::toRate(Helpers::divisionOfSelf($arrivingData['new_again_transaction'], $totalTransition)),
            // 老客成交占比 = 老客成交 / 总成交
            'old_transaction_proportion'       => Helpers::toRate(Helpers::divisionOfSelf($arrivingData['old_transaction'], $totalTransition)),


            // 新客首次成交率 = 新客首次成交数 / 新客首次到院数
            'new_first_transaction_rate'       => Helpers::toRate(Helpers::divisionOfSelf($arrivingData['new_first_transaction'], $arrivingData['new_first'])),
            // 新客二次成交率 = 新客二次成交数 / 新客二次到院数
            'new_again_transaction_rate'       => Helpers::toRate(Helpers::divisionOfSelf($arrivingData['new_again_transaction'], $arrivingData['new_again'])),
            // 老客成交率 = 老客成交数 / 老客到院数
            'old_transaction_rate'             => Helpers::toRate(Helpers::divisionOfSelf($arrivingData['old_transaction'], $arrivingData['old'])),
            // 总成交率 =  总成交 / 总到院
            'total_transaction_proportion'     => Helpers::toRate(Helpers::divisionOfSelf($totalTransition, $arrivingData['arriving_count'])),

            // 首次业绩占比 = 首次业绩 / 总业绩
            'new_first_account_proportion'     => Helpers::toRate(Helpers::divisionOfSelf($billAccountData['new_first_account'], $billAccountData['total_account'])),
            // 二次业绩占比 = 二次业绩 / 总业绩
            'new_again_account_proportion'     => Helpers::toRate(Helpers::divisionOfSelf($billAccountData['new_again_account'], $billAccountData['total_account'])),
            // 老客业绩占比 = 老客业绩 / 总业绩
            'old_account_proportion'           => Helpers::toRate(Helpers::divisionOfSelf($billAccountData['old_account'], $billAccountData['total_account'])),
            // 表单成本 = 消费 / 总表单数
            'form_spend'                       => round(Helpers::divisionOfSelf($offSpend, $formData['form_count']), 2),
            // 有效表单成本 =  消费 / 有效表单数
            'effective_form_spend'             => round(Helpers::divisionOfSelf($offSpend, $effectiveForm), 2),
            // 转微成本 = 消费 / 转微
            'turn_weixin_spend'                => round(Helpers::divisionOfSelf($offSpend, $formData['turn_weixin-1']), 2),
            // 预约成本 = 消费 / 预约
            'reservation_spend'                => round(Helpers::divisionOfSelf($offSpend, $formData['intention-2']), 2),
            // 到诊成本 = 消费 / 到院
            'arriving_spend'                   => round(Helpers::divisionOfSelf($offSpend, $arrivingData['arriving_count']), 2),
            // 到诊(首次)成本 = 消费 / 新客到院
            'arriving_new_spend'               => round(Helpers::divisionOfSelf($offSpend, $arrivingData['new_first']), 2),
            // 新客投产比 = 1 : (总消费 / 新客业绩)
            'proportion_new'                   => Helpers::toRatio($offSpend, $billAccountData['new_account']),
            // 总投产比 = 1 : ( 总消费 / 总业绩)
            'proportion_total'                 => Helpers::toRatio($offSpend, $billAccountData['total_account']),
        ];
    }


    public function toAccountExcel()
    {
        $formData        = $this->getCountData('formData');
        $spendData       = $this->getCountData('spendData');
        $billAccountData = $this->getCountData('billAccountData');
        $arrivingData    = $this->getCountData('arrivingData');

        $effectiveForm   = $formData['intention-2'] + $formData['intention-3'] + $formData['intention-4'] + $formData['intention-5'];
        $totalTransition = $arrivingData['new_transaction'] + $arrivingData['old_transaction'];

        $offSpend = round($spendData['off_spend'], 2);

        return [
            // 总互动
            'interactive'                      => Arr::get($spendData, 'interactive', 0),
            // 评论
            'comment'                          => 0,
            // 私信
            'message'                          => 0,
            // 评论转出
            'comment_turn'                     => 0,
            // 展现
            'show'                             => $spendData['show'],
            // 点击
            'click'                            => $spendData['click'],
            // 点击率
            'click_rate'                       => Helpers::toRate(Helpers::divisionOfSelf($spendData['click'], $spendData['show'])),
            // 点击单价
            'click_spend'                      => round(Helpers::divisionOfSelf($spendData['spend'], $spendData['click']), 2),
            // 实消费
            'off_spend'                        => $offSpend,
            // 虚消费
            'spend'                            => $spendData['spend'],
            // 总表单数
            'form_count'                       => $formData['form_count'],
            // 有效表单数 =   意向1 + 意向2 + 意向3 +意向4
            'effective_form'                   => $effectiveForm,
            // 空号 = 意向5
            'empty_phone_form'                 => $formData['intention-6'],
            // 未接通
            'invalid_form'                     => $formData['intention-5'],
            // 重复表单
            'repeat_form'                      => $formData['is_repeat-2'],
            // 有效表单占比
            'effective_form_rate'              => Helpers::toRate(Helpers::divisionOfSelf($effectiveForm, $formData['form_count'])),
            // 空号占比
            'empty_phone_form_rate'            => Helpers::toRate(Helpers::divisionOfSelf($formData['intention-6'], $formData['form_count'])),
            // 未接通占比
            'invalid_form_rate'                => Helpers::toRate(Helpers::divisionOfSelf($formData['intention-5'], $formData['form_count'])),
            // 重复表单占比
            'repeat_form_rate'                 => Helpers::toRate(Helpers::divisionOfSelf($formData['is_repeat-2'], $formData['form_count'])),
            // 转微
            'turn_weixin'                      => $formData['turn_weixin-1'],
            // 总预约
            'reservation'                      => $formData['intention-2'],
            // 建档数
            'archive_count'                    => $formData['is_archive-1'],
            // 一级建档
            'first_archive'                    => $formData['intention-2'],
            // 二级建档
            'second_archive'                   => $formData['intention-3'],
            // 三级建档
            'third_archive'                    => $formData['intention-4'],
            // 四级建档
            'fourth_archive'                   => $formData['intention-5'],
            // 五级建档
            'fifth_archive'                    => $formData['intention-6'],
            // 未建档
            'un_archive_count'                 => $formData['is_archive-0'],
            // 新客到院
            'new_first_arriving'               => $arrivingData['new_first'],
            // 二次到院
            'new_again_arriving'               => $arrivingData['new_again'],
            // 老客到院
            'old_arriving'                     => $arrivingData['old'],

            // 新客首次到院率 = 新客首次 / 总建档
            'new_first_rate'                   => Helpers::toRate(Helpers::divisionOfSelf($arrivingData['new_first'], $formData['is_archive-1'])),
            // 新客二次到院率 = 新客二次 / 总建档
            'new_again_rate'                   => Helpers::toRate(Helpers::divisionOfSelf($arrivingData['new_again'], $formData['is_archive-1'])),
            // 老客到院率
            'old_rate'                         => Helpers::toRate(Helpers::divisionOfSelf($arrivingData['old'], $formData['is_archive-1'])),

            // 首次成交
            'new_first_transaction'            => $arrivingData['new_first_transaction'],
            // 二次成交
            'new_again_transaction'            => $arrivingData['new_again_transaction'],
            // 老客成交
            'old_transaction'                  => $arrivingData['old_transaction'],
            // 总成交
            'total_transaction'                => $totalTransition,
            // 首次业绩
            'new_first_account'                => $billAccountData['new_first_account'],
            // 二次业绩
            'new_again_account'                => $billAccountData['new_again_account'],
            // 老客业绩
            'old_account'                      => $billAccountData['old_account'],
            // 总业绩
            'total_account'                    => $billAccountData['total_account'],
            // 首次单体 = 新客首次业绩  / 新客首次成交数
            'new_first_average'                => round(Helpers::divisionOfSelf($billAccountData['new_first_account'], $arrivingData['new_first_transaction']), 2),
            // 二次单体  = 新客二次业绩  / 新客二次成交数
            'new_again_average'                => round(Helpers::divisionOfSelf($billAccountData['new_again_account'], $arrivingData['new_again_transaction']), 2),
            // 老客单体 = 老客业绩  / 老客成交数
            'old_average'                      => round(Helpers::divisionOfSelf($billAccountData['old_account'], $arrivingData['old_transaction']), 2),
            // 总单体 = 总业绩 / 总到院
            'total_average'                    => round(Helpers::divisionOfSelf($billAccountData['total_account'], $totalTransition), 2),
            // 首次成交占比 = 首次成交 / 总成交
            'new_first_transaction_proportion' => Helpers::toRate(Helpers::divisionOfSelf($arrivingData['new_first_transaction'], $totalTransition)),
            // 二次成交占比 = 二次成交 / 总成交
            'new_again_transaction_proportion' => Helpers::toRate(Helpers::divisionOfSelf($arrivingData['new_again_transaction'], $totalTransition)),
            // 老客成交占比 = 老客成交 / 总成交
            'old_transaction_proportion'       => Helpers::toRate(Helpers::divisionOfSelf($arrivingData['old_transaction'], $totalTransition)),


            // 新客首次成交率 = 新客首次成交数 / 新客首次到院数
            'new_first_transaction_rate'       => Helpers::toRate(Helpers::divisionOfSelf($arrivingData['new_first_transaction'], $arrivingData['new_first'])),
            // 新客二次成交率 = 新客二次成交数 / 新客二次到院数
            'new_again_transaction_rate'       => Helpers::toRate(Helpers::divisionOfSelf($arrivingData['new_again_transaction'], $arrivingData['new_again'])),
            // 老客成交率 = 老客成交数 / 老客到院数
            'old_transaction_rate'             => Helpers::toRate(Helpers::divisionOfSelf($arrivingData['old_transaction'], $arrivingData['old'])),
            // 总成交率 =  总成交 / 总到院
            'total_transaction_proportion'     => Helpers::toRate(Helpers::divisionOfSelf($totalTransition, $arrivingData['arriving_count'])),


            // 首次业绩占比 = 首次业绩 / 总业绩
            'new_first_account_proportion'     => Helpers::toRate(Helpers::divisionOfSelf($billAccountData['new_first_account'], $billAccountData['total_account'])),
            // 二次业绩占比 = 二次业绩 / 总业绩
            'new_again_account_proportion'     => Helpers::toRate(Helpers::divisionOfSelf($billAccountData['new_again_account'], $billAccountData['total_account'])),
            // 老客业绩占比 = 老客业绩 / 总业绩
            'old_account_proportion'           => Helpers::toRate(Helpers::divisionOfSelf($billAccountData['old_account'], $billAccountData['total_account'])),
            // 表单成本 = 消费 / 总表单数
            'form_spend'                       => round(Helpers::divisionOfSelf($offSpend, $formData['form_count']), 2),
            // 有效表单成本 =  消费 / 有效表单数
            'effective_form_spend'             => round(Helpers::divisionOfSelf($offSpend, $effectiveForm), 2),
            // 转微成本 = 消费 / 转微
            'turn_weixin_spend'                => round(Helpers::divisionOfSelf($offSpend, $formData['turn_weixin-1']), 2),
            // 预约成本 = 消费 / 预约
            'reservation_spend'                => round(Helpers::divisionOfSelf($offSpend, $formData['intention-2']), 2),
            // 到诊成本 = 消费 / 到院
            'arriving_spend'                   => round(Helpers::divisionOfSelf($offSpend, $arrivingData['arriving_count']), 2),
            // 到诊(首次)成本 = 消费 / 新客到院
            'arriving_new_spend'               => round(Helpers::divisionOfSelf($offSpend, $arrivingData['new_first']), 2),
            // 新客投产比 = 1 : (总消费 / 新客业绩)
            'proportion_new'                   => Helpers::toRatio($offSpend, $billAccountData['new_account']),
            // 总投产比 = 1 : ( 总消费 / 总业绩)
            'proportion_total'                 => Helpers::toRatio($offSpend, $billAccountData['total_account']),
        ];
    }


    public function toWeiboExcel()
    {
        $formData        = $this->getCountData('formData', true);
        $spendData       = $this->getCountData('spendData', true);
        $billAccountData = $this->getCountData('billAccountData', true);
        $arrivingData    = $this->getCountData('arrivingData', true);

        return [
            // 展现
            'show'                             => $spendData['show'],
            // 虚消费
            'off_spend'                        => $spendData['off_spend'],
            // 实消费
            'spend'                            => $spendData['spend'],
            // 千次曝光成本
            'thousand_show_spend'              => round(Helpers::divisionOfSelf($spendData['spend'], Helpers::divisionOfSelf($spendData['show'], 1000)), 2),
            // 互动数
            'intention_count'                  => $spendData['click'],
            // 导流数
            'diversions_count'                 => $spendData['diversions'],
            // 评论
            'comment_count'                    => 0,
            // 表单提交
            'form_count'                       => $formData['form_count'],
            // 点赞
            'like_count'                       => $spendData['like'],
            // 转发
            'share_count'                      => $spendData['share'],
            // 收藏
            'start_count'                      => $spendData['start'],
            // 关注数
            'follow_count'                     => $spendData['follow'],
            // 互动成本
            'intentions_spend'                 => round(Helpers::divisionOfSelf($spendData['spend'], $spendData['click']), 2),
            // 导流成本
            'diversions_spend'                 => round(Helpers::divisionOfSelf($spendData['spend'], $spendData['diversions']), 2),
            // 评论陈本
            'comment_spend'                    => 0,
            // 表单陈本
            'form_spend'                       => round(Helpers::divisionOfSelf($spendData['spend'], $formData['form_count']), 2),
            // 点赞成本
            'like_spend'                       => round(Helpers::divisionOfSelf($spendData['spend'], $spendData['like']), 2),
            // 转发成本
            'share_spend'                      => round(Helpers::divisionOfSelf($spendData['spend'], $spendData['share']), 2),
            // 收藏成本
            'start_spend'                      => round(Helpers::divisionOfSelf($spendData['spend'], $spendData['start']), 2),
            // 关注成本
            'follow_spend'                     => round(Helpers::divisionOfSelf($spendData['spend'], $spendData['follow']), 2),
            // 有效对话
            'effective_chat'                   => 0,
            // 有效对话成本
            'effective_chat_spend'             => 0,
            // 微私微评
            'weibo_message_weibo_comment'      => 0,
            // 私评转出
            'weibo_message_weibo_comment_turn' => 0,
            // 转出率
            'turn_proportion'                  => 0,
            // 评论首次到院
            'first_arriving_comment'           => data_get($arrivingData, 'first_arriving_comment', 0),
            // 表单首次到院
            'first_arriving_form'              => data_get($arrivingData, 'first_arriving_form', 0),
            // 私信首次到院
            'first_arriving_message'           => data_get($arrivingData, 'first_arriving_message', 0),
            // 关注首次到院
            'first_arriving_follow'            => data_get($arrivingData, 'first_arriving_follow', 0),
            // 其他首次到院
            'first_arriving_other'             => data_get($arrivingData, 'first_arriving_other', 0),
            // 首次到院
            'first_arriving'                   => data_get($arrivingData, 'new_first', 0),

            // 评论二次到院
            'again_arriving_comment'           => data_get($arrivingData, 'again_arriving_comment', 0),
            // 表单二次到院
            'again_arriving_form'              => data_get($arrivingData, 'again_arriving_form', 0),
            // 私信二次到院
            'again_arriving_message'           => data_get($arrivingData, 'again_arriving_message', 0),
            // 关注二次到院
            'again_arriving_follow'            => data_get($arrivingData, 'again_arriving_follow', 0),
            // 其他二次到院
            'again_arriving_other'             => data_get($arrivingData, 'again_arriving_other', 0),
            // 二次到院
            'again_arriving'                   => data_get($arrivingData, 'new_again', 0),

            // 评论老客到院
            'old_arriving_comment'             => data_get($arrivingData, 'old_arriving_comment', 0),
            // 表单老客到院
            'old_arriving_form'                => data_get($arrivingData, 'old_arriving_form', 0),
            // 私信老客到院
            'old_arriving_message'             => data_get($arrivingData, 'old_arriving_message', 0),
            // 关注老客到院
            'old_arriving_follow'              => data_get($arrivingData, 'old_arriving_follow', 0),
            // 其他老客到院
            'old_arriving_other'               => data_get($arrivingData, 'old_arriving_other', 0),
            // 老客到院
            'old_arriving'                     => data_get($arrivingData, 'old', 0),

            // 总到院
            'total_arriving'                   => data_get($arrivingData, 'arriving_count', 0),
            // 首次目标
            'first_target'                     => 0,
            // 首次目标完成率
            'first_target_complete'            => 0,
            // 首次目标差
            'first_target_less'                => 0,
            // 首次成本
            'first_arriving_spend'             => round(Helpers::divisionOfSelf($spendData['spend'], data_get($arrivingData, 'arriving_count', 0)), 2),
            // 首次到院率 = 新客首次到院 / 总表单
            'first_arriving_proportion'        => Helpers::toRate(Helpers::divisionOfSelf(data_get($arrivingData, 'new_first', 0), $formData['form_count'])),
            // 评论总业绩
            'comment_account'                  => data_get($billAccountData, 'comment_account', 0),
            // 表单总业绩
            'form_account'                     => data_get($billAccountData, 'form_account', 0),
            // 私信总业绩
            'message_account'                  => data_get($billAccountData, 'message_account', 0),
            // 关注总业绩
            'follow_account'                   => data_get($billAccountData, 'follow_account', 0),
            // 其他总业绩
            'other_account'                    => data_get($billAccountData, 'other_account', 0),
            // 总业绩
            'total_account'                    => data_get($billAccountData, 'total_account', 0),

            // 新客首次单价
            'first_average'                    => round(Helpers::divisionOfSelf($billAccountData['new_first_account'], $arrivingData['new_first_transaction']), 2),
            // 业绩目标
            'account_target'                   => 0,
            // 业绩目标完成率
            'account_complete'                 => 0,
            // 业绩目标差
            'account_less'                     => 0,

            // 新客投产比 = 1 : (总消费 / 新客业绩)
            'proportion_new'                   => Helpers::toRatio($spendData['spend'], $billAccountData['new_account']),
            // 投产比
            'proportion_total'                 => Helpers::toRatio($spendData['spend'], $billAccountData['total_account']),
        ];
    }

    public function getCountData($name, $weibo = false)
    {
        switch ($name) {
            case "formData":
                if (!$this->formDataCount) {
                    $this->formDataCount = $this->parserFormDataToCount();
                }
                return $this->formDataCount;
            case "spendData":
                if (!$this->spendDataCount) {
                    $this->spendDataCount = $this->parserSpendDataToCount($weibo);
                }
                return $this->spendDataCount;
            case "arrivingData":
                if (!$this->arrivingDataCount) {
                    $this->arrivingDataCount = $this->parserArrivingDataToCount($weibo);
                }
                return $this->arrivingDataCount;
            case "billAccountData":
                if (!$this->billAccountDataCount) {
                    $this->billAccountDataCount = $this->parserBillaccountDataToCount($weibo);
                }
                return $this->billAccountDataCount;
        }
    }

    public function toConsultantData()
    {
        $formData        = $this->getCountData('formData');
        $billAccountData = $this->getCountData('billAccountData');
        $arrivingData    = $this->getCountData('arrivingData');

        $allIntention = $formData['intention-2'] + $formData['intention-3'] + $formData['intention-4'] + $formData['intention-5'] + $formData['intention-6'];

        $totalArriving          = $arrivingData['arriving_count'];
        $less5IntentionNewFirst = $formData['is_archive-1'] - $formData['intention-6'];
        $totalTransition        = $arrivingData['new_transaction'] + $arrivingData['old_transaction'];

        return [
            // 建档数据
            '总表单数'        => $formData['form_count'],
            '建档数'         => $formData['is_archive-1'],
            '重复建档'        => $formData['is_repeat-2'],
            '未下单'         => $formData['intention-1'],
            '预约单总数'       => $allIntention,
            '一级预约'        => $formData['intention-2'],
            '二级预约'        => $formData['intention-3'],
            '三级预约'        => $formData['intention-4'],
            '四级预约'        => $formData['intention-5'],
            '五级预约'        => $formData['intention-6'],
            '一级占比'        => Helpers::toRate(Helpers::divisionOfSelf($formData['intention-2'], $allIntention)),
            '二级占比'        => Helpers::toRate(Helpers::divisionOfSelf($formData['intention-3'], $allIntention)),
            '三级占比'        => Helpers::toRate(Helpers::divisionOfSelf($formData['intention-4'], $allIntention)),
            '四级占比'        => Helpers::toRate(Helpers::divisionOfSelf($formData['intention-5'], $allIntention)),
            '五级占比'        => Helpers::toRate(Helpers::divisionOfSelf($formData['intention-6'], $allIntention)),

            // 到院数据
            '新客首次'        => $arrivingData['new_first'],
            '新客二次'        => $arrivingData['new_again'],
            '老客'          => $arrivingData['old'],
            '到院总数'        => $totalArriving,
            '新客首次到院率'     => Helpers::toRate(Helpers::divisionOfSelf($arrivingData['new_first'], $formData['is_archive-1'])),
            '除去5级首次到院率'   => Helpers::toRate(Helpers::divisionOfSelf(
                $arrivingData['new_first'],
                $less5IntentionNewFirst
            )),
            '除去4,5级首次到院率' => Helpers::toRate(Helpers::divisionOfSelf(
                $arrivingData['new_first'],
                $less5IntentionNewFirst - $formData['intention-5']
            )),
            '新客二次到院占比'    => Helpers::toRate(Helpers::divisionOfSelf(
                $arrivingData['new_again'],
                $totalArriving
            )),
            '老客到院占比'      => Helpers::toRate(Helpers::divisionOfSelf(
                $arrivingData['old'],
                $totalArriving
            )),
            '总到院率'        => Helpers::toRate(Helpers::divisionOfSelf(
                $totalArriving,
                $formData['is_archive-1']
            )),

            // 成交数据
            '新客首次成交'      => $arrivingData['new_first_transaction'],
            '新客二次成交'      => $arrivingData['new_again_transaction'],
            '老客成交'        => $arrivingData['old_transaction'],
            '成交总数'        => $totalTransition,
            '新客首次成交率'     => Helpers::toRate(Helpers::divisionOfSelf($arrivingData['new_first_transaction'], $arrivingData['new_first'])),
            '新客二次成交率'     => Helpers::toRate(Helpers::divisionOfSelf($arrivingData['new_again_transaction'], $arrivingData['new_again'])),
            '老客成交率'       => Helpers::toRate(Helpers::divisionOfSelf($arrivingData['old_transaction'], $arrivingData['old'])),
            '总成交率'        => Helpers::toRate(Helpers::divisionOfSelf($totalTransition, $totalArriving)),

            // 业绩数据
            '新客首次业绩'      => $billAccountData['new_first_account'],
            '新客二次业绩'      => $billAccountData['new_again_account'],
            '老客业绩'        => $billAccountData['old_account'],
            '业绩小计'        => $billAccountData['total_account'],
            '新客首次成交单体'    => round(Helpers::divisionOfSelf(
                $billAccountData['new_first_account'],
                $arrivingData['new_first_transaction']
            ), 2),
            '新客二次成交单体'    => round(Helpers::divisionOfSelf(
                $billAccountData['new_again_account'],
                $arrivingData['new_again_transaction']
            ), 2),
            '老客成交单体'      => round(Helpers::divisionOfSelf(
                $billAccountData['old_account'],
                $arrivingData['old_transaction']
            ), 2),
            '总成交单体'       => round(Helpers::divisionOfSelf(
                $billAccountData['total_account'],
                $totalTransition
            ), 2),
            '新客首次挂号单体'    => round(Helpers::divisionOfSelf(
                $billAccountData['new_first_account'],
                $arrivingData['new_first']
            ), 2),
            '新客二次挂号单体'    => round(Helpers::divisionOfSelf(
                $billAccountData['new_again_account'],
                $arrivingData['new_again']
            ), 2),
            '老客挂号单体'      => round(Helpers::divisionOfSelf(
                $billAccountData['old_account'],
                $arrivingData['old']
            ), 2),
            '总挂号单体'       => round(Helpers::divisionOfSelf(
                $billAccountData['total_account'],
                $totalArriving
            ), 2),
        ];

    }

    /**
     * @return array
     */
    public function parserFormDataToCount()
    {
        $result = FormData::$FormCountDataFormat;

        $result['form_count'] = $this->formData->count();

        foreach ($this->formData as $item) {
            $phone = $item->phones->first();

            if ($phone['is_repeat'] == 2) {
                $result["is_repeat-{$phone['is_repeat']}"]++;
            } else {
                $turn_weixin = $phone['turn_weixin'] ?? 0;
                if (!isset($result["is_archive-{$phone['is_archive']}"]))
                    $result["is_archive-{$phone['is_archive']}"] = 0;
                if (!isset($result["intention-{$phone['intention']}"]))
                    $result["intention-{$phone['intention']}"] = 0;
                if (!isset($result["turn_weixin-{$turn_weixin}"]))
                    $result["turn_weixin-{$turn_weixin}"] = 0;

                $result["is_archive-{$phone['is_archive']}"]++;
                $result["intention-{$phone['intention']}"]++;
                $result["turn_weixin-{$turn_weixin}"]++;
            }
        }
        return $result;
    }

    /**
     * @param boolean $weibo
     * @return array
     */
    public function parserSpendDataToCount($weibo)
    {
        $result = SpendData::$SpendCountDataFormat;

        foreach ($this->spendData as $item) {
            $result['spend']       += (float)Arr::get($item, 'spend', 0);
            $result['off_spend']   += (float)Arr::get($item, 'off_spend', 0);
            $result['interactive'] += (int)Arr::get($item, 'interactive', 0);
            $result['click']       += (int)Arr::get($item, 'click', 0);
            $result['show']        += (int)Arr::get($item, 'show', 0);

            if ($weibo && $item['data_snap']) {
                WeiboSpend::getWeiboSpendField($item['data_snap'], [
                    'diversions',
                    'like',
                    'share',
                    'start',
                    'follow',
                ], function ($field, $value) use (&$result) {
                    $result[$field] += (int)$value;
                });
            }

        }

        return $result;
    }

    /**
     * @param boolean $weibo
     * @return array
     */
    public function parserArrivingDataToCount($weibo)
    {
        $result                   = ArrivingData::$ArrivingCountDataFormat;
        $data                     = $this->arrivingData->unique(function ($value) {
            return data_get($value, 'customer_id') . data_get($value, 'reception_date');
        });
        $result['arriving_count'] = $data->count();

        foreach ($data as $value) {
            $weiboType = $weibo ? WeiboSpend::getWeiboType($value) : null;

            $transaction = preg_match('/是/', $value['is_transaction']);

            if (preg_match('/新客户/', $value['customer_status'])) {
                if (preg_match('/二次/', $value['again_arriving'])) {
                    $result['new_again']++;
                    $transaction && $result['new_again_transaction']++;

                    $weiboType && ($result["again_arriving_$weiboType"]++);
                } else {
                    $result['new_first']++;
                    $transaction && $result['new_first_transaction']++;
                    $weiboType && ($result["first_arriving_$weiboType"]++);
                }
            } else {
                $result['old']++;
                $transaction && $result['old_transaction']++;
                $weiboType && ($result["old_arriving_$weiboType"]++);
            }
        }

        $result['new_total']       = $result['new_first'] + $result['new_again'];
        $result['new_transaction'] = $result['new_again_transaction'] + $result['new_first_transaction'];
        return $result;
    }

    /**
     * @param boolean $weibo
     * @return array
     */
    public function parserBillAccountDataToCount($weibo)
    {
        $result = BillAccountData::$BillAccountCountDataFormat;

        foreach ($this->billAccountData as $value) {
            $customerStatus          = $value['customer_status'];
            $account                 = (float)($value['order_account'] ?? 0);
            $result['total_account'] += $account;

            $weiboType = $weibo ? WeiboSpend::getWeiboType($value) : null;
            $weiboType && ($result["{$weiboType}_account"] += $account);

            if ($customerStatus) {
                if (preg_match('/新客户/', $customerStatus)) {
                    if (preg_match('/是/', $value['again_arriving'])) {
                        $result['new_again_count']++;
                        $result['new_again_account'] += $account;
                    } else {
                        $result['new_first_count']++;
                        $result['new_first_account'] += $account;
                    }
                } else {
                    $result['old_count']++;
                    $result['old_account'] += $account;
                }
            }
        }

        $result['new_account'] = $result['new_first_account'] + $result['new_again_account'];
        $result['new_count']   = $result['new_first_count'] + $result['new_again_count'];

        return $result;
    }


}
