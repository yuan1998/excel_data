<?php

namespace App\Parsers;

use App\Helpers;
use App\Models\ProjectType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

/**
 * 根据 `渠道ID` 获取 `媒介` 和 `数据类型`
 * 根据 `科室ID` 获取 `病种` 和 `科室建档类型`
 * 根据 `病种` 获取 `病种-建档类型`
 *
 * 获取 到院数据 :
 *  - `科室到院数据` = 使用 `媒介` 丶 `科室建档类型` 丶 `日期` 筛选
 *  - `病种到院数据` = 使用 `病种-建档类型` 筛选 `科室到院数据`
 *  - `其他到院数据` = 获取不包含 `病种-建档类型` 的 `科室业绩数据`
 *  - `渠道到院数据` = `科室到院数据` 相加
 *  - `总到院数据` = `渠道到院数据` 相加
 *
 * 获取 业绩数据 :
 *  - `科室业绩数据` =  使用 `媒介` 丶 `科室建档类型` 丶 `日期` 筛选
 *  - `病种业绩数据` = 使用 `病种-建档类型` 筛选 `科室业绩数据`
 *  - `其他业绩数据` =  获取不包含 `病种-建档类型` 的 `科室业绩数据`
 *  - `渠道业绩数据` = `科室业绩数据` 相加
 *  - `总业绩数据` = `渠道业绩数据` 相加
 *
 * 获取 表单数据 :
 *  - `科室表单数据` = 使用 `数据类型` , `日期` , `科室ID`  筛选
 *  - `病种表单数据` = 使用 `病种` 筛选 `科室表单数据`
 *  - `其他表单业绩` = 获取不包含 `病种` 的 `科室表单数据`
 *  - `渠道表单数据` = `科室表单数据` 相加
 *  - `总表单数据` = `渠道表单数据` 相加
 *
 * 获取 消费数据 :
 *  - `科室消费数据` = 使用 `数据类型` , `日期` , `科室ID`  筛选
 *  - `病种消费数据` = 使用 `病种` 筛选 `科室消费数据`
 *  - `其他消费业绩` = 获取不包含 `病种` 的 `科室消费数据`
 *  - `渠道消费数据` = `科室消费数据` 相加
 *  - `总消费数据` = `渠道消费数据` 相加
 *
 * Class ParserStart
 * @package App\Parsers
 */
class ParserStart extends ParserBase
{

    /**
     * @var Collection|static
     */
    public $channelsModel;


    /**
     * ParserStart constructor.
     * @param $requestData
     */
    public function __construct($requestData)
    {
        $this->requestData    = $requestData;
        $this->project_id     = Arr::get($requestData, 'project_id', []);
        $this->channels_id    = Arr::get($requestData, 'channel_id', []);
        $this->departments_id = Arr::get($requestData, 'department_id', []);
        $this->group_id       = Arr::get($requestData, 'consultant_group_id', null);
        $this->type           = Arr::get($requestData, 'type');

        $this->parserDates($requestData['dates']);
    }


    public function getFileName()
    {

        $channelName    = $this->channels->pluck('title')->implode(',');
        $departmentName = $this->departments->pluck('title')->implode(',');
        $dateName       = implode('-', $this->dates);
        return "[{$channelName}]_[{$departmentName}]_{$dateName}.xlsx";
    }

    /**
     * 入口,解析所需要的数据
     * @param $name
     * @return Collection
     */
    public function toArray($name)
    {
        $data = [
            'formData'        => $this->formData,
            'spendData'       => $this->spendData,
            'billAccountData' => $this->billAccountData,
            'arrivingData'    => $this->arrivingData,
        ];

        switch ($name) {
            case "channel" :
                return $this->generateDateToChannel($data);
            case "account" :
                return $this->generateDateToAccount($data);
            case "department" :
                return $this->generateDateToDepartment($data);
            case "channel-department":
                return $this->generateChannelToDepartment($data);
        }
    }


    public function generateConsultantGroup() {
        $data = [
            'billAccountData' => $this->billAccountData,
            'arrivingData'    => $this->arrivingData,
        ];
    }

    public function getChannelsModel()
    {
        if (!$this->channelsModel) {
            $this->channelsModel = $this->channels->map(function ($channel) {
                return new ParserChannel($channel, $this);
            });
        }
        return $this->channelsModel;
    }

    /**
     * 生成 日期 -> 渠道 -> 科室 格式的数据
     * @param $data
     * @return Collection
     */
    public function generateChannelToDepartment($data)
    {
        $channelResult = collect();
        foreach ($this->channels as $channel) {
            $channelData = $this->filterAllDataOfChannelMediums($data, $channel);
            $channelData = $this->filterAllDataOfRequestDepartment($channelData);
            $channelResult->put($channel->title, $this->generateDateToDepartment($channelData));
        }

        return $channelResult;
    }

    /**
     * 生成 日期 -> 渠道 -> 账户 格式的数据
     * @param $data
     * @return Collection
     */
    public function generateDateToAccount($data)
    {
        $groupData = $this->groupDataOfDate($data);
        $result    = collect();

        $totalName = Carbon::parse($this->dates[0])->format("Y-m");
        $result->put($totalName, $this->generateAccountItem($data));
        Helpers::dateRangeForEach($this->dates, function (Carbon $date)
        use ($groupData, $result) {
            $dateString = $date->toDateString();
            $dayData    = $this->filterAllDataOfDate($groupData, $date);

            $channelData = $this->generateAccountItem($dayData);
            $result->put($dateString, $channelData);
        });

        return $result;
    }

