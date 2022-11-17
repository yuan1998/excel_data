<?php

namespace App\Clients;

use App\Helpers;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class CrmClient extends Request
{

    /**
     * 查看登录状态
     * @return bool
     * @throws GuzzleException
     */
    public function isLogin(): bool
    {
        $response = $this->get('/');
        $response = $response->getBody()->getContents();
        return !preg_match('/用户登录/', $response);
    }

    /**
     * 获取账号的登录状态
     * @return bool
     * @throws GuzzleException
     */
    public function loginStatus(): bool
    {
        if (!$this->isLogin()) {
            return $this->login();
        }
        return true;
    }

    /**
     * Crm登录方法
     * @return bool
     * @throws GuzzleException
     */
    public function login(): bool
    {
        $response = $this->post('/Account/Auth/Login', [
            'form_params' => $this->account,
        ]);
        $contents = $response->getBody()->getContents();
        $result = json_decode($contents, true);
        return data_get($result, 'statusCode', 0) === "200";
    }


    public function ReservationTempCustSearchIndex($data): \Illuminate\Support\Collection
    {
        $data = array_merge(ClientConstants::TEMP_API_BASE_PARAMS, $data);

        $response = $this->post('/Reservation/TempCustSearch/Index', [
            'form_params' => $data
        ]);
        $body = $response->getBody()->getContents();

        if (!preg_match("/建档时间/", $body))
            throw new \Exception('ReservationTempCustSearchIndex接口失败.');
        $t = Helpers::parserHtmlTableToObject($body, '.table-striped', 'innerText');
        $result = collect($t)
            ->filter(function ($item) {
                return !!data_get($item, '媒介');
            });
        return $result;
    }

    public function ReservationTempCustInfoIndex($data): \Illuminate\Support\Collection
    {
        $data = array_merge([
            'pageCurrent' => 1,
        ], $data);
        $response =$this->post("/Reservation/TempCustInfo/Index",[
            'form_params' => $data
        ]);
        $body = $response->getBody()->getContents();

        if (!preg_match("/添加预约/", $body))
            throw new \Exception('ReservationTempCustInfoIndex接口失败.');
        $t = Helpers::parserHtmlTableToObject($body, '.table', 'innerText');
        $result = collect($t)
            ->filter(function ($item) {
                return !!data_get($item, '建档时间');
            });
        return $result;
    }

    /**
     * @throws \Exception
     */
    public static function test()
    {
        $client = new CrmClient([
            'username' => '7023',
            'password' => 'hm2018',
        ], 'zx');

        $item = $client->ReservationTempCustInfoIndex([
            'Phone' => '18792629031'
        ]);
        dd($item);
    }


}
