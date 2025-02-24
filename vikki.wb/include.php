<?php

Bitrix\Main\Loader::registerAutoloadClasses(
    'vikki.wb',
    array(
        '\Vikki\WB\Main' => 'lib/Main.php',
        '\Vikki\WB\Api\WbConnector' => 'lib/api/WbConnector.php',
        '\Vikki\WB\OrdersTable' => 'lib/OrdersTable.php',
        '\Vikki\WB\ExchangeOfOrders' => 'lib/ExchangeOfOrders.php',
        '\Vikki\WB\Api\Orders\Collection' => 'lib/api/orders/Collection.php',
        '\Vikki\WB\Api\Orders\Item' => 'lib/api/orders/Item.php',
        '\Vikki\WB\Api\Orders\Model\Field' => 'lib/api/orders/model/Field.php',
        '\Vikki\WB\Bitrix\OrderWB' => 'lib/bitrix/OrderWB.php',
        '\Vikki\WB\Bitrix\OrdersTableManager' => 'lib/bitrix/OrdersTableManager.php',
        '\Vikki\WB\Agent' => 'lib/Agent.php',
    )
);
