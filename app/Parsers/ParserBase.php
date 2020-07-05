<?php

namespace App\Parsers;

use App\Helpers;
use App\Models\AccountData;
use App\Models\ArrivingData;
use App\Models\BillAccountData;
use App\Models\Channel;
use App\Models\Consultant;
use App\Models\DepartmentType;
use App\Models\FormData;
use App\Models\MediumType;
use App\Models\ProjectType;
use App\Models\SpendData;
use App\Models\TempCustomerData;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use phpDocumentor\Reflection\Project;

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
    public $requestData;
    /**
     * @var array
     */
    public $channels_id;
    /**
     * @var array
     */
    public $departments_id;
    public $project_id;
    public $group_id;
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
     * @var Collection
     */
    public $_projects;
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

    public $_tempCustomerData;

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
    /**
     * @var ParserBase[]|Builder[]|\Illuminate\Database\Eloquent\Collection|Collection|static[]|null
     */
    public $_consultantData;

    public function __get($name)
    {
        switch ($name) {
            case 'departments' :
                return $this->getDepartments();
            case 'archivesData' :
                return $this->getAllArchives();
            case 'mediumsData' :
                return $this->getMediums();
            case 'tempCustomerData':
                return $this->getTempCustomerData();
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
                // ->orWhereBetween('date',$this->dateTimes)
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
                // ->orWhereBetween('date',$this->dateTimes)
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
                // ->orWhereBetween('reception_date',$this->dateTimes)
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
                // ->orWhereBetween('pay_date',$this->dateTimes)
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
                    'mediums',
                    'accounts' => function ($query) {
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
            $this->_mediumsData = MediumType::query()
                ->whereHas('channels', function ($query) {
                    $query->whereIn('id', $this->channels_id);
                })->get(['id', 'title']);
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
            $this->getDepartments()
                ->each(function ($department) use (&$result) {
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
     * 根据传参中的 科室ID 筛选数据
     * @param Collection     $data
     * @param string|integer $id
     * @param bool           $d
     * @return Collection
     */
    public function filterDataOfDepartmentId($data, $id, $d = true)
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
     * 根据传参中的 建档类型ID 筛选数据
     * @param Collection $data
     * @param Collection $archivesId
     * @param bool       $d
     * @return Collection
     */
    public function filterDataOfArchiveIds($data, $archivesId, $d = true)
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
     * 根据数据的病种类型筛选数据是否包含 指定的病种,并筛选出来
     * @param Collection|null        $data
     * @param ProjectType|Collection $project
     * @param bool                   $d
     * @return array|Collection
     */
    public function filterDataProjectOfProjectId($data, $project, $d = true)
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
     * 根据 建档类型 筛选 数据
     * @param Collection|null $data
     * @param Collection      $archives
     * @param bool            $d
     * @return array|Collection
     */
    public function filterDataOfArchives($data, $archives, $d = true)
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
     * 根据 病种筛选 所有数据
     * @param $dayData
     * @param $project
     * @return array|Collection
     */
    public function filterAllDataOfProjectArchive($dayData, $project)
    {
        $archives = $project->archives->pluck('id');
        return collect($dayData)->map(function ($value, $key) use ($archives, $project) {
            switch ($key) {
                case "formData":
                case "spendData":
                    return $this->filterDataProjectOfProjectId($value, $project);
                case "billAccountData":
                case "arrivingData":
                    return $this->filterDataOfArchives($value, $archives);
                default:
                    return $value;
            }
        });
    }

    /**
     * 根据传参 中的时间数据 筛选所有数据
     * @param array  $data
     * @param Carbon $date
     * @return array
     */
    public function filterAllDataOfDate($data, $date)
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
     * 将数据根据时间格式分类
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

    /**
     * 根据 传参中 的 科室类型 筛选所有数据
     * @param array          $data
     * @param DepartmentType $department
     * @return Collection|array
     */
    public function filterAllDataOfDepartment($data, $department)
    {
        $id         = $department->id;
        $archivesId = $department->archives->pluck('id');

        return collect($data)->map(function ($value, $key) use ($id, $archivesId) {
            switch ($key) {
                case "formData":
                case "spendData":
                    return $this->filterDataOfDepartmentId($value, $id);
                case "billAccountData":
                case "arrivingData":
                    return $this->filterDataOfArchiveIds($value, $archivesId);
                default:
                    return $value;
            }
        });
    }

    /**
     * 根据 request 中的 科室类型 筛选所有数据
     * @param $data
     * @return array
     */
    public function filterAllDataOfRequestDepartment($data)
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
            'billAccountData' => $this->filterDataOfArchiveIds($data['billAccountData'], $archiveId),
            'arrivingData'    => $this->filterDataOfArchiveIds($data['arrivingData'], $archiveId),
        ];
    }

    /**
     * @param array      $data
     * @param Consultant $consultant
     * @return array
     */
    public function filterAllDataOfConsultant($data, $consultant)
    {
        $id = collect($consultant->id);

        return [
            'formData'        => $this->filterDataInConsultantId($data['formData'], $id),
            'billAccountData' => $this->filterDataInConsultantId($data['billAccountData'], $id, true, 'online_customer_id'),
            'arrivingData'    => $this->filterDataInConsultantId($data['arrivingData'], $id, true, 'online_customer_id'),
        ];
    }

    public function filterAllDataNotInConsultantId($data, $consultantId)
    {
        return [
            'formData'        => $this->filterDataInConsultantId($data['formData'], $consultantId, false),
            'billAccountData' => $this->filterDataInConsultantId($data['billAccountData'], $consultantId, false, 'online_customer_id'),
            'arrivingData'    => $this->filterDataInConsultantId($data['arrivingData'], $consultantId, false, 'online_customer_id'),
        ];

    }


    /**
     * 筛选出包含对应 客服ID 的数据
     * @param Collection $data
     * @param Collection $ids
     * @param bool       $d
     * @param string     $fieldText
     * @return mixed
     */
    private function filterDataInConsultantId($data, $ids, $d = true, $fieldText = 'consultant_id')
    {
        return $data->filter(function ($item) use ($ids, $fieldText, $d) {
            $contains = $ids->contains($item[$fieldText]);
//            var_dump([
//                'id'       => $ids,
//                $fieldText => $item[$fieldText],
//                'result'   => $contains,
//            ]);
            return $d ? $contains : !$contains;
        });
    }

    /**
     * 筛选出对应 科室ID 的数据
     * @param Collection $data
     * @param Collection $ids
     * @param bool       $d
     * @return mixed
     */
    private function filterInDepartmentId($data, $ids, $d = true)
    {
        return $data->filter(function ($item) use ($ids, $d) {
            if ($d) {
                return $ids->contains($item['department_id']);
            } else {
                return !$ids->contains($item['department_id']);
            }
        });
    }

    /**
     * 根据 参数中的渠道类型  筛选所有数据.
     * @param array   $data
     * @param Channel $channel
     * @return Collection
     */
    public function filterAllDataOfChannelMediums($data, $channel)
    {
        $mediumsId = $channel->mediums->pluck('id');
        $formTypes = collect(explode(',', $channel->form_type ?? ''));
        return collect($data)->map(function ($value, $key) use ($formTypes, $mediumsId) {
            switch ($key) {
                case "formData":
                    return $this->filterDataOfTypeId($value, $formTypes, 'form_type');
                case "spendData":
                    return $this->filterDataOfTypeId($value, $formTypes, 'spend_type');
                case "billAccountData":
                    return $this->filterDataOfMediumId($value, $mediumsId);
                case "arrivingData":
                    return $this->filterDataOfMediumId($value, $mediumsId);
                default:
                    return $value;
            }
        });
    }

    /**
     * 根据 传参中的 账户类型 筛选所有数据
     * @param Collection|array $data
     * @param AccountData      $account
     * @return array
     */
    public function filterAllDataOfAccount($data, $account)
    {
        $id = $account->id;

        return [
            'formData'        => $this->filterDataOfAccountId($data['formData'], $id),
            'spendData'       => $this->filterDataOfAccountId($data['spendData'], $id),
            'billAccountData' => $this->filterDataOfAccountId($data['billAccountData'], $id),
            'arrivingData'    => $this->filterDataOfAccountId($data['arrivingData'], $id),
        ];
    }

    /**
     * 根据传参中的 多个账户类型 , 筛选出不包含在这些账户中的数据
     * @param Collection|array $data
     * @param Collection       $accounts
     * @return array
     */
    public function filterAllDataNotInAccount($data, $accounts)
    {
        $id = $accounts->pluck('id');

        return [
            'formData'        => $this->filterDataNotInAccountId($data['formData'], $id),
            'spendData'       => $this->filterDataNotInAccountId($data['spendData'], $id),
            'billAccountData' => $this->filterDataNotInAccountId($data['billAccountData'], $id),
            'arrivingData'    => $this->filterDataNotInAccountId($data['arrivingData'], $id),
        ];
    }


    /**
     * 根据 账户Id 筛选传参中的数据
     * @param Collection     $data
     * @param string|integer $id
     * @return mixed
     */
    private function filterDataOfAccountId($data, $id)
    {
        return $data->filter(function ($item) use ($id) {
            return $id === $item['account_id'];
        });
    }

    /**
     * 筛选出不包含 在 账户ID数组 中的数据
     * @param Collection $data
     * @param Collection $id
     * @return mixed
     */
    private function filterDataNotInAccountId($data, $id)
    {
        return $data->filter(function ($item) use ($id) {
            return !$id->contains($item['account_id']);
        });
    }


    /**
     * 根据 数据类型 筛选数据
     * @param Collection $data
     * @param Collection $typeId
     * @param string     $filed
     * @param bool       $d
     * @return mixed
     */
    private function filterDataOfTypeId($data, $typeId, $filed = 'form_type', $d = true)
    {
        return $data->filter(function ($data) use ($typeId, $filed, $d) {
            if ($d) {
                return $typeId->contains($data[$filed]);
            } else {
                return !$typeId->contains($data[$filed]);
            }
        });
    }

    /**
     * 筛选 数据中 包含对应 媒介的数据
     * @param Collection $data
     * @param Collection $mediumsId
     * @param bool       $d
     * @return mixed
     */
    private function filterDataOfMediumId($data, $mediumsId, $d = true)
    {
        return $data->filter(function ($data) use ($mediumsId, $d) {
            if ($d) {
                return $mediumsId->contains($data->medium_id);

            } else {
                return !$mediumsId->contains($data->medium_id);
            }
        });
    }

    /**
     * 筛选出 所有 不包含 项目ID的数据
     * @param array          $dayData
     * @param string|integer $projectId
     * @param Collection     $archives
     * @return array
     */
    public function filterAllDataNotInProjectId($dayData, $projectId, $archives)
    {
        return [
            'formData'        => $this->filterDataProjectOfProjectId($dayData['formData'], $projectId, false),
            'spendData'       => $this->filterDataProjectOfProjectId($dayData['spendData'], $projectId, false),
            'billAccountData' => $this->filterDataOfArchives($dayData['billAccountData'], $archives, false),
            'arrivingData'    => $this->filterDataOfArchives($dayData['arrivingData'], $archives, false),
        ];
    }

    /**
     * 获取 科室 下所有关联的 建档类型
     * @param DepartmentType $department
     * @return Collection
     */
    public function getArchiveOfDepartmentProject($department)
    {
        $arr = collect();
        $department->projects->each(function ($project) use (&$arr) {
            $arr = $arr->merge($project->archives->pluck('id'));
        });
        return $arr->unique();
    }

    /**
     * 解析 时间数组
     * @param $dates
     */
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

    /**
     * 获取 临客数据
     * @return Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getTempCustomerData()
    {
        if (!$this->_tempCustomerData) {
            $this->_tempCustomerData = TempCustomerData::query()
                ->where('type', $this->type)
                ->whereIn('medium_id', $this->getMediumsId())
                ->whereBetween('archive_date', $this->dateTimes)
                ->get();
        }
        return $this->_tempCustomerData;
    }

    /**
     * 根据 咨询组ID 获取 咨询数据
     * @return ParserBase[]|Builder[]|\Illuminate\Database\Eloquent\Collection|Collection|null
     */
    public function getConsultantData()
    {
        if (!$this->_consultantData) {
            $this->_consultantData = Consultant::query()
                ->whereHas('consultantGroup', function ($query) {
                    $query->where('id', $this->group_id);
                })
                ->get();
        }
        return $this->_consultantData;
    }

    public function getProjectData()
    {
        if (!$this->_projects) {
            if ($this->project_id && count($this->project_id) > 0) {
                $this->_projects = ProjectType::find($this->project_id);
            } else {
                return null;
            }
        }

        return $this->_projects;

    }

}
