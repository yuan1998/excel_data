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
 * @property ParserBase[]|Builder[]|\Illuminate\Database\Eloquent\Collection|Collection|null                               departments
 * @property ParserBase[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|Collection|null billAccountData
 * @property ParserBase[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|Collection|null spendData
 * @property ParserBase[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|Collection|null formData
 * @property ParserBase[]|Builder[]|\Illuminate\Database\Eloquent\Collection|Collection|null                               channels
 * @property ParserBase[]|Builder[]|\Illuminate\Database\Eloquent\Collection|Collection|null                               arrivingData
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

    public function allDataExcelData($toCount = false)
    {
        $data = [
            'formData'        => $this->formData,
            'spendData'       => $this->spendData,
            'billAccountData' => $this->billAccountData,
            'arrivingData'    => $this->arrivingData,
        ];
        return [
            'total'            => $this->generateDateToDepartment($data, $toCount),
            'total_department' => $this->generateDateToDepartmentProject($data, $toCount),
        ];
    }

    public function channelDataExcelData($toCount = false)
    {
        $result = collect();
        $this->getChannelsModel()
            ->each(function (ParserChannel $channel) use ($result, $toCount) {
                $data = $channel->dataGroup();
                $result->put($channel->getTitle(), [
                    'total'            => $this->generateDateToDepartment($data, $toCount),
                    'total_department' => $this->generateDateToDepartmentProject($data, $toCount),
                ]);
            });

        return $result;
    }


    public function generateExcelDataToCount()
    {
        $excelData = $this->generateExcelDataChannel();
        return $this->excelDataToCount($excelData);
    }

    public function testGenerate()
    {
        $data = $this->generateExcelDataToCount();

        $result = collect();
        $data->each(function ($channel, $channelKey) use ($result) {
            if (isset($channel['proportion_total'])) {
                $channel['channel_name']    = '合计';
                $channel['department_name'] = '-';
                $channel['date_name']       = '-';
                $channel['project_name']    = '-';

                $result->push($channel);
            } else {
                $channel->each(function ($department, $departmentKey) use ($result, $channelKey) {
                    if (isset($department['proportion_total'])) {
                        $department['department_name'] = '合计';
                        $department['channel_name']    = $channelKey;
                        $department['date_name']       = '-';
                        $department['project_name']    = '-';
                        $result->push($department);
                    } else {
                        $department->each(function ($date, $dateKey) use ($departmentKey, $channelKey, $result) {

                            if (isset($date['proportion_total'])) {
                                $date['channel_name']    = $channelKey;
                                $date['department_name'] = $departmentKey;
                                $date['date_name']       = '合计';
                                $date['project_name']    = '-';
                                $result->push($date);
                            } else {
                                $date->each(function ($project, $projectKey) use ($dateKey, $departmentKey, $channelKey, $result) {
                                    $project['date_name']       = $dateKey;
                                    $project['project_name']    = $projectKey;
                                    $project['department_name'] = $departmentKey;
                                    $project['channel_name']    = $channelKey;
                                    $result->push($project);
                                });
                            }
                        });
                    }
                });
            }
        });
        return $result;
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

    public function generateExcelDataChannel()
    {
        $result = collect();
        $this->getChannelsModel()
            ->each(function (ParserChannel $channelModel) use (&$result) {
                $data = [
                    'arrivingData'    => $channelModel->getArrivingData(),
                    'formData'        => $channelModel->getFormData(),
                    'spendData'       => $channelModel->getSpendData(),
                    'billAccountData' => $channelModel->getBillAccountData(),
                ];

                $result->put($channelModel->getTitle(), $this->generateExcelDataDepartment($data));
            });
        $result->put('all-total', [
            'arrivingData'    => $this->arrivingData,
            'formData'        => $this->formData,
            'spendData'       => $this->spendData,
            'billAccountData' => $this->billAccountData,
        ]);
        return $result;
    }

    public function generateDateToDepartment($data, $toCount = false)
    {
        $groupData        = $this->groupDataOfDate($data);
        $resultDepartment = collect();

        $resultDepartment->put('合计', $this->generateDepartmentItem($data));
        Helpers::dateRangeForEach($this->dates, function (Carbon $date)
        use ($groupData, $resultDepartment) {
            $dateString = $date->toDateString();
            $dayData    = $this->filterDateStringData($groupData, $date);

            $departmentData = $this->generateDepartmentItem($dayData);
            $departmentData->put('合计', $dayData);
            $resultDepartment->put($dateString, $departmentData);
        });

        return !$toCount ? $resultDepartment : $this->excelDataToCount($resultDepartment);
    }

    public function generateDepartmentItem($data)
    {
        $departmentData = collect();

        $this->departments->each(function ($department)
        use ($departmentData, $data) {
            $data = $this->filterDepartmentData($data, $department);

            $departmentData->put($department->title, $data);
        });
        $departmentData->put('合计', $data);
        return $departmentData;
    }

    public function generateDateToDepartmentProject($data, $toCount = false)
    {
        $groupData        = $this->groupDataOfDate($data);
        $resultDepartment = collect();
        $this->departments->each(function ($department)
        use ($groupData, $resultDepartment, $data) {
            $dateResult = collect();
            $projectId  = $department->projects->pluck('id');
            $archivesId = $this->filterProjectArchives($department);


            $dateResult->put('合计', $this->generateProjectItem($data, $department, $projectId, $archivesId));

            Helpers::dateRangeForEach($this->dates, function (Carbon $date)
            use ($groupData, $department, $dateResult, $projectId, $archivesId) {
                $dayData       = $this->filterDateStringData($groupData, $date);
                $projectResult = $this->generateProjectItem($dayData, $department, $projectId, $archivesId);

                $dateString = $date->toDateString();
                $dateResult->put($dateString, $projectResult);
            });
            $resultDepartment->put($department->title, $dateResult);
        });

        return !$toCount ? $resultDepartment : $this->excelDataToCount($resultDepartment);
    }

    public function generateProjectItem($data, $department, $projectId, $archivesId)
    {
        $projectResult  = collect();
        $departmentData = $this->filterDepartmentData($data, $department);

        $department->projects->each(function ($project) use ($departmentData, $projectResult) {
            $projectData = $this->filterProjectData($departmentData, $project);
            $projectResult->put($project->title, $projectData);
        });
        $projectResult->put('其他',
            $this->filterOtherData($departmentData, $projectId, $archivesId)
        );
        $projectResult->put('合计', $departmentData);
        return $projectResult;
    }

    public function generateExcelDataDate($data, $department)
    {
        $groupData = $this->groupDataOfDate($data);

        $projectId  = $department->projects->pluck('id');
        $archivesId = $this->filterProjectArchives($department);
        $result     = collect();
        Helpers::dateRangeForEach($this->dates, function (Carbon $date)
        use ($groupData, $result, $department, $projectId, $archivesId) {

            $dayData = $this->filterDateStringData($groupData, $date);

            $projectResult = collect();
            $department->projects->map(function ($project) use ($dayData, $projectResult) {
                $projectData = $this->filterProjectData($dayData, $project);
                $projectResult->put($project->title, $projectData);
            });
            $projectResult->put('other', $this->filterOtherData($dayData, $projectId, $archivesId));
            $projectResult->put('project-total', $dayData);
            $result->put($date->toDateString(), $projectResult);
        });
        $result->put('date-total', $data);

        return $result;
    }

    public function generateExcelDataDepartment($data)
    {
        $result = collect();

        $this->departments->each(function ($department) use ($data, $result) {
            $departmentData = $this->filterDepartmentData($data, $department);
            $result->put($department->title, $this->generateExcelDataDate($departmentData, $department));
        });
        $result->put('channel-total', $data);

        return $result;
    }

    public function generateExcelDataProject($data)
    {

    }

}
