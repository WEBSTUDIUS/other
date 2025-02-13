<?php
global $APPLICATION;
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

try {
    if (isset($arResult['RESULT']) && !empty($arResult['RESULT']['items'][0])) {
        $curPage = mb_substr($APPLICATION->GetCurPage(), 0, -1);
        $curPage = explode("/", $curPage);
        $curPage = end($curPage);
        $curPage = str_replace("_", "-", $curPage);

        $ozonItem = $arResult['RESULT']['items'][0];
        $id = $ozonItem['id'];
        $sku = $ozonItem['sources'][0]['sku'];
        $minPrice = $ozonItem['price_indexes']['ozon_index_data']['minimal_price'] ?: $ozonItem['min_price'];
        $minPriceFormatted = number_format($minPrice, 0, '.', ' ');

        if(!isset($arParams['PRODUCT_PRICE']) || ($arParams['PRODUCT_PRICE'] && $arParams['PRODUCT_PRICE'] >= $minPrice)) {
            echo "<div class='shadowed-block' id='ozonPrice' style='padding:10px;margin-bottom:10px;'>
                    <div class='text-center' id='$id' sku='$sku'>
                        <div class='alert alert-success' style='text-align:center;margin-bottom: 10px;'>
                            <span>с Ozon Картой:</span>
                            <span style='font-weight:bold;'>{$minPriceFormatted} руб.</span>
                        </div>      
                        <a href='https://www.ozon.ru/product/$curPage-{$ozonItem['sources'][0]['sku']}' class='btn btn-default' target='_blank' style='padding: 5px 20px;'>
                        Купить на <img src='/upload/ozon_logo.png'  alt='buy on ozon' width='auto' height='32' style='margin-left: 7px'>
                        </a>
                    </div>
                </div>";
        } else {
            echo '';
        }

    }
} catch (\Throwable $e) {
    \Bitrix\Main\Diag\Debug::writeToFile([
        'error' => $e->getMessage(),
        '$arParams' => $arParams,
        '$arResult' => $arResult,
        '$curPage' => $curPage,

    ], 'error ozon template - ' . date('d.m.Y'), $componentPath . '_error.log');
}