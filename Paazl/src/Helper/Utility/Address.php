<?php
/**
 * Copyright (c) 2017 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */
namespace Paazl\Shipping\Helper\Utility;


class Address extends \Paazl\Shipping\Helper\Data
{
    /**
     * @param $street
     * @return array
     */
    public function getMultiLineStreetParts($street)
    {
        // Magento implodes multi-line address fields with a newline
        $streetArray = (!is_array($street))
            ? explode("\n", (string)$street)
            : $street;
        $streetParts = [
            'street' => (isset($streetArray[0])) ? $streetArray[0] : null,
            'house_number' => (isset($streetArray[1])) ? $streetArray[1] : null,
            'addition' => (isset($streetArray[2])) ? $streetArray[2] : null
        ];

        return $streetParts;
    }
    
    /**
     * Get street parts
     * @param mixed $street
     * @return array mixed
     */
    public function getStreetParts($street, $format = true)
    {
        if (is_array($street)) $street = implode(' ', $street);
        $street = trim($street);
        if ($format) $street = preg_replace('!\h+!', ' ', $street);

        $patterns =  [
            '/^(\d*[\wäöüß\d \'\-\.]+)[,\s]+(\d+)\s*([\wäöüß\d\-\/]*)$/i',
            '/^(\d+)([,\s])+([\wäöüß\d \'\-\.]+)$/'
        ];
        $keys = [
            ['street', 'house_number', 'addition'],
            ['house_number', 'addition', 'street'],
        ];
        $streetParts = array_fill_keys(['street', 'house_number', 'addition'], '');
        foreach ($patterns as $key => $pattern) {
            if (preg_match($pattern, $street, $matches)) {
                array_shift($matches);
                $streetParts = array_combine($keys[$key], $matches);
                break;
            }
        }

        return $streetParts;
    }

    /**
     * @param $postCode
     * @param bool $format
     * @return bool|string
     */
    public function isDutchPostcode($postCode, $format = false)
    {
        $pattern = '/^([1-9][0-9]{3})\s?(?!sa|sd|ss)([a-zA-Z]{2})$/';
        if (preg_match($pattern, $postCode, $matches)) {
            if ($format) {
                $postCode = strtoupper($postCode);
                $postCode = preg_replace('/\s+/', '', $postCode);
                return $postCode;
            }
            return true;
        }
        return false;
    }
}
