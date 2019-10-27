<?php

namespace App\Parsers;

use App\Helpers;
use App\Models\ArrivingData;
use App\Models\BillAccountData;
use App\Models\Channel;
use App\Models\DepartmentType;
use App\Models\FormData;
use App\Models\SpendData;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class ParserBase
{
    /**
     * @var array
     */
    public $channels_id;
    /**
     * @var array
     */
    public $departments_id;
    /**
     * @var array
     */
    public $dates;

    /**
     * @var Collection
     */
    public $_channels;
    /**
     * 表单数据
     * @var Collection
     */
    public $_formData;

    /**
     * 消费数据
     * @var Collection
     */
    public $_spendData;

    /**
     * 到院数据
     * @var Collection
     */
    public $_arrivingData;

    /**
     * 业绩数据
     * @var Collection
     */
    public $_billAccountData;
    /**
     * @var Collection
     */
    public $_mediumsData;
    /**
     * @var Collection
     */
    public $_archivesData;
    /**
     * @var \Illuminate\Database\Eloquent\Collection|static[]
     */
    public $_departments;

    public function __get($name)
    {
        switch ($name) {
            case 'departments' :
                return $this->getDepartments();
            case 'archivesData' :
                return $this->getAllArchives();
            case 'mediumsData' :
                return $this->getMediums();
            case 'billAccountData':
                return $this->getBillAccountData();
            case 'arrivingData':
                return $this->getArrivingData();
            case 'spendData':
                return $this->getSpendData();
            case 'formData':
                return $this->getFormData();
            case 'channels':
                return $this->getChannels();
            default:
                return null;
        }
    }

    /**
     * 获取 `所有渠道` 表单数据
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|Collection
     */
    public function getFormData()
    {
        if (!$this->_formData) {
            $this->_formData = FormData::query()
                ->with(['projects', 'department', 'phones'])
                ->whereBetween('date', $this->dates)
                ->whereIn('department_id', $this->departments_id)
                ->whereIn('form_type', $this->getFormType())
                ->get();
        }
        return $this->_formData;
    }

    /**
     * 获取 `所有渠道` 消费数据
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|Collection
     */
    public function getSpendData()
    {
        if (!$this->_spendData) {
            $this->_spendData = SpendData::query()
                ->with(['projects', 'department'])
                ->whereBetween('date', $this->dates)
                ->whereIn('department_id', $this->departments_id)
                ->whereIn('spend_type', $this->getFormType())
                ->get();
        }
        return $this->_spendData;
    }

    /**
     * 获取 `所有渠道` 到院数据
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|Collection
     */
    public function getArrivingData()
    {
        if (!$this->_arrivingData) {
            $this->_arrivingData = ArrivingData::query()
                ->whereIn('medium_id', $this->getMediumsId())
                ->whereBetween('reception_date', $this->dates)
                ->get();
        }
        return $this->_arrivingData;
    }

    /**
     * 获取 `所有渠道` 业绩数据
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|Collection
     */
    public function getBillAccountData()
    {
        if (!$this->_billAccountData) {
            $this->_billAccountData = BillAccountData::query()
                ->whereBetween('pay_date', $this->dates)
                ->whereIn('medium_id', $this->getMediumsId())
                ->get();
        }
        return $this->_billAccountData;
    }

    /**
     * 获取 Request 中的 `所有科室`
     * @return ParserBase[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getDepartments()
    {
        if (!$this->_departments) {
            $this->_departments = DepartmentType::query()
                ->with(['projects', 'projects.archives', 'archives'])
                ->whereIn('id', $this->departments_id)
                ->get();
        }
        return $this->_departments;
    }

    /**
     * 获取 Request 中的 `所有渠道`
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|Collection
     */
    public function getChannels()
    {
        if (!$this->_channels) {
            $this->_channels = Channel::query()
                ->with('mediums')
                ->whereIn('id', $this->channels_id)
                ->get();
        }
        return $this->_channels;
    }

    /**
     * 获取 `所有渠道` 中的 from_type(表单类型)
     * @return Collection
     */
    public function getFormType()
    {
        return collect(explode(',', $this->getChannels()->pluck('form_type')->implode(',')))->unique();
    }

    /**
     * 获取 `所有渠道` 中的 媒介类型(medium)
     * @return Collection
     */
    public function getMediums()
    {
        if (!$this->_mediumsData) {
            $result = collect();
            $this->getChannels()->each(function ($channel) use (&$result) {
                $result = $result->merge($channel->mediums);
            });
            $this->_mediumsData = $result;
        }
        return $this->_mediumsData;
    }

    /**
     * 获取 `所有渠道` 中的 媒介类型Id(medium_id)
     * @return Collection
     */
    public function getMediumsId()
    {
        return $this->getMediums()->pluck('id');
    }

    /**
     * 获取 `所有科室` 中的 `建档类型`(archive_type)
     * @return Collection
     */
    public function getAllArchives()
    {
        if (!$this->_mediumsData) {
            $result = collect();
            $this->getDepartments()->each(function ($department) use (&$result) {
                $result = $result->merge($department->archives);
            });
            $this->_archivesData = $result;
        }

        return $this->_archivesData;
    }

    /**
     * 获取 `所有科室` 中的 `建档类型ID`(archive_id)
     * @return Collection
     */
    public function getArchivesDataId()
    {
        return $this->getAllArchives()->pluck('id');
    }

    /**
     * @param Collection     $data
     * @param string|integer $id
     * @param bool           $d
     * @return Collection
     */
    public function filterDepartmentId($data, $id, $d = true)
    {
        return $data->filter(function ($item) use ($id, $d) {
            if ($d) {
                return $item->department_id == $id;
            } else {
                return $item->department_id != $id;
            }
        });
    }

    /**
     * @param Collection $data
     * @param            $archivesId
     * @param bool       $d
     * @return Collection
     */
    public function filterArchiveId($data, $archivesId, $d = true)
    {
        return $data->filter(function ($item) use ($archivesId, $d) {
            if ($d) {
                return $archivesId->contains($item->archive_id);
            } else {
                return !$archivesId->contains($item->archive_id);
            }
        });
    }

    /**
     * @param Collection|null $data
     * @param integer         $project
     * @param bool            $d
     * @return array|Collection
     */
    public function filterProject($data, $project, $d = true)
    {
        return $data ? $data->filter(function ($item) use ($project, $d) {
            if ($d) {
                return $item->projects->pluck('id')->contains($project->id);
            } else {
                return !$item->projects->contains(function ($item) use ($project) {
                    return $project->contains($item->id);
                });
            }
        }) : collect();
    }

    /**
     * @param Collection|null $data
     * @param array           $archives
     * @param bool            $d
     * @return array|Collection
     */
    public function filterArchives($data, $archives, $d = true)
    {
        return $data ? $data->filter(function ($item) use ($archives, $d) {
            if ($d) {
                return $archives->contains($item->archive_id);
            } else {
                return !$archives->contains($item->archive_id);
            }
        }) : collect();
    }

    /**
     * @param $dayData
     * @param $project
     * @return array
     */
    public function filterProjectData($dayData, $project)
    {
        $archives = $project->archives->pluck('id');
        return [
            'formData'        => $this->filterProject($dayData['formData'], $project),
            'spendData'       => $this->filterProject($dayData['spendData'], $project),
            'arrivingData'    => $this->filterArchives($dayData['arrivingData'], $archives),
            'billAccountData' => $this->filterArchives($dayData['billAccountData'], $archives),
        ];
    }

    /**
     * @param        $data
     * @param Carbon $date
     * @return array
     */
    public function filterDateStringData($data, $date)
    {
        $dateString = $date->toDateString();
        return $dayData = [
            'formData'        => Arr::get($data['formData'], $dateString, collect()),
            'spendData'       => Arr::get($data['spendData'], $dateString, collect()),
            'arrivingData'    => Arr::get($data['arrivingData'], $dateString, collect()),
            'billAccountData' => Arr::get($data['billAccountData'], $dateString, collect()),
        ];
    }

    /**
     * @param array $data
     * @return array
     */
    public function groupDataOfDate($data)
    {
        return [
            'formData'        => $data['formData']->groupBy('date'),
            'spendData'       => $data['spendData']->groupBy('date'),
            'arrivingData'    => $data['arrivingData']->groupBy('reception_date'),
            'billAccountData' => $data['billAccountData']->groupBy('pay_date'),
        ];
    }

    public function filterDepartmentData($data, $department)
    {
        $id         = $department->id;
        $archivesId = $department->archives->pluck('id');
        return [
            'formData'        => $this->filterDepartmentId($data['formData'], $id),
            'spendData'       => $this->filterDepartmentId($data['spendData'], $id),
            'billAccountData' => $this->filterArchiveId($data['billAccountData'], $archivesId),
            'arrivingData'    => $this->filterArchiveId($data['arrivingData'], $archivesId),
        ];
    }

    public function filterOtherData($dayData, $projectId, $archives)
    {
        return [
            'formData'        => $this->filterProject($dayData['formData'], $projectId, false),
            'spendData'       => $this->filterProject($dayData['spendData'], $projectId, false),
            'billAccountData' => $this->filterArchives($dayData['billAccountData'], $archives, false),
            'arrivingData'    => $this->filterArchives($dayData['arrivingData'], $archives, false),
        ];
    }

    public function filterProjectArchives($department)
    {
        $arr = collect();
        $department->projects->each(function ($project) use (&$arr) {
            $arr = $arr->merge($project->archives->pluck('id'));
        });
        return $arr->unique();
    }

    public function excelDataToCount($data)
    {
        return $data->map(function ($item) {
            if (isset($item['formData'])) {
                return $this->toExcelCountData($item);
            }
            return $this->excelDataToCount($item);
        });

    }

    public function toCountData($data)
    {
        return [
            'formData'        => $this->parserFormDataToCount($data['formData']),
            'spendData'       => $this->parserSpendDataToCount($data['spendData']),
            'billAccountData' => $this->parserBillAccountDataToCount($data['billAccountData']),
            'arrivingData'    => $this->parserArrivingDataToCount($data['arrivingData']),
        ];
    }

    public function toExcelCountData($data)
    {
        $countData = $this->toCountData($data);

        // dd($countData);
        $formData        = $countData['formData'];
        $spendData       = $countData['spendData'];
        $billAccountData = $countData['billAccountData'];
        $arrivingData    = $countData['arrivingData'];

        $result = Helpers::$ExcelFields;

        // 总点击
        $result['click'] = $spendData['click'];
        // 点击率 = 总点击 / 总展现
        $result['click_rate'] = $this->toRate($this->divisionOfSelf($spendData['click'], $spendData['show']));
        // 留表率 = 总表单 / 总点击
        $result['form_rate'] = $this->toRate($this->divisionOfSelf($formData['form_count'], $spendData['click']));
//        $result['spend_rate']          = $formData['form_count'] / $spendData['click'];
        // 展现
        $result['show'] = $spendData['show'];

        // 消费
        $result['spend'] = $spendData['spend'];

        // 总表单数
        $result['form_count'] = $formData['form_count'];
        // 有效对话 = 意向1 + 意向2 + 意向3 +意向4
        $result['effective_form'] = $formData['intention-2'] + $formData['intention-3'] + $formData['intention-4'] + $formData['intention-5'];
        // 无效对话 = 查不到 + 意向5
        $result['uneffective_form'] = $formData['intention-1'] + $formData['intention-6'];
        // 有效对话占比 = 有效表单 / 总表单
        $result['effective_form_rate'] = $this->toRate($this->divisionOfSelf($result['effective_form'], $result['form_count']));
        // 点击成本 = 总消费 / 总点击
        $result['click_spend'] = round($this->divisionOfSelf($spendData['spend'], $spendData['click']),2);
        // 表单成本 = 总消费 / 总表单数
        $result['form_spend'] = round($this->divisionOfSelf($spendData['spend'], $formData['form_count']),2);
        // 到院成本 = 总消费 / 总到院
        $result['arriving_spend'] = round($this->divisionOfSelf($spendData['spend'], $arrivingData['arriving_count']),2);
        // 总建档
        $result['archive_count'] = $formData['is_archive-1'];
        // 未建档
        $result['un_archive_count'] = $formData['is_archive-0'];
        //总到院
        $result['arriving_count'] = $arrivingData['arriving_count'];
        // 新客首次
        $result['new_first_arriving'] = $arrivingData['new_first'];
        // 新客二次
        $result['new_again_arriving'] = $arrivingData['new_again'];
        // 老客
        $result['old_arriving'] = $arrivingData['old'];
        // 新客首次占比 = 新客首次 / 总到院
        $result['new_first_rate'] = $this->toRate($this->divisionOfSelf($result['new_first_arriving'], $result['arriving_count']));
        // 到院率 = 新客首次 / 总表单
        $result['arriving_rate'] = $this->toRate($this->divisionOfSelf($result['new_first_arriving'], $result['form_count']));
        // 新客首次成交数
        $result['new_first_transaction'] = $arrivingData['new_first_transaction'];
        // 新客二次成交数
        $result['new_again_transaction'] = $arrivingData['new_again_transaction'];
        // 老客成交数
        $result['old_transaction'] = $arrivingData['old_transaction'];
        // 总成交
        $result['total_transaction'] = $arrivingData['new_transaction'] + $arrivingData['old_transaction'];
        // 新客首次业绩
        $result['new_first_account'] = $billAccountData['new_first_account'];
        // 新客二次业绩
        $result['new_again_account'] = $billAccountData['new_again_account'];
        // 老客业绩
        $result['old_account'] = $billAccountData['old_account'];
        // 总业绩
        $result['total_account'] = $billAccountData['total_account'];

        // 业绩占比 = 病种业绩 / 科室业绩
        // -- 开发中 --

        // 新客首次成交率 = 新客首次数 / 总成交
        $result['new_first_transaction_rate'] = $this->toRate($this->divisionOfSelf($result['new_first_transaction'], $result['total_transaction']));
        // 新客二次成交率 = 新客二次数 / 总成交
        $result['new_again_transaction_rate'] = $this->toRate($this->divisionOfSelf($result['new_again_transaction'], $result['total_transaction']));
        // 老客成交率 = 老客数 / 总成交
        $result['old_transaction_rate'] = $this->toRate($this->divisionOfSelf($result['old_transaction'], $result['total_transaction']));
        // 总成交率 = 到院数 / 成交数
        $result['total_transaction_rate'] = $this->toRate($this->divisionOfSelf($result['total_transaction'], $result['arriving_count']));
        // 总单体 = 总业绩  / 总成交数
        $result['total_average'] = round($this->divisionOfSelf($result['total_account'], $result['total_transaction']), 2);
        // 新客首次单体 = 新客首次业绩  / 新客首次成交数
        $result['new_first_average'] = round($this->divisionOfSelf($result['new_first_account'], $result['new_first_transaction']),2);

        // 新客二次单体 = 新客二次业绩  / 新客二次成交数
        $result['new_again_average'] = round($this->divisionOfSelf($result['new_again_account'], $result['new_again_transaction']),2);
        // 老客单体 = 老客业绩  / 老客成交数
        $result['old_average'] =round($this->divisionOfSelf($result['old_account'], $result['old_transaction']),2);


        // 总投产比 =  1: 总业绩 / 总消费
        $result['proportion_total'] = $this->toRatio($result['spend'], $result['total_account']);
        // 新客投产比 = 1: 总业绩 / 总消费
        $result['proportion_new'] = $this->toRatio($result['spend'], $billAccountData['new_account']);
        return $result;
    }


    /**
     * @param Collection $data
     * @return array
     */
    public function parserFormDataToCount($data)
    {
        $result               = FormData::$FormCountDataFormat;
        $result['form_count'] = $data->count();

        foreach ($data as $item) {
            $phone = $item->phones->first();
            $result["is_archive-{$phone['is_archive']}"]++;
            $result["intention-{$phone['intention']}"]++;
        }
        return $result;
    }

    /**
     * @param $data
     * @return array
     */
    public function parserSpendDataToCount($data)
    {
        $result = SpendData::$SpendCountDataFormat;

        foreach ($data as $item) {
            $result['spend'] += $item['spend'] ?? 0;
            $result['click'] += $item['click'] ?? 0;
            $result['show']  += $item['show'] ?? 0;
        }

        return $result;
    }

    /**
     * @param Collection $data
     * @return array
     */
    public function parserArrivingDataToCount($data)
    {
        $result                   = ArrivingData::$ArrivingCountDataFormat;
        $result['arriving_count'] = $data->count();

        foreach ($data as $value) {
            $transaction = $value['is_transaction'] == ' 是 ';

            if ($value['customer_status'] == ' 新客户 ') {
                if ($value['arriving_again'] == '二次') {
                    $result['new_again']++;
                    $transaction && $result['new_again_transaction']++;
                } else {
                    $result['new_first']++;
                    $transaction && $result['new_first_transaction']++;
                }
            } else {
                $result['old']++;
                $transaction && $result['old_transaction']++;
            }
        }

        $result['new_total']       = $result['new_first'] + $result['new_again'];
        $result['new_transaction'] = $result['new_again_transaction'] + $result['new_first_transaction'];
        return $result;
    }

    /**
     * @param $data
     * @return array
     */
    public function parserBillAccountDataToCount($data)
    {
        $result = BillAccountData::$BillAccountCountDataFormat;

        foreach ($data as $value) {
            $customerStatus          = $value['customer_status'];
            $account                 = (float)($value['order_account'] ?? 0);
            $result['total_account'] += $account;

            if ($customerStatus) {
                if ($customerStatus == ' 新客户 ') {
                    if ($value['arriving_again'] == '二次') {
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

    public function divisionOfSelf($val, $div)
    {
        return !$div
            ? $val
            : $val / $div;
    }

    public function toRate($value)
    {
        if (!$value) return '0%';
        return round($value * 100, 2) . '%';
    }


    public function toRatio($num1, $num2)
    {
        if (!$num1) {
            return $num1 . ":" . $num2;
        }
        return "1:" . round($num2 / $num1, 2);
    }

    public function gcd($a, $b)
    {
        if ($a == 0 || $b == 0)
            return abs(max(abs($a), abs($b)));

        $r = $a % $b;
        return ($r != 0) ?
            $this->gcd($b, $r) :
            abs($b);
    }

}
