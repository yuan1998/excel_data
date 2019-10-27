<?php

namespace App\Parsers;

use App\Models\DepartmentType;
use Illuminate\Support\Collection;

class ParserDepartment
{

    /**
     * @var DepartmentType
     */
    public $department;

    /**
     * @var Collection
     */
    public $projects;


    /**
     * @var mixed array
     */
    public $projects_id;


    /**
     * @var Collection
     */
    public $archives;

    /**
     * @var mixed array
     */
    public $archives_id;
    public $arrivingData;
    public $billAccountData;
    public $spendData;
    public $formData;


    /**
     * ParserDepartment constructor.
     * @param $department
     */
    public function __construct($department)
    {
        $this->department  = $department;
        $this->projects    = $this->getProjects();
        $this->archives    = $this->getArchives();
        $this->projects_id = $this->getProjectId()->toArray();
        $this->archives_id = $this->getArchivesId()->toArray();
    }

    public function __get($name)
    {
        switch ($name) {
            case 'id' :
                return $this->department->id;
            default :
                return null;
        }
    }

    public function getProjects()
    {
        return $this->department->projects;
    }

    public function getProjectArchives()
    {
        $arr = collect();
        $this->department->projects->each(function ($project) use (&$arr) {
            $arr =  $arr->merge($project->archives->pluck('id'));
        });
        return $arr->unique();
    }


    public function getArchives()
    {
        return $this->department->archives;
    }


    /**
     * @return Collection
     */
    public function getProjectId()
    {
        return $this->projects->pluck('id');
    }

    /**
     * @return Collection
     */
    public function getArchivesId()
    {
        return $this->archives->pluck('id');
    }


    public function fillData($data)
    {
        $this->arrivingData    = $data['arrivingData'];
        $this->billAccountData = $data['billAccountData'];
        $this->spendData       = $data['spendData'];
        $this->formData        = $data['formData'];
    }

    public function projectEach($callBack)
    {
        $this->projects->each(function ($project) {
            $project->archives;

        });


    }


}
