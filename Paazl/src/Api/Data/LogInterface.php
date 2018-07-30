<?php
/**
 * Copyright (c) 2017 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */
namespace Paazl\Shipping\Api\Data;

/**
 * Interface LogInterface
 * @package Paazl\Shipping\Api
 */
interface LogInterface
{
    /**
     * @return int
     */
    public function getLogId();

    /**
     * @param $id
     * @return int
     */
    public function setLogId($id);

    /**
     * @return string
     */
    public function getLogType();

    /**
     * @param $type
     * @return string
     */
    public function setLogType($type);

    /**
     * @return string
     */
    public function getLogCode();

    /**
     * @param $code
     * @return mixed
     */
    public function setLogCode($code);

    /**
     * @return string
     */
    public function getOrderId();

    /**
     * @param $orderId
     * @return string
     */
    public function setOrderId($orderId);

    /**
     * @return string
     */
    public function getShipmentId();

    /**
     * @param $shipmentId
     * @return mixed
     */
    public function setShipmentId($shipmentId);

    /**
     * @return string
     */
    public function getMessage();

    /**
     * @param $message
     * @return string
     */
    public function setMessage($message);

    /**
     * @return mixed
     */
    public function getCreatedAt();

    /**
     * @param $createdAt
     * @return mixed
     */
    public function setCreatedAt($createdAt);
}
