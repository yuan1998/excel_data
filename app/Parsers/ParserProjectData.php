<?php

namespace App\Parsers;

use Illuminate\Database\Eloquent\Builder;

class ParserProjectData
{
    public $project;
    public $arrivingQuery;
    public $billAccountQuery;
    public $arrivingData;
    public $billAccountData;

    /**
     * ParserProjectData constructor.
     * @param         $project
     * @param Builder $arrivingQuery
     * @param Builder $billAccountQuery
     */
    public function __construct($project, $arrivingQuery, $billAccountQuery)
    {
        $this->project          = $project;
        $this->arrivingQuery    = $arrivingQuery;
        $this->billAccountQuery = $billAccountQuery;
    }


    public function getArrivingData()
    {
        $query = $this->arrivingQuery->whereHas('projects',
            function ($query) {
                return $query->where('id', $this->project->id);
            });

        $this->arrivingData = new ParserArrivingData($query);
    }

    public function getBillAccountData()
    {
        $query = $this->billAccountQuery->whereHas('projects',
            function ($query) {
                return $query->where('id', $this->project->id);
            });

    }


}
