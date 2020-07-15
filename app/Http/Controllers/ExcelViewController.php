<?php

namespace App\Http\Controllers;

use App\Exports\TestExport;
use App\Parsers\ParserStart;
use Maatwebsite\Excel\Facades\Excel;

class ExcelViewController extends Controller
{

    public function index(string $type)
    {
        $data   = [
            'department_id' => [2],
            'channel_id'    => [4,3,1,2],
            'dates'         => ['2019-10-10', '2019-10-20']
        ];
        $parser = new ParserStart($data);

        return Excel::download(new TestExport($parser), 'tests.xlsx');
        $count = $parser->testChannelExcelData(true);
        dd($count);
//        $data = Redis::get($type);

        if (!$data) {
            return view('welcome');
        } else {
            return $this->renderExcelView($data);
        }
    }

    protected function renderExcelView($data)
    {
        $data   = json_decode($data, true);
        $data   = [
            'department_id' => [2],
            'channel_id'    => [4],
            'dates'         => ['2019-10-23', '2019-10-23']
        ];
        $parser = new ParserStart($data);

//        $count = $parser->allDataExcelData(true);
//        dd($count);
        return Excel::download(new TestExport($parser), 'tests.xlsx');
    }
}
