<?php

namespace Paazl\Shipping\Api\Data;

interface ShipmentDeliveryInterface
{

    /**
     * Get the Service Point Address
     *
     * @return string
     */
    public function getServicePointAddress();


    /**
     * Set the Service Point Address
     *
     * @param string $servicePointAddress
     * @return $this
     */
    public function setServicePointAddress($servicePointAddress);


    /**
     * Get the Service Point Name
     *
     * @return string
     */
    public function getServicePointName();


    /**
     * Set the Service Point Name
     *
     * @param string $servicePointName
     * @return $this
     */
    public function setServicePointName($servicePointName);


    /**
     * Get the Service Point Postcode
     *
     * @return string
     */
    public function getServicePointPostcode();


    /**
     * Set the Service Point Postcode
     *
     * @param string $postcode
     * @return $this
     */
    public function setServicePointPostcode($postcode);


    /**
     * Get the Service Point City
     *
     * @return string
     */
    public function getServicePointCity();


    /**
     * Set the Service Point City
     *
     * @param string $city
     * @return $this
     */
    public function setServicePointCity($city);


    /**
     * Get the Delivery Date
     *
     * @return string
     */
    public function getDeliveryDate();


    /**
     * Set the Delivery Date
     *
     * @param string $deliveryDate
     * @return $this
     */
    public function setDeliveryDate($deliveryDate);


    /**
     * Get the Delivery Window Start
     *
     * @return string
     */
    public function getDeliveryWindowStart();


    /**
     * Set the Delivery Window Start
     *
     * @param string $deliveryWindowStart
     * @return $this
     */
    public function setDeliveryWindowStart($deliveryWindowStart);


    /**
     * Get the Delivery Window End
     *
     * @return string
     */
    public function getDeliveryWindowEnd();


    /**
     * Set the Delivery Window End
     *
     * @param string $deliveryWindowEnd
     * @return $this
     */
    public function setDeliveryWindowEnd($deliveryWindowEnd);


    /**
     * Get the Delivery Window Text
     *
     * @return string
     */
    public function getDeliveryWindowText();


    /**
     * Set the Delivery Window End
     *
     * @param string $deliveryWindowText
     * @return $this
     */
    public function setDeliveryWindowText($deliveryWindowText);


    /**
     * Get the Service Point Code
     *
     * @return string
     */
    public function getServicePointCode();


    /**
     * Set the Service Point Code
     *
     * @param string $servicePointCode
     * @return $this
     */
    public function setServicePointCode($servicePointCode);
}
