<?php

namespace Vikki\WB;

use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\Localization\Loc,
    Bitrix\Main\ORM\Data\DataManager,
    Bitrix\Main\ORM\Fields\DatetimeField,
    Bitrix\Main\ORM\Fields\IntegerField,
    Bitrix\Main\ORM\Fields\StringField,
    Bitrix\Main\ORM\Fields\Validators\LengthValidator,
    Bitrix\Main\Type\DateTime;
use Bitrix\Main\SystemException;

Loc::loadMessages(__FILE__);

/**
 * Class OrdersTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> ORDER_WB_ID int optional
 * <li> ORDER_BITRIX_ID int optional
 * <li> CREATED_DATE datetime optional default current datetime
 * <li> WAREHOUSE_ID int optional
 * <li> SKUS string(1000) optional
 * <li> STATUS string(100) optional
 * <li> PRICE string(100) optional
 * <li> NM_ID string(100) optional
 * <li> RID string(100) optional
 * </ul>
 *
 * @package Bitrix\Api
 **/
class OrdersTable extends DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName(): string
    {
        return 'wb_api_orders';
    }

    /**
     * Returns entity map definition.
     *
     * @return array
     * @throws SystemException
     */
    public static function getMap(): array
    {
        return [
            new IntegerField(
                'ID',
                [
                    'primary' => true,
                    'autocomplete' => true,
                    'title' => Loc::getMessage('ORDERS_ENTITY_ID_FIELD')
                ]
            ),
            new StringField(
                'ORDER_WB_ID',
                [
                    'title' => Loc::getMessage('ORDERS_ENTITY_ORDER_WB_ID_FIELD')
                ]
            ),
            new IntegerField(
                'ORDER_BITRIX_ID',
                [
                    'title' => Loc::getMessage('ORDERS_ENTITY_ORDER_BITRIX_ID_FIELD')
                ]
            ),
            new DatetimeField(
                'CREATED_DATE',
                [
                    'default' => function () {
                        return new DateTime();
                    },
                    'title' => Loc::getMessage('ORDERS_ENTITY_CREATED_DATE_FIELD')
                ]
            ),
            new IntegerField(
                'WAREHOUSE_ID',
                [
                    'title' => Loc::getMessage('ORDERS_ENTITY_WAREHOUSE_ID_FIELD')
                ]
            ),
            new StringField(
                'SKUS',
                [
                    'validation' => [__CLASS__, 'validateSkus'],
                    'title' => Loc::getMessage('ORDERS_ENTITY_SKUS_FIELD')
                ]
            ),
            new StringField(
                'STATUS',
                [
                    'validation' => [__CLASS__, 'validateStatus'],
                    'title' => Loc::getMessage('ORDERS_ENTITY_STATUS_FIELD')
                ]
            ),
            new StringField(
                'PRICE',
                [
                    'validation' => [__CLASS__, 'validatePrice'],
                    'title' => Loc::getMessage('ORDERS_ENTITY_PRICE_FIELD')
                ]
            ),
            new StringField(
                'NM_ID',
                [
                    'validation' => [__CLASS__, 'validateNmId'],
                    'title' => Loc::getMessage('ORDERS_ENTITY_NM_ID_FIELD')
                ]
            ),
            new StringField(
                'RID',
                [
                    'title' => 'RID'
                ]
            ),
        ];
    }

    /**
     * Returns validators for SKUS field.
     *
     * @return array
     * @throws ArgumentTypeException
     */
    public static function validateSkus(): array
    {
        return [
            new LengthValidator(null, 1000),
        ];
    }

    /**
     * Returns validators for STATUS field.
     *
     * @return array
     * @throws ArgumentTypeException
     */
    public static function validateStatus(): array
    {
        return [
            new LengthValidator(null, 100),
        ];
    }

    /**
     * Returns validators for PRICE field.
     *
     * @return array
     * @throws ArgumentTypeException
     */
    public static function validatePrice(): array
    {
        return [
            new LengthValidator(null, 100),
        ];
    }

    /**
     * Returns validators for NM_ID field.
     *
     * @return array
     * @throws ArgumentTypeException
     */
    public static function validateNmId(): array
    {
        return [
            new LengthValidator(null, 100),
        ];
    }
}