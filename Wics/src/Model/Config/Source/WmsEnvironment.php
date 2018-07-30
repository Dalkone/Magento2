<?php
namespace WICS\Connector\Model\Config\Source;

class WmsEnvironment implements \Magento\Framework\Option\ArrayInterface {
    public function toOptionArray() {
        return [
            ['value' => 'test', 'label' => __('Staging')],
            ['value' => 'live', 'label' => __('Production')]
        ];
    }
}