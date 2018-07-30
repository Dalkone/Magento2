<?php
/**
 * Copyright (c) 2017 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */
namespace Paazl\Shipping\Model\Quote;

use Magento\Quote\Api\Data\EstimateAddressInterface;
use Magento\Quote\Model\Cart;
use Magento\Quote\Model\Quote;

class ShippingMethodManagement extends \Magento\Quote\Model\ShippingMethodManagement
{

    /** @var \Paazl\Shipping\Helper\Utility\Address */
    protected $addressHelper;

    /**
     * ShippingMethodManagement constructor.
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param Cart\ShippingMethodConverter $converter
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     * @param Quote\TotalsCollector $totalsCollector
     * @param \Paazl\Shipping\Helper\Utility\Address $addressHelper
     */
    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        Cart\ShippingMethodConverter $converter,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Quote\Model\Quote\TotalsCollector $totalsCollector,
        \Paazl\Shipping\Helper\Utility\Address $addressHelper
    )
    {
        $this->addressHelper = $addressHelper;
        parent::__construct($quoteRepository, $converter, $addressRepository, $totalsCollector);
    }


    /**
     * {@inheritDoc}
     */
    public function estimateByAddressId($cartId, $addressId)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);

        // no methods applicable for empty carts or carts with virtual products
        if ($quote->isVirtual() || 0 == $quote->getItemsCount()) {
            return [];
        }
        $address = $this->addressRepository->getById($addressId);

        $streetParts = $this->addressHelper->getMultiLineStreetParts($address->getStreet());

        return $this->getEstimatedRates(
            $quote,
            $address->getCountryId(),
            $address->getPostcode(),
            $address->getRegionId(),
            $address->getRegion(),
            $streetParts['street'],
            $streetParts['house_number'],
            $streetParts['addition']
        );
    }

    /**
     * Get estimated rates
     *
     * @param Quote $quote
     * @param int $country
     * @param string $postcode
     * @param int $regionId
     * @param string $region
     * @param string $street
     * @param string $houseNumber
     * @param string $addition
     * @return \Magento\Quote\Api\Data\ShippingMethodInterface[] An array of shipping methods.
     */
    protected function getEstimatedRates(\Magento\Quote\Model\Quote $quote, $country, $postcode, $regionId, $region, $street = null, $houseNumber = null, $addition = null)
    {
        $data = [
            EstimateAddressInterface::KEY_COUNTRY_ID => $country,
            EstimateAddressInterface::KEY_POSTCODE => $postcode,
            EstimateAddressInterface::KEY_REGION_ID => $regionId,
            EstimateAddressInterface::KEY_REGION => $region,
            'street' => $street,
            'house_number' => $houseNumber,
            'house_number_addition' => $addition,
        ];
        return $this->getShippingMethods($quote, $data);
    }

    /**
     * Get list of available shipping methods
     * @param \Magento\Quote\Model\Quote $quote
     * @param array $addressData
     * @return \Magento\Quote\Api\Data\ShippingMethodInterface[]
     */
    private function getShippingMethods(Quote $quote, array $addressData)
    {
        $output = [];
        $shippingAddress = $quote->getShippingAddress();
        $shippingAddress->addData($addressData);
        $shippingAddress->setCollectShippingRates(true);

        $this->totalsCollector->collectAddressTotals($quote, $shippingAddress);
        $shippingRates = $shippingAddress->getGroupedAllShippingRates();
        foreach ($shippingRates as $carrierRates) {
            foreach ($carrierRates as $rate) {
                $output[] = $this->converter->modelToDataObject($rate, $quote->getQuoteCurrencyCode());
            }
        }
        return $output;
    }
}