    public function generateAccountItem($data)
    {
        $channelResult = collect();
        $data          = $this->filterAllDataOfRequestDepartment($data);
        foreach ($this->channels as $channel) {
            $channelData = $this->filterAllDataOfChannelMediums($data, $channel);
            $accounts    = $channel->accounts;

            $accountResult = collect();
            foreach ($accounts as $account) {
                $accountData = $this->filterAllDataOfAccount($channelData, $account);
                $accountResult->put($account->name, new ExcelFieldsCount($accountData));
            }
            $otherAccountData = $this->filterAllDataNotInAccount($channelData, $accounts);
            $accountResult->put('其他', new ExcelFieldsCount($otherAccountData));
            $accountResult->put('汇总', new ExcelFieldsCount($channelData));

            $channelResult->put($channel->title, $accountResult);
        }
        return $channelResult;
    }

    /**
     * 生成 日期 -> 渠道 格式的数据
     * @param $data
     * @return Collection
     */
    public function generateDateToChannel($data)
    {
        $groupData = $this->groupDataOfDate($data);
//        dd($groupData['spendData']->toArray());
        $result = collect();

        $totalName = Carbon::parse($this->dates[0])->format("Y-m");
        $result->put($totalName, $this->generateChannelItem($data));

        Helpers::dateRangeForEach($this->dates, function (Carbon $date)
        use ($groupData, $result) {
            $dateString = $date->toDateString();
            $dayData    = $this->filterAllDataOfDate($groupData, $date);

            $channelData = $this->generateChannelItem($dayData);
            $result->put($dateString, $channelData);
        });

        return $result;
    }

    /**
     * 日期 -> 渠道 数据: 将每个渠道数据 解析 成 ExcelFieldsCount
     * @param $data
     * @return Collection
     */
    public function generateChannelItem($data)
    {
        $channelResult = collect();
//        $data          = $this->filterAllDepartmentData($data);
        // 根据 渠道 筛选出对应的数据
        foreach ($this->channels as $channel) {
            $channelData = $this->filterAllDataOfChannelMediums($data, $channel);
            $channelData = $this->filterAllDataOfRequestDepartment($channelData);
            $test        = new ExcelFieldsCount($channelData);
            $channelResult->put($channel->title, $test);
        }

        // 根据 科室 筛选出数据
//        foreach ($this->departments as $department) {
//            $departmentData = $this->filterDepartmentData($data, $department);
//            $channelResult->put($department->title . '汇总', new ExcelFieldsCount($departmentData));
//        }

        if (count($this->project_id) > 0) {
            $projects = ProjectType::find($this->project_id);

            foreach ($projects as $project) {
                $projectData = $this->filterAllDataOfProjectArchive($data, $project);

                $channelResult->put($project->title . '汇总', new ExcelFieldsCount($projectData));
            }
        }

        // 判断 渠道 数量,是否需要添加 汇总 行
        if ($this->channels->count() > 1) {
            $test = new ExcelFieldsCount($data);
            $channelResult->put('汇总', $test);
        }
        return $channelResult;
    }

    /**
     * 生成 日期 -> 科室 -> 病种 格式的数据
     * @param $data
     * @return Collection
     */
    public function generateDateToDepartment($data)
    {
        $groupData        = $this->groupDataOfDate($data);
        $resultDepartment = collect();

        $totalName = Carbon::parse($this->dates[0])->format("Y-m");
        $resultDepartment->put($totalName, $this->generateDepartmentItem($data));
        Helpers::dateRangeForEach($this->dates, function (Carbon $date)
        use ($groupData, $resultDepartment) {
            $dateString = $date->toDateString();
            $dayData    = $this->filterAllDataOfDate($groupData, $date);

            $departmentData = $this->generateDepartmentItem($dayData);
            $resultDepartment->put($dateString, $departmentData);
        });

        return $resultDepartment;
    }

    /**
     * 日期 -> 科室 -> 病种 数据 : 解析 数据 ,拆分出不同 科室和病种 中的数据
     * @param $data
     * @return Collection
     */
    public function generateDepartmentItem($data)
    {
        $departmentResult = collect();

        foreach ($this->departments as $department) {
            $departmentData = $this->filterAllDataOfDepartment($data, $department);
            $projects       = $department->projects;
            $projectId      = $projects->pluck('id');
            $archivesId     = $this->getArchiveOfDepartmentProject($department);

            $projectResult = collect();
            foreach ($projects as $project) {
                $projectData = $this->filterAllDataOfProjectArchive($departmentData, $project);

                $projectResult->put($project->title, new ExcelFieldsCount($projectData));
            }
            $otherProjectData = $this->filterAllDataNotInProjectId($departmentData, $projectId, $archivesId);
            $projectResult->put('其他', new ExcelFieldsCount($otherProjectData));
            $projectResult->put('汇总', new ExcelFieldsCount($departmentData));

            $departmentResult->put($department->title, $projectResult);
        }
        $departmentResult->put('总汇总', new ExcelFieldsCount($data));

        return $departmentResult;
    }

}
