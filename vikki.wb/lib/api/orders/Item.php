<?php

namespace Vikki\WB\Api\Orders;

use Vikki\WB\Api\Orders\Model\Field;
use Vikki\WB\Api\WbConnector;
use Vikki\WB\Bitrix\OrdersTableManager;
use Exception;

class Item extends Field
{

    protected $orderWbId;
    protected $orderBitrixId;
    protected $warehouseId;
    protected $skus;
    protected $status;
    protected $price;
    protected $nmId;
    protected $rid;

    /**
     * @throws Exception
     */
    public function __construct($order)
    {
        $this->order = $order;
        $this->orderWbId = $this->getOrderWbId();
        $this->orderBitrixId = $this->getOrderBitrixId();
        $this->warehouseId = $this->getWarehouseId();
        $this->skus = $this->getSkus();
        $this->status = $this->getStatus();
        $this->price = $this->getPrice();
        $this->nmId = $this->getNmId();
        $this->rid = $this->getRid();
    }

    public function getQuery(): array
    {
        return [
            'ORDER_WB_ID' => $this->orderWbId,
            'ORDER_BITRIX_ID' => $this->orderBitrixId,
            'WAREHOUSE_ID' => $this->warehouseId,
            'SKUS' => $this->skus,
            'STATUS' => $this->status,
            'PRICE' => $this->price,
            'NM_ID' => $this->nmId,
            'RID' => $this->rid,
        ];
    }

    public function getOrderWbId()
    {
        return $this->getField('id');
    }

    public function getDateCreate()
    {
        return $this->getField('createdAt');
    }

    public function getWarehouseId()
    {
        return $this->getField('warehouseId');
    }

    public function getSkus()
    {
        return $this->getField('skus');
    }

    public function getPrice()
    {
        return $this->getField('convertedPrice')/100;
    }

    public function getNmId()
    {
        return $this->getField('nmId');
    }

    public function getRid()
    {
        return $this->getField('rid');
    }

    /**
     * @throws Exception
     */
    public function getOrderBitrixId()
    {
        $bitrixTable = new OrdersTableManager();
        return $bitrixTable->getBitrixOrderById($this->orderWbId);
    }

    public function getStatus()
    {
        $odj = new WbConnector();
        $result = $odj->getOrderStatus($this->orderWbId);
        if ($result["orders"][0]['wbStatus'])
        {
            return $result["orders"][0]['wbStatus'];
        }
        return 'error';
    }
}