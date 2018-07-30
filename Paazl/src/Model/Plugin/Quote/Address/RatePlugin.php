<?php
/**
 * Copyright (c) 2017 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */
namespace Paazl\Shipping\Model\Plugin\Quote\Address;

class RatePlugin
{

    /**
     * @param \Magento\Quote\Model\Quote\Address\Rate $subject
     * @param $proceed
     * @param \Magento\Quote\Model\Quote\Address\RateResult\AbstractResult $rate
     * @return mixed
     */
    public function aroundImportShippingRate(\Magento\Quote\Model\Quote\Address\Rate $subject, $proceed, \Magento\Quote\Model\Quote\Address\RateResult\AbstractResult $rate)
    {
        $return = $proceed($rate);

        if ($rate instanceof \Magento\Quote\Model\Quote\Address\RateResult\Method) {
            $identifier = $rate->getIdentifier();
            if (isset($identifier) && $identifier != '') {
                $return->setIdentifier(
                    $rate->getIdentifier()
                );
            }
            $paazlOption = $rate->getPaazlOption();
            if (isset($paazlOption) && $paazlOption != '') {
                $return->setPaazlOption(
                    $rate->getPaazlOption()
                );
            }
            $paazlNotification = $rate->getPaazlNotification();
            if (isset($paazlNotification) && $paazlNotification != '') {
                $return->setPaazlNotification(
                    $rate->getPaazlNotification()
                );
            }
            $paazlPreferredDate = $rate->getPaazlPreferredDate();
            if (isset($paazlPreferredDate) && $paazlPreferredDate != '') {
                $return->setPaazlPreferredDate(
                    $rate->getPaazlPreferredDate()
                );
            }
        }
        return $return;
    }
}