<?php
/**
 * Copyright (c) 2017 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */
namespace Paazl\Shipping\Model;

use Paazl\Shipping\Api\Data\LogInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Class Log
 * @package Paazl\Shipping\Model
 */
class Log extends AbstractModel implements LogInterface
{
    /**
     * Initialize resource model
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Paazl\Shipping\Model\ResourceModel\Log');
    }

    /**
     * @return int
     */
    public function getLogId()
    {
        return $this->getData('log_id');
    }

    /**
     * @param $id
     * @return int
     */
    public function setLogId($id)
    {
        return $this->setData('log_id', $id);
    }

    /**
     * @return string
     */
    public function getLogType()
    {
        return $this->getData('log_type');
    }

    /**
     * @param $type
     * @return string
     */
    public function setLogType($type)
    {
        return $this->setData('log_type', $type);
    }

    /**
     * @return string
     */
    public function getLogCode()
    {
        return $this->getData('log_code');
    }

    /**
     * @param $code
     * @return string
     */
    public function setLogCode($code)
    {
        return $this->setData('log_code', $code);
    }

    /**
     * @return string
     */
    public function getOrderId()
    {
        return $this->getData('order_id');
    }

    /**
     * @param $orderId
     * @return string
     */
    public function setOrderId($orderId)
    {
        return $this->setData('log_code', $orderId);
    }

    /**
     * @return string
     */
    public function getShipmentId()
    {
        return $this->getData('shipment_id');
    }

    /**
     * @param $shipmentId
     * @return string
     */
    public function setShipmentId($shipmentId)
    {
        return $this->setData('shipment_id', $shipmentId);
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->getData('message');
    }

    /**
     * @param $message
     * @return string
     */
    public function setMessage($message)
    {
        return $this->setData('message', $message);
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->getData('created_at');
    }

    /**
     * @param $createdAt
     * @return mixed
     */
    public function setCreatedAt($createdAt)
    {
        $this->setData('created_at', $createdAt);
    }
}
