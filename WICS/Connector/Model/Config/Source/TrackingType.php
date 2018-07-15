<?php
namespace WICS\Connector\Model\Config\Source;

class TrackingType implements \Magento\Framework\Option\ArrayInterface {
    public function toOptionArray() {
        return [
            ['value' => 'url', 'label' => __('Tracking url')],
            ['value' => 'code', 'label' => __('Tracking code')]
        ];
    }
}