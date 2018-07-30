<?php
/**
 * Copyright (c) 2017 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */

namespace Paazl\Shipping\Model\Attribute\Source;

use Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory;
use Magento\Framework\DB\Ddl\Table;

class Matrix extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * @var OptionFactory
     */
    protected $optionFactory;

    /**
     * @param OptionFactory $optionFactory
     */
    public function __construct(OptionFactory $optionFactory)
    {
        $this->optionFactory = $optionFactory;
    }

    /**
     * Get all options
     *
     * @return array
     */
    public function getAllOptions()
    {
        if (null === $this->_options) {
            $options = [];
            $options[] = [
                'label' => 'None',
                'value' => '',
            ];
            $alphas = $this->getcolumnrange('A', 'ZZ');
            foreach ($alphas as $key => $alpha) {
                $options[] = [
                    'label' => $alpha,
                    'value' => $key,
                ];
            }

            $this->_options = $options;
        }
        return $this->_options;
    }

    /**
     * Get a text for option value
     *
     * @param string|integer $value
     * @return string|bool
     */
    public function getOptionText($value)
    {
        foreach ($this->getAllOptions() as $option) {
            if ($option['value'] == $value) {
                return $option['label'];
            }
        }
        return false;
    }

    /**
     * Retrieve flat column definition
     *
     * @return array
     */
    public function getFlatColumns()
    {
        $attributeCode = $this->getAttribute()->getAttributeCode();

        return [
            $attributeCode => [
                'unsigned' => false,
                'default' => null,
                'extra' => null,
                'type' => Table::TYPE_INTEGER,
                'nullable' => true,
                'comment' => 'Matrix',
            ],
        ];
    }

    /**
     * Retrieve Select for update Attribute value in flat table
     *
     * @param   int $store
     * @return  \Magento\Framework\DB\Select|null
     */
    public function getFlatUpdateSelect($store)
    {
        return $this->optionFactory->create()->getFlatUpdateSelect($this->getAttribute(), $store, false);
    }

    private function getcolumnrange($min, $max)
    {
        $pointer = strtoupper($min);
        $output = array();
        while ($this->positionalcomparison($pointer, strtoupper($max)) <= 0) {
            array_push($output, $pointer);
            $pointer++;
        }
        return $output;
    }

    private function positionalcomparison($a, $b)
    {
        $a1 = $this->stringtointvalue($a);
        $b1 = $this->stringtointvalue($b);
        if ($a1 > $b1) return 1;
        else if ($a1 < $b1) return -1;
        else return 0;
    }

    /*
    * e.g. A=1 - B=2 - Z=26 - AA=27 - CZ=104 - DA=105 - ZZ=702 - AAA=703
    */
    private function stringtointvalue($str)
    {
        $amount = 0;
        $strarra = array_reverse(str_split($str));

        for ($i = 0; $i < strlen($str); $i++) {
            $amount += (ord($strarra[$i]) - 64) * pow(26, $i);
        }
        return $amount;
    }
}
