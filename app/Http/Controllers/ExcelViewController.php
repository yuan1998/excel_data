<?php

namespace App\Http\Controllers;

use App\Exports\TestExport;
use App\Models\Channel;
use App\Parsers\ParserChannel;
use App\Parsers\ParserDepartment;
use App\Parsers\ParserStart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Maatwebsite\Excel\Facades\Excel;

class ExcelViewController extends Controller
{

    public function index(string $type)
    {
        $data = [
            'department_id' => [2],
            'channel_id' => [4],
            'dates' => ['2019-10-23','2019-10-23']
        ];
        $parser = new ParserStart($data);

//        $count = $parser->allDataExcelData(true);
//        dd($count);
        return Excel::download(new TestExport($parser), 'tests.xlsx');
        $data = Redis::get($type);

        if (!$data) {
            return view('welcome');
        } else {
            return $this->renderExcelView($data);
        }
    }

    protected function renderExcelView($data)
    {
        $data   = json_decode($data, true);
        $data = [
            'department_id' => [2],
            'channel_id' => [4],
            'dates' => ['2019-10-23','2019-10-23']
        ];
        $parser = new ParserStart($data);

//        $count = $parser->allDataExcelData(true);
//        dd($count);
        return Excel::download(new TestExport($parser), 'tests.xlsx');
    }
}
