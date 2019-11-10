<?php

namespace App\Admin\Extensions\Exporter;

use App\models\CrmGrabLog;
use App\Models\WeiboFormData;
use Encore\Admin\Grid\Exporters\ExcelExporter;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class WeiboFormDataExporter extends ExcelExporter implements WithMapping, WithStrictNullComparison
{
    protected $fileName = '微博_Form_Data_List.xlsx';

    protected $headings = [
        '项目名称',
        '项目ID',
        '名称',
        '电话号码',
        '回访次数',
        '回访信息',
        '标签',
        '所属人',
        '表单提交时间',
        '抓取时间',
        '回访时间',
        '分配时间',
        '表单类型',
    ];

    /**
     * @param mixed $form
     *
     * @return array
     */
    public function map($form): array
    {

        return [
            $form->project_name,
            $form->project_id,
            $form->name,
            $form->phone,
            $form->recallLog ? count($form->recallLog) : 0,
            $form->comment ?? '-',
            isset(WeiboFormData::$TagList[$form->tags]) ? WeiboFormData::$TagList[$form->tags] : '未标记',
            data_get($form, 'weiboUser.username') ?? '-',
            $form->real_post_date,
            $form->upload_date,
            $form->recall_date ?? '-',
            $form->dispatch_date ?? '-',
            CrmGrabLog::$typeList[$form->type],
        ];
    }
}
