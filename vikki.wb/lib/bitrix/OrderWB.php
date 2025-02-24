<?php

namespace Vikki\WB\Bitrix;

use Bitrix\Crm\Service\Container;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Context,
    Bitrix\Currency\CurrencyManager,
    Bitrix\Sale\Order,
    Bitrix\Sale\Basket,
    Bitrix\Sale\Delivery,
    Bitrix\Sale\PaySystem;
use Bitrix\Main\Diag\Debug;
use Bitrix\Main\Loader;
use Bitrix\Crm\Item\Deal;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Vikki\App;

class OrderWB
{
    use \Vikki\Deals\TAdminOptions;
    use \Vikki\Deals\TMethods;

    use \Vikki\ExchangeReborn\Entities\Deal\Constants;

    public function create($arFields): int
    {
        try {
            Loader::includeModule('crm');

            $factory = Container::getInstance()->getFactory(\CCrmOwnerType::Deal);
            $item = $factory->createItem();

            // ContactID
            $contactID = self::wbContactId() ?: $arFields['CONTACT_ID'];
            $shipmentDate = date('d.m.Y 00:00:00', strtotime('+' . self::daysToShipmentDate() . ' days', time()));
            $title = 'Сделка #' . $arFields['ID'] . ' (' . $arFields['TITLE'] . ')';
            $dateCreated = \Bitrix\Main\Type\Date::createFromText(date("d.m.Y H:i:s"));

            $stage = $arFields['STAGE'] ?: 'NEW';
            $closed = 'N';

            // Manager
            $managerId = $arFields['ASSIGNED_BY_ID'];

            // Madnatory Fields!
            $item->setAssignedById($managerId);
            $item->setUpdatedBy($managerId);
            //
            $item->setTitle($title);
            $item->setBegindate($dateCreated);
            $item->setStageId($stage);
            $item->setClosed($closed);
            $item->setSourceId(self::wbFieldValue());
            $item->setCategoryId(0); // Общая воронка
            $item->setSourceDescription(self::wbFieldValue());
            $item->setComments($arFields['COMMENTS']);
            $item->setOpportunityAccount($arFields['PRICE']);
            $item->setOpportunity($arFields['PRICE']);
            $item->setContactId($contactID);
            $item->setOriginId('');

//            $item?->set('UF_DV_TYPE', 'DEAL');
            $item?->set('UF_DV_MARKETPLACE_ORDER_ID', $arFields['ID']);

            // Product Rows
            $productRows = self::getDealProductRows($arFields['SKUS'][0], $arFields['PRICE']);
            $item->setProductRowsFromArrays($productRows);

            // Save for getting ID
//            $result = $item->save();

            $operation = $factory->getAddOperation($item)
                ->disableAllChecks()
                ->launch();

            if($operation->isSuccess()) {
                // DELIVERY ARRAY
                $this->setDelivery([
                    'TITLE' => 'Доставка_' . $title,
                    'UF_CRM_4_DELIVERY_DATE' => $shipmentDate,
                    'UF_CRM_4_DELIVERY_TIME' => 2, // 15-00 - 19-00
                    'UF_CRM_4_DELIVERY_TYPE' => 2, // courier
                    'UF_CRM_4_DELIVERY_ADDRESS' => 'Wildberries ПВЗ',
                    'UF_CRM_4_TRACKING_NUMBER' => 'Wildberries',
                    'PARENT_ID_2' => $item->getId(),
                ]);
                //

                // PAYMENT ARRAY
                $this->setPayment([
                    'TITLE' => 'Оплата_' . $title,
                    'UF_CRM_5_PAYMENT_DATE' => \Bitrix\Main\Type\Date::createFromText(date('d.m.Y 00:00:00', strtotime('+2 days', time()))),
                    'UF_CRM_5_PAYMENT_TYPE' => self::paySystemId(),
                    'PARENT_ID_2' => $item->getId(),
                ]);
                //
            } else {
                Debug::writeToFile($operation->getErrorMessages(), date('d.m.Y H:i:s'), '/log/wb/error.log');
            }

            return $operation->isSuccess() ? $item->getId() : 0;

        } catch (\Exception $e) {
            Debug::writeToFile($e->getMessage(), date('d.m.Y H:i:s'), '/log/wb/error.log');
            return 0;
        }
    }

