<?php

namespace Paazl\Shipping\Model\Data;

class Delivery implements \Paazl\Shipping\Api\Data\ShipmentDeliveryInterface
{

    protected $_data;


    /**
     * @inheritdoc
     */
    public function getData()
    {
        return $this->_data;
    }


    /**
     * @inheritdoc
     */
    public function setData($data)
    {
        $this->_data = $data;
    }


    /**
     * @inheritdoc
     */
    public function getServicePointAddress()
    {
        return isset($this->_data['service_point_address'])
            ? $this->_data['service_point_address'] : null;
    }


    /**
     * @inheritdoc
     */
    public function setServicePointAddress($servicePointAddress)
    {
        $this->_data['service_point_address'] = $servicePointAddress;
    }


    /**
     * @inheritdoc
     */
    public function getServicePointName()
    {
        return isset($this->_data['service_point_name'])
            ? $this->_data['service_point_name'] : null;
    }


    /**
     * @inheritdoc
     */
    public function setServicePointName($servicePointName)
    {
        $this->_data['service_point_name'] = $servicePointName;
    }


    /**
     * @inheritdoc
     */
    public function getServicePointPostcode()
    {
        return isset($this->_data['service_point_postcode'])
            ? $this->_data['service_point_postcode'] : null;
    }


    /**
     * @inheritdoc
     */
    public function setServicePointPostcode($postcode)
    {
        $this->_data['service_point_postcode'] = $postcode;
    }


    /**
     * @inheritdoc
     */
    public function getServicePointCity()
    {
        return isset($this->_data['service_point_city'])
            ? $this->_data['service_point_city'] : null;
    }


    /**
     * @inheritdoc
     */
    public function setServicePointCity($city)
    {
        $this->_data['service_point_city'] = $city;
    }


    /**
     * @inheritdoc
     */
    public function getDeliveryDate()
    {
        return isset($this->_data['delivery_date']) ? $this->_data['delivery_date']
            : null;
    }


    /**
     * @inheritdoc
     */
    public function setDeliveryDate($deliveryDate)
    {
        $this->_data['delivery_date'] = $deliveryDate;
    }


    /**
     * @inheritdoc
     */
    public function getDeliveryWindowStart()
    {
        return isset($this->_data['delivery_window_start'])
            ? $this->_data['delivery_window_start'] : null;
    }


    /**
     * @inheritdoc
     */
    public function setDeliveryWindowStart($deliveryWindowStart)
    {
        $this->_data['delivery_window_start'] = $deliveryWindowStart;
    }


    /**
     * @inheritdoc
     */
    public function getDeliveryWindowEnd()
    {
        return isset($this->_data['delivery_window_end'])
            ? $this->_data['delivery_window_end'] : null;
    }


    /**
     * @inheritdoc
     */
    public function setDeliveryWindowEnd($deliveryWindowEnd)
    {
        $this->_data['delivery_window_end'] = $deliveryWindowEnd;
    }


    /**
     * @inheritdoc
     */
    public function getDeliveryWindowText()
    {
        return isset($this->_data['delivery_window_text'])
            ? $this->_data['delivery_window_text'] : null;
    }


    /**
     * @inheritdoc
     */
    public function setDeliveryWindowText($deliveryWindowText)
    {
        $this->_data['delivery_window_text'] = $deliveryWindowText;
    }


    /**
     * @inheritdoc
     */
    public function getServicePointCode()
    {
        return isset($this->_data['service_point_code'])
            ? $this->_data['service_point_code'] : null;
    }


    /**
     * @inheritdoc
     */
    public function setServicePointCode($servicePointCode)
    {
        $this->_data['service_point_code'] = $servicePointCode;
    }
}