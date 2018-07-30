<?php
/**
 * Copyright (c) 2017 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */

namespace Paazl\Shipping\Model\Shipping;

use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\Order\Address;
use Magento\Store\Model\ScopeInterface;

class Labels extends \Magento\Shipping\Model\Shipping\Labels
{
    /**
     * Prepare and do request to shipment
     *
     * @param Shipment $orderShipment
     * @return \Magento\Framework\DataObject
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function requestToShipmentWithUser(Shipment $orderShipment, $user)
    {
        $admin = $user;
        $order = $orderShipment->getOrder();

        $shippingMethod = $order->getShippingMethod(true);
        $shipmentStoreId = $orderShipment->getStoreId();
        $shipmentCarrier = $this->_carrierFactory->create($order->getShippingMethod(true)->getCarrierCode());
        $baseCurrencyCode = $this->_storeManager->getStore($shipmentStoreId)->getBaseCurrencyCode();
        if (!$shipmentCarrier) {
            throw new LocalizedException(__('Invalid carrier: %1', $shippingMethod->getCarrierCode()));
        }
        $shipperRegionCode = $this->_scopeConfig->getValue(
            Shipment::XML_PATH_STORE_REGION_ID,
            ScopeInterface::SCOPE_STORE,
            $shipmentStoreId
        );
        if (is_numeric($shipperRegionCode)) {
            $shipperRegionCode = $this->_regionFactory->create()->load($shipperRegionCode)->getCode();
        }

        $originStreet1 = $this->_scopeConfig->getValue(
            Shipment::XML_PATH_STORE_ADDRESS1,
            ScopeInterface::SCOPE_STORE,
            $shipmentStoreId
        );
        $storeInfo = new DataObject(
            (array)$this->_scopeConfig->getValue(
                'general/store_information',
                ScopeInterface::SCOPE_STORE,
                $shipmentStoreId
            )
        );

        if (!$admin->getFirstname()
            || !$admin->getLastname()
            || !$storeInfo->getName()
            || !$storeInfo->getPhone()
            || !$originStreet1
            || !$shipperRegionCode
            || !$this->_scopeConfig->getValue(
                Shipment::XML_PATH_STORE_CITY,
                ScopeInterface::SCOPE_STORE,
                $shipmentStoreId
            )
            || !$this->_scopeConfig->getValue(
                Shipment::XML_PATH_STORE_ZIP,
                ScopeInterface::SCOPE_STORE,
                $shipmentStoreId
            )
            || !$this->_scopeConfig->getValue(
                Shipment::XML_PATH_STORE_COUNTRY_ID,
                ScopeInterface::SCOPE_STORE,
                $shipmentStoreId
            )
        ) {
            throw new LocalizedException(
                __(
                    'We don\'t have enough information to create shipping labels. Please make sure your store information and settings are complete.'
                )
            );
        }

        /** @var $request \Magento\Shipping\Model\Shipment\Request */
        $request = $this->_shipmentRequestFactory->create();
        $request->setOrderShipment($orderShipment);
        $address = $order->getShippingAddress();

        $this->setShipperDetails($request, $admin, $storeInfo, $shipmentStoreId, $shipperRegionCode, $originStreet1);
        $this->setRecipientDetails($request, $address);

        $request->setShippingMethod($shippingMethod->getMethod());
        $request->setPackageWeight($order->getWeight());
        $request->setPackages($orderShipment->getPackages());
        $request->setBaseCurrencyCode($baseCurrencyCode);
        $request->setStoreId($shipmentStoreId);

        return $shipmentCarrier->requestToShipment($request);
    }
}