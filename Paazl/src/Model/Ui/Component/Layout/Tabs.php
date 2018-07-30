<?php
/**
 * Copyright (c) 2017 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */
namespace Paazl\Shipping\Model\Ui\Component\Layout;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\UiComponent\DataSourceInterface;
use Magento\Ui\Component\Layout\Tabs\TabInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Framework\View\Element\UiComponent\LayoutInterface;
use Magento\Framework\View\Element\UiComponent\BlockWrapperInterface;

class Tabs extends \Magento\Ui\Component\Layout\Tabs
{
    /**
     * To prepare the structure of child components
     *
     * @param UiComponentInterface $component
     * @param string $parentName
     * @return array
     */
    protected function prepareChildComponents(UiComponentInterface $component, $parentName)
    {
        $name = $component->getName();
        // Remove old street from Admin -> customer -> addresses
        if (in_array($name, array('street', 'street_0', 'street_1'))) {
            return [$component, []];
        }

        $childComponents = $component->getChildComponents();

        $childrenStructure = [];
        foreach ($childComponents as $childName => $child) {
            $isVisible = $child->getData('config/visible');
            if ($isVisible !== null && $isVisible == 0) {
                continue;
            }
            /**
             * @var UiComponentInterface $childComponent
             * @var array $childStructure
             */
            list($childComponent, $childStructure) = $this->prepareChildComponents($child, $component->getName());
            $childrenStructure = array_merge($childrenStructure, $childStructure);
            $component->addComponent($childName, $childComponent);
        }

        $structure = [
            $name => [
                'type' => $component->getComponentName(),
                'name' => $component->getName(),
                'children' => $childrenStructure
            ]
        ];

        list($config, $dataScope) = $this->prepareConfig((array) $component->getConfiguration(), $name, $parentName);

        if ($dataScope !== false) {
            $structure[$name]['dataScope'] = $dataScope;
        }
        $structure[$name]['config'] = $config;

        return [$component, $structure];
    }
}