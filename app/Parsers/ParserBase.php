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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

/**
 * Class ParserBase
 * @property ParserBase[]|Builder[]|\Illuminate\Database\Eloquent\Collection|Collection|null                               departments
 * @property ParserBase[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|Collection|null billAccountData
 * @property ParserBase[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|Collection|null spendData
 * @property ParserBase[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|Collection|null formData
 * @property ParserBase[]|Builder[]|\Illuminate\Database\Eloquent\Collection|Collection|null                               channels
 * @property ParserBase[]|Builder[]|\Illuminate\Database\Eloquent\Collection|Collection|null                               arrivingData
 * @package App\Parsers
 */
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
    public $dateTimes;

    /**
     * @var string
     */
    public $type;

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
                ->with(['projects', 'department', 'phones', 'account'])
                ->where('type', $this->type)
                ->whereIn('department_id', $this->departments_id)
                ->whereIn('form_type', $this->getFormType())
                ->whereBetween('date', $this->dates)
                ->orWhereBetween('date',$this->dateTimes)
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
                ->with(['projects', 'department', 'account'])
                ->where('type', $this->type)
                ->whereIn('department_id', $this->departments_id)
                ->whereIn('spend_type', $this->getFormType())
                ->whereBetween('date', $this->dates)
                ->orWhereBetween('date',$this->dateTimes)
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
                ->with(['account'])
                ->where('type', $this->type)
                ->whereIn('medium_id', $this->getMediumsId())
                ->whereBetween('reception_date', $this->dates)
                ->orWhereBetween('reception_date',$this->dateTimes)
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
                ->with(['account'])
                ->where('type', $this->type)
                ->whereIn('medium_id', $this->getMediumsId())
                ->whereBetween('pay_date', $this->dates)
                ->orWhereBetween('pay_date',$this->dateTimes)
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
                ->with([
                    'mediums', 'accounts' => function ($query) {
                        $query->where('type', $this->type);
                    }
                ])
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
            'formData'        => $data['formData']->map(function ($item) {
                $item['group_date'] = Carbon::parse($item['date'])->toDateString();
                return $item;
            })->groupBy('group_date'),
            'spendData'       => $data['spendData']->map(function ($item) {
                $item['group_date'] = Carbon::parse($item['date'])->toDateString();
                return $item;
            })->groupBy('group_date'),
            'arrivingData'    => $data['arrivingData']->map(function ($item) {
                $item['group_date'] = Carbon::parse($item['reception_date'])->toDateString();
                return $item;
            })->groupBy('group_date'),
            'billAccountData' => $data['billAccountData']->map(function ($item) {
                $item['group_date'] = Carbon::parse($item['pay_date'])->toDateString();
                return $item;
            })->groupBy('group_date'),
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

    public function filterAllDepartmentData($data)
    {
        $departmentId = $this->departments->pluck('id');
        $archiveId    = collect();
        foreach ($this->departments as $department) {
            $archiveId = $archiveId->merge($department->archives->pluck('id'));
        }
        $archiveId = $archiveId->unique();

        return [
            'formData'        => $this->filterInDepartmentId($data['formData'], $departmentId),
            'spendData'       => $this->filterInDepartmentId($data['spendData'], $departmentId),
            'billAccountData' => $this->filterArchiveId($data['billAccountData'], $archiveId),
            'arrivingData'    => $this->filterArchiveId($data['arrivingData'], $archiveId),
        ];
    }

    public function filterInDepartmentId($data, $ids, $d = true)
    {
        return $data->filter(function ($item) use ($ids, $d) {
            if ($d) {
                return $ids->contains($item['department_id']);
            } else {
                return !$ids->contains($item['department_id']);
            }
        });
    }

    public function filterChannelData($data, $channel)
    {
        $mediumsId = $channel->mediums->pluck('id');
        $formTypes = collect(explode(',', $channel->form_type ?? ''));
        return [
            'formData'        => $this->filterFormType($data['formData'], $formTypes, 'form_type'),
            'spendData'       => $this->filterFormType($data['spendData'], $formTypes, 'spend_type'),
            'billAccountData' => $this->filterMediums($data['billAccountData'], $mediumsId),
            'arrivingData'    => $this->filterMediums($data['arrivingData'], $mediumsId),
        ];
    }

    public function filterAccountData($data, $account)
    {
        $id = $account->id;

        return [
            'formData'        => $this->filterAccountId($data['formData'], $id),
            'spendData'       => $this->filterAccountId($data['spendData'], $id),
            'billAccountData' => $this->filterAccountId($data['billAccountData'], $id),
            'arrivingData'    => $this->filterAccountId($data['arrivingData'], $id),
        ];
    }

    public function filterOtherAccountData($data, $accounts)
    {
        $id = $accounts->pluck('id');

        return [
            'formData'        => $this->filterOtherAccountId($data['formData'], $id),
            'spendData'       => $this->filterOtherAccountId($data['spendData'], $id),
            'billAccountData' => $this->filterOtherAccountId($data['billAccountData'], $id),
            'arrivingData'    => $this->filterOtherAccountId($data['arrivingData'], $id),
        ];
    }


    public function filterAccountId($data, $id)
    {
        return $data->filter(function ($item) use ($id) {
            return $id === $item['account_id'];
        });
    }

    public function filterOtherAccountId($data, $id)
    {
        return $data->filter(function ($item) use ($id) {
            return !$id->contains($item['account_id']);
        });
    }


    public function filterFormType($data, $typeId, $filed = 'form_type', $d = true)
    {
        return $data->filter(function ($data) use ($typeId, $filed, $d) {
            if ($d) {
                return $typeId->contains($data[$filed]);
            } else {
                return !$typeId->contains($data[$filed]);
            }
        });
    }

    public function filterMediums($data, $mediumsId, $d = true)
    {
        return $data->filter(function ($data) use ($mediumsId, $d) {
            if ($d) {
                return $mediumsId->contains($data->medium_id);

            } else {
                return !$mediumsId->contains($data->medium_id);
            }
        });

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

    public function parserDates($dates)
    {
        $date1           = Carbon::parse($dates[0]);
        $date2           = Carbon::parse($dates[1]);
        $this->dates     = [
            $date1->toDateString(),
            $date2->toDateString(),
        ];
        $this->dateTimes = [
            $date1->toDateTimeString(),
            $date2->toDateTimeString(),
        ];
    }
}
