<?php

namespace App\Parsers;


use App\Models\ArrivingData;
use App\Models\BillAccountData;
use App\Models\Consultant;
use App\Models\FormData;
use App\Models\ProjectType;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class ParserConsultantGroup extends ParserBase
{


    /**
     * ParserConsultantGroup constructor.
     * @param $requestData array
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


    public function handle($data)
    {
        return $this->departmentHandle($data)
            ->merge($this->channelHandle($data));
    }

    public function departmentHandle($data, $prefix = '汇总')
    {
        $result = collect();

        $result = $result->merge($this->mapToDepartmentData($data, $prefix))
            ->merge($this->mapToProjectData($data, $prefix));

        return $result;
    }

    public function channelHandle($data)
    {
        $result = collect();

        foreach ($this->channels as $channel) {
            $channelData = $this->filterAllDataOfChannelMediums($data, $channel);

            if ($this->project_id && count($this->project_id)) {
                $result = $result->merge($this->mapToProjectData($channelData, $channel->title));
            } else {
                $result = $result->merge($this->mapToDepartmentData($channelData, $channel->title));
            }
        }
        return $result;
    }

    public function mapToDepartmentData($data, $prefix = '')
    {
        $result = collect();
        foreach ($this->departments as $department) {
            $departmentData = $this->filterAllDataOfDepartment($data, $department);
            $result->put("{$prefix} - {$department->title}", $this->mapToConsultantData($departmentData));
        }
        return $result;
    }

    public function mapToConsultantData($data)
    {
        $consultants      = $this->getConsultantData();
        $consultantResult = collect();
        foreach ($consultants as $consultant) {
            $consultantData = $this->filterAllDataOfConsultant($data, $consultant);


            $consultantResult->put($consultant->name, new ExcelFieldsCount($consultantData));
        }
        $otherData = $this->filterAllDataNotInConsultantId($data, $consultants->pluck('id'));
        $consultantResult->put('其他', new ExcelFieldsCount($otherData));
        $consultantResult->put('合计', new ExcelFieldsCount($data));

        return $consultantResult;
    }

    public function mapToProjectData($data, $prefix = '')
    {
        $result   = collect();
        $projects = $this->getProjectData();
        if ($projects) {
            foreach ($projects as $project) {
                $projectData = $this->filterAllDataOfProjectArchive($data, $project);
                $result->put("{$prefix} - {$project->title}", $this->mapToConsultantData($projectData));
            }
        }
        return $result;
    }

    public function toArray()
    {
        $data = [
            'formData'        => $this->formData,
            'billAccountData' => $this->billAccountData,
            'arrivingData'    => $this->arrivingData,
        ];

        return $this->handle($data);
    }


    public function getFormData()
    {
        if (!$this->_formData) {
            $this->_formData = FormData::query()
                ->with(['projects', 'phones'])
                ->where('type', $this->type)
                ->whereIn('department_id', $this->departments_id)
                ->whereIn('form_type', $this->getFormType())
                ->whereBetween('date', $this->dates)
                // ->orWhereBetween('date',$this->dateTimes)
                ->get();
        }
        return $this->_formData;
    }

    public function getArrivingData()
    {
        if (!$this->_arrivingData) {
            $this->_arrivingData = ArrivingData::query()
                ->where('type', $this->type)
                ->whereIn('medium_id', $this->getMediumsId())
                ->whereBetween('reception_date', $this->dates)
                // ->orWhereBetween('reception_date',$this->dateTimes)
                ->get();
        }
        return $this->_arrivingData;
    }

    public function getBillAccountData()
    {
        if (!$this->_billAccountData) {
            $this->_billAccountData = BillAccountData::query()
                ->where('type', $this->type)
                ->whereIn('medium_id', $this->getMediumsId())
                ->whereBetween('pay_date', $this->dates)
                ->get();
        }
        return $this->_billAccountData;
    }


}
