<?php

namespace Vikki\WB\Api;

use Bitrix\Main\Config\Option;

class WbConnector
{

    private string $apiKey;


    public function __construct()
    {
        $this->apiKey = Option::get('vikki.wb', "WB_API_KEY");
    }

    private function getCurl(string $link, array $data = [], string $method = 'GET')
    {
        $data_string = json_encode($data, JSON_UNESCAPED_UNICODE);
        header("HTTPS/1.0 200 OK");
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $link.'?'.http_build_query($data));
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLINFO_HEADER_OUT, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json;charset=UTF-8','Authorization:'.$this->apiKey));
        $result = curl_exec($curl);
        curl_close($curl);
        $ex = json_decode($result, true,512,JSON_INVALID_UTF8_IGNORE | JSON_INVALID_UTF8_SUBSTITUTE|JSON_OBJECT_AS_ARRAY);
        return $ex;
    }

    public function getAllOrders()
    {
        $daysFrom = $this->getDaysFrom();
        $get = ["limit" => 1000,"next" => 0, "dateFrom" => strtotime("-$daysFrom day")];
        $link = 'https://suppliers-api.wildberries.ru/api/v3/orders';
        return $this->getCurl($link, $get);
    }
    public function getNewOrders()
    {
        $link = 'https://suppliers-api.wildberries.ru/api/v3/orders/new';
        return $this->getCurl($link);
    }
    public function getOrderStatus(int $orderId)
    {
        $post = ["orders" => [$orderId]];
        $link = 'https://suppliers-api.wildberries.ru/api/v3/orders/status';
        return $this->getCurl($link, $post, 'POST');
    }

    private function getDaysFrom(): int
    {
        return (int)Option::get('vikki.wb', "WB_DAYS_FROM") ?: 4;
    }

}