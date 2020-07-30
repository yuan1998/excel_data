<?php

namespace App\Admin\Extensions\Exporter;

use App\models\CrmGrabLog;
use App\Models\WeiboFormData;
use Encore\Admin\Grid\Exporters\ExcelExporter;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class FormDataExporter extends ExcelExporter implements WithMapping, WithStrictNullComparison
{
    protected $fileName = '表单数据_导出数据.xlsx';

    protected $headings = [
        '科室',
        '渠道',
        '手机号码',
        '病种',
        '所属账户',
        '日期',
        '表单名称',
    ];

    /**
     * @param mixed $form
     *
     * @return array
     */
    public function map($form): array
    {
        $project= $form->projects->first();
        return [
            $form->department ? $form->department['title'] : '无科室',
            $form->channel ? $form->channel['title'] : '无渠道',
            $form->phones ? $form->phones->pluck('phone')->join(',') : '无电话',
            $project ? $project->title : '无病种',
            $form->account ? $form->account->name : '无账户',
            $form->date,
            $form->code,
        ];
    }
}
