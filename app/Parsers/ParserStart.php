<?php

namespace App\Parsers;

use App\Helpers;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
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
     * @var array
     */
    public $requestData;
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
        $this->requestData = $requestData;

        $this->channels_id = $requestData['channel_id'];

        $this->departments_id = $requestData['department_id'];
        $this->dates          = $requestData['dates'];
    }

    public function getFileName()
    {

        $channelName    = $this->channels->pluck('title')->implode(',');
        $departmentName = $this->departments->pluck('title')->implode(',');
        $dateName       = implode('-', $this->dates);
        return "[{$channelName}]_[{$departmentName}]_{$dateName}.xlsx";
    }

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

    public function getChannelsModel()
    {
        if (!$this->channelsModel) {
            $this->channelsModel = $this->channels->map(function ($channel) {
                return new ParserChannel($channel, $this);
            });
        }
        return $this->channelsModel;
    }

    public function generateChannelToDepartment($data)
    {
        $channelResult = collect();
        foreach ($this->channels as $channel) {
            $channelData = $this->filterChannelData($data, $channel);
            $channelData = $this->filterAllDepartmentData($channelData);
            $channelResult->put($channel->title, $this->generateDateToDepartment($channelData));
        }

        return $channelResult;
    }

    public function generateDateToAccount($data)
    {
        $groupData = $this->groupDataOfDate($data);
        $result    = collect();

        $totalName = Carbon::parse($this->dates[0])->format("Y-m");
        $result->put($totalName, $this->generateAccountItem($data));
        Helpers::dateRangeForEach($this->dates, function (Carbon $date)
        use ($groupData, $result) {
            $dateString = $date->toDateString();
            $dayData    = $this->filterDateStringData($groupData, $date);

            $channelData = $this->generateAccountItem($dayData);
            $result->put($dateString, $channelData);
        });

        return $result;
    }

    public function generateAccountItem($data)
    {
        $channelResult = collect();
        $data          = $this->filterAllDepartmentData($data);
        foreach ($this->channels as $channel) {
            $channelData = $this->filterChannelData($data, $channel);
            $accounts    = $channel->accounts;

            $accountResult = collect();
            foreach ($accounts as $account) {
                $accountData = $this->filterAccountData($channelData, $account);
                $accountResult->put($account->name, new ExcelFieldsCount($accountData));
            }
            $otherAccountData = $this->filterOtherAccountData($channelData, $accounts);
            $accountResult->put('其他', new ExcelFieldsCount($otherAccountData));
            $accountResult->put('汇总', new ExcelFieldsCount($channelData));

            $channelResult->put($channel->title, $accountResult);
        }
        return $channelResult;
    }

    public function generateDateToChannel($data)
    {
        $groupData = $this->groupDataOfDate($data);
        $result    = collect();

        $totalName = Carbon::parse($this->dates[0])->format("Y-m");
        $result->put($totalName, $this->generateChannelItem($data));

        Helpers::dateRangeForEach($this->dates, function (Carbon $date)
        use ($groupData, $result) {
            $dateString = $date->toDateString();
            $dayData    = $this->filterDateStringData($groupData, $date);

            $channelData = $this->generateChannelItem($dayData);
            $result->put($dateString, $channelData);
        });

        return $result;
    }

    public function generateChannelItem($data)
    {
        $channelResult = collect();
        $data          = $this->filterAllDepartmentData($data);
        $this->channels->each(function ($channel)
        use ($channelResult, $data) {
            $channelData = $this->filterChannelData($data, $channel);
//            $channelData          = $this->filterAllDepartmentData($channelData);
            $test = new ExcelFieldsCount($channelData);
            $channelResult->put($channel->title, $test);
        });
        if ($this->channels->count() > 1) {
            $test = new ExcelFieldsCount($data);
            $channelResult->put('汇总', $test);
        }
        return $channelResult;
    }

    public function generateDateToDepartment($data)
    {
        $groupData        = $this->groupDataOfDate($data);
        $resultDepartment = collect();

        $totalName = Carbon::parse($this->dates[0])->format("Y-m");
        $resultDepartment->put($totalName, $this->generateDepartmentItem($data));
        Helpers::dateRangeForEach($this->dates, function (Carbon $date)
        use ($groupData, $resultDepartment) {
            $dateString = $date->toDateString();
            $dayData    = $this->filterDateStringData($groupData, $date);

            $departmentData = $this->generateDepartmentItem($dayData);
            $resultDepartment->put($dateString, $departmentData);
        });

        return $resultDepartment;
    }

    public function generateDepartmentItem($data)
    {
        $departmentResult = collect();

        foreach ($this->departments as $department) {
            $departmentData = $this->filterDepartmentData($data, $department);
            $projects       = $department->projects;
            $projectId      = $projects->pluck('id');
            $archivesId     = $this->filterProjectArchives($department);

            $projectResult = collect();
            foreach ($projects as $project) {
                $projectData = $this->filterProjectData($departmentData, $project);

                $projectResult->put($project->title, new ExcelFieldsCount($projectData));
            }
            $otherProjectData = $this->filterOtherData($departmentData, $projectId, $archivesId);
            $projectResult->put('其他', new ExcelFieldsCount($otherProjectData));
            $projectResult->put('汇总', new ExcelFieldsCount($departmentData));

            $departmentResult->put($department->title, $projectResult);
        }
        $departmentResult->put('总汇总', new ExcelFieldsCount($data));

        return $departmentResult;
    }

}
