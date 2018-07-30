<?php
/**
 * Copyright (c) 2017 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */
namespace Paazl\Shipping\Block\Form\Renderer;

/**
 * EAV Entity Attribute Form Renderer Block for select
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Select extends \Paazl\Shipping\Block\Form\Renderer\AbstractRenderer
{
    /**
     * Return array of select options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->getAttributeObject()->getSource()->getAllOptions();
    }
}
