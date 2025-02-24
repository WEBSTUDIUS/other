<?php

namespace Vikki\WB;

use Bitrix\Main\Config\Option;
use Vikki\WB\Api\Orders\Item;
use Vikki\WB\Api\WbConnector;
use Vikki\WB\Bitrix\OrdersTableManager;

class ExchangeOfOrders
{

    /*
        Получение заказов с wb
        Получить данные из таблицы
        Перебираем массив wb и ищем совпадения

        Если есть совпадение, то обновляем статус в таблице и возможно у счёта

        Если нет совпадений, добавляем заказ в таблицу, создам счёт и обнавляем данные в таблице(записываем ORDER_BITRIX_ID)

        Пересоздатся агент

    */

    /*
     * apy key
     *
     */
    /**
     * @throws \Exception
     */
    public static function AgentExportWB(): string
    {

        //Проверка настроек модуля
        if (!self::checkModuleSettings()) {
            return '';
        }

        // получение заказов
        $orders = new WbConnector();
        $allOrders = $orders->getAllOrders();

        // обновление заказов/сделок и запись в дб
        foreach ($allOrders['orders'] as $order) {
            $orderWb = new Item($order);

            $router = new OrdersTableManager();
            $router->routeOrderFromWb($orderWb->getQuery());
        }
        return '\Vikki\WB\ExchangeOfOrders::AgentExportWB();';
    }

    public static function checkModuleSettings(): bool
    {
        if (!Option::get('vikki.wb', "WB_API_KEY")) {
            return false;
        }
        if (Option::get('vikki.wb', "WB_ACTIVE_MODULE") != 'Y') {
            return false;
        }
        if (!Option::get('vikki.wb', "WB_RESPONSIBLE_PERSON")) {
            return false;
        }
        if (!Option::get('vikki.wb', "WB_CATALOG_ID")) {
            return false;
        }

        return true;
    }


}