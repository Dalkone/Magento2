<?php
/**
 * Copyright (c) 2017 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */
namespace Paazl\Shipping\Block\Form\Renderer;

/**
 * EAV Entity Attribute Form Renderer Block for Text
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Text extends \Paazl\Shipping\Block\Form\Renderer\AbstractRenderer
{
    /**
     * Return filtered and escaped value
     *
     * @return string
     */
    public function getEscapedValue()
    {
        return $this->escapeHtml($this->_applyOutputFilter($this->getValue()));
    }

    /**
     * Return array of validate classes
     *
     * @param boolean $withRequired
     * @return array
     */
    protected function _getValidateClasses($withRequired = true)
    {
        $classes = parent::_getValidateClasses($withRequired);
        $rules = $this->getAttributeObject()->getValidateRules();
        if (!empty($rules['min_text_length'])) {
            $classes[] = 'validate-length';
            $classes[] = 'minimum-length-' . $rules['min_text_length'];
        }
        if (!empty($rules['max_text_length'])) {
            if (!in_array('validate-length', $classes)) {
                $classes[] = 'validate-length';
            }
            $classes[] = 'maximum-length-' . $rules['max_text_length'];
        }

        return $classes;
    }
}
