<?php
namespace WICS\Connector\Model\Config\Source;

class WmsType implements \Magento\Framework\Option\ArrayInterface {
    public function toOptionArray() {
        return [
            ['value' => 'hageman', 'label' => __('Hageman API')]
        ];
    }
}