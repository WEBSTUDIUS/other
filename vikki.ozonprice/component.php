<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

try {
    // \Bitrix\Main\Diag\Debug::dump($arParams);
    $params = json_encode(
        [
            'offer_id' => [$arParams['OFFER_ID']],
        ], JSON_UNESCAPED_UNICODE);

    $apiUrl = ozonBaseUrlNew() . ozonPriceUrl();
    //$apiUrl = ozonBaseUrl() . ozonProductListUrl();

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Client-Id: " . ozonClientId(),
        "Api-Key: " . ozonApiKey(),
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 4);

    $response = curl_exec($ch);
    $errors = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    $arResult['RESULT'] = json_decode($response, true);
} catch (\Throwable $e) {
    \Bitrix\Main\Diag\Debug::writeToFile([
        'error' => $e->getMessage(), '$arParams' => $arParams, '$arResult' => $arResult,
    ], 'error ozon component - ' . date('d.m.Y'), $componentPath . '_error.log');
}

function ozonProductListUrl(): string
{
    return 'product/list';
}

function ozonPriceUrl(): string
{
//    return 'product/info/prices';
    return 'product/info/list';
}

function ozonClientId(): string
{
    return '2288228';
}

function ozonApiKey(): string
{
    return '950f04ae-8d8c-4d38-ae59-7c0895f0cb8b';
}

function ozonBaseUrlNew(): string
{
    return 'https://api-seller.ozon.ru/v3/';
}

function ozonBaseUrl(): string
{
    return 'https://api-seller.ozon.ru/v3/';
}

$this->IncludeComponentTemplate();