    /**
     */
    public static function getDealProductRows($barcode, $price): array
    {
        $res = [];

        try {
            Loader::includeModule('crm');
            Loader::includeModule('iblock');
            Loader::includeModule('catalog');

            // TODO - вытащить из админки свойство
            $productObj = \CIBlockElement::getList(
                [],
                [
                    'IBLOCK_ID' => Option::get('vikki.wb', "WB_CATALOG_ID"),
                    'PROPERTY_F_BARCODE_WB' => $barcode
                ],
                ['ID', 'NAME', 'PROPERTY_F_BARCODE_WB', 'IBLOCK_ID'],
            );
            // TODO - чекнуть товар из ВБ
            while ($product = $productObj->fetch()) {

                if (!$product['PROPERTY_F_BARCODE_WB']) continue; // FIX!!! If no barcode or property - we'll get ALL CATALOG!

                $res[] =
                    [
                        'PRODUCT_ID' => $product['ID'],
                        'PRODUCT_NAME' => $product['NAME'],
                        'PRICE' => $price,
                        'QUANTITY' => 1,
                        'PRICE_NETTO' => $price,
                        'PRICE_BRUTTO' => $price,
                    ];
            }
            return $res;
        } catch (\Exception $e) {
            Debug::writeToFile($e->getMessage(), date('d.m.Y H:i:s'), '/log/wb/error.log');
        }
        return $res;
    }

    /**
     */
    public function setDelivery(array $fields): void
    {
        Loader::includeModule('crm');
        try {
            $factory = Container::getInstance()->getFactory($this->getSpaDeliveryId());
            $this->updateFactory($factory, $fields, $fields['PARENT_ID_2']);
        } catch (\Throwable $e) {
            Debug::writeToFile([
                'msg' => $e->getMessage(),
            ], date('d.m.Y H:i:s'), '/log/wb/' . __FUNCTION__ . '.log');
        }
    }

    /**
     */
    public function setPayment(array $fields): void
    {
        Loader::includeModule('crm');
        try {
            $factory = Container::getInstance()->getFactory($this->getSpaPaymentId());
            $this->updateFactory($factory, $fields, $fields['PARENT_ID_2']);
        } catch (\Throwable $e) {
            Debug::writeToFile([
                'msg' => $e->getMessage(),
            ], date('d.m.Y H:i:s'), '/log/wb/' . __FUNCTION__ . '.log');
        }
    }

    private function updateFactory(?\Bitrix\Crm\Service\Factory $factory, array $fields, $dealId): void
    {
        Loader::includeModule('crm');

        try {
            $filter = [
                'filter' => [
                    'PARENT_ID_2' => $dealId,
                ],
                'order' => ['ID' => 'DESC'],
            ];

            $smartProcesses = $factory->getItems($filter);

            if (!$smartProcesses) {
                $info = $factory->createItem($fields);
                $result = $factory->getAddOperation($info)
                    ->disableAllChecks()
                    ->launch();
            } else {
                foreach ($fields as $key => $value) {
                    $smartProcesses[0]->set($key, $value);
                }
                $result = $factory->getUpdateOperation($smartProcesses[0])
                    ->disableAllChecks()
                    ->launch();
            }

        } catch (\Exception $e) {
            Debug::writeToFile([
                'msg' => $e->getMessage(),
                'dealId' => $dealId,
            ], date('d.m.Y H:i:s'), '/log/wb/'. __FUNCTION__. '.log');
        }
    }

}