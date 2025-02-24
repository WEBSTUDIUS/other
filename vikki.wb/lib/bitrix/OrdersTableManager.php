<?php

namespace Vikki\WB\Bitrix;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Diag\Debug;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;
use Vikki\WB\OrdersTable;


class OrdersTableManager
{

    const array arDealStatuses = [
        'WB_NEW_ORDER_STATUS' => 'PAID_TO_DELIVERY',
        'WB_SENT_ORDER_STATUS' => 'SHIPPED',
        'WB_WON_ORDER_STATUS' => 'WON',
        'WB_LOSE_ORDER_STATUS' => 'PRODUCT_CANCEL',
    ];

    const array arWbStatuses = [
            'waiting' => 'Сборочное задание в работе',
            'sorted' => 'Сборочное задание отсортировано',
            'sold' => 'Сборочное задание получено покупателем',
            'canceled' => 'Отмена сборочного задания',
            'canceled_by_client' => 'Покупатель отменил заказ при получении',
            'declined_by_client' => 'Покупатель отменил заказ в первый чаc',
            'defect' => 'Отмена сборочного задания по причине брака',
            'ready_for_pickup' => 'Сборочное задание прибыло на ПВЗ',
            'canceled_by_missed_call' => 'Отмена заказа по причине недозвона',
            'postponed_delivery' => 'Курьерская доставка отложена',
        ];

    public array $order;


    /**
     * @throws \Exception
     */
    public function getBitrixOrderById($orderWbId): int
    {
        $array = [
            'select' => ["ORDER_BITRIX_ID"],
            'filter' => ["ORDER_WB_ID" => $orderWbId],
        ];
        $result = OrdersTable::getList($array)->fetch();
        if ($result["ORDER_BITRIX_ID"]) {
            return $result["ORDER_BITRIX_ID"];
        }
        return 0;
    }

    /**
     * @throws \Exception
     */
    public function setWbOrderToBitrixTable(): bool
    {
        $this->order['SKUS'] = json_encode($this->order['SKUS']); // Задана строка в таблице, а передаешь массив!!
        $result = OrdersTable::add($this->order);
        if ($result->isSuccess()) {
            return true;
        } else {
            Debug::writeToFile([$result->getErrorMessages()], date('d.m.Y H:i:s'), '/log/wb/error.log');
            return false;
        }
    }

    /**
     * @throws \Exception
     */
    public function routeOrderFromWb($order): void
    {
        try {
            $this->order = $order;
            if ($order['ORDER_BITRIX_ID']) {
                $this->updateStatus();
            } else {
                $this->order['ORDER_BITRIX_ID'] = $this->createOrder();
                if ($this->order['ORDER_BITRIX_ID']) {
                    $this->setWbOrderToBitrixTable();
                }
            }
        } catch (\Exception $e) {
            Debug::writeToFile($e->getMessage(), date('d.m.Y H:i:s'), '/log/wb/error.log');
        }

    }

    /**
     * @throws \Exception
     */
    private function updateStatus(): void
    {
        if (!$this->order['id'] || !$this->order['rid']) return;

        \Vikki\WB\OrdersTable::update([
            'ID' => $this->order['id']],
            ['fields' => [
                'RID' => $this->order['rid']],
                'STATUS' => $this->order['status'],
                'DISABLE_USER_FIELD_CHECK' => true,
            ]);
    }

    private function createOrder(): int
    {
        $order = $this->order;
        $arFields = [
            'ID' => $order['ORDER_WB_ID'],
            'TITLE' => 'wildberries',
            'SKUS' => $order['SKUS'],
            'CONTACT_ID' => 3707, // WB
            'SOURCE_ID' => 'WILDBERRIES',
            'ASSIGNED_BY_ID' => Option::get('vikki.wb', "WB_RESPONSIBLE_PERSON"),
            'CREATED_BY' => Option::get('vikki.wb', "WB_RESPONSIBLE_PERSON"),
            'PRICE' => $order['PRICE'],
            'CURRENCY_ID' => 'RUB',
            'COMMENTS' => 'Заказ создан автоматически из wildberries. Номер заказа: ' . $order['ORDER_WB_ID'],
            'STAGE' => self::getCurrentDealStatus($order['STATUS']) ?: 'PAID_TO_DELIVERY',

        ];

        return (new \Vikki\WB\Bitrix\OrderWB())->create($arFields);
    }

    /**
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws ArgumentException
     * waiting - сборочное задание в работе
     * sorted - сборочное задание отсортировано
     * sold - сборочное задание получено покупателем
     * canceled - отмена сборочного задания
     * canceled_by_client - покупатель отменил заказ при получении
     * declined_by_client - покупатель отменил заказ в первый чаc
     * Отмена доступна покупателю в первый час с момента заказа, если заказ не переведён на сборку
     * defect - отмена сборочного задания по причине брака
     * ready_for_pickup - сборочное задание прибыло на ПВЗ
     * canceled_by_missed_call - отмена заказа по причине недозвона. Для схемы "Доставка силами продавца dbs"
     * postponed_delivery - курьерская доставка отложена
     */
    public static function getOrderStatuses(): array
    {
        $statuses = [];

        $tableObj = OrdersTable::getList([
            'select' => [
                'ID',
                'STATUS'
            ],
        ]);

        while ($status = $tableObj->fetch()) {
            if (in_array($status['STATUS'], $statuses))
                continue;
            $statuses[$status['STATUS']] = self::arWbStatuses[$status['STATUS']];
        }

        return $statuses;
    }

    public static function getSelectedOrderStatuses(): array
    {
        $arOrderStatus = [];

        foreach (self::arDealStatuses as $statusCode => $arDealStatus) {
            $arOrderStatus[$statusCode] = explode(',', Option::get('vikki.wb', $statusCode));
        }

        return $arOrderStatus;
    }

    public static function getCurrentDealStatus($status): false|string
    {
        foreach (self::getSelectedOrderStatuses() as $key => $arOrderStatus) {
            if (in_array($status, $arOrderStatus)) {
                return self::arDealStatuses[$key];
            }
        }
        return false;
    }


}