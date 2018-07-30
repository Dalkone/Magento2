<?php
/**
 * Copyright (c) 2017 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */
namespace Paazl\Shipping\Block\Adminhtml\Log;

use Paazl\Shipping\Model\Log;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Button\ButtonList;
use Magento\Backend\Block\Widget\Button\Item;
use Magento\Backend\Block\Widget\ContainerInterface;
use Magento\Backend\Block\Widget\Button\ToolbarInterface;
use Magento\Framework\Registry;

/**
 * Class Details
 * @package Paazl\Shipping\Block\Adminhtml\Log
 */
class Details extends Template implements ContainerInterface
{
    /** @var Registry $coreRegistry */
    protected $coreRegistry = null;

    /** @var Log $currentLog */
    protected $currentLog;

    /** @var  ButtonList $buttonList */
    protected $buttonList;

    /** @var ToolbarInterface $toolbar */
    protected $toolbar;

    /**
     * Details constructor.
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ButtonList $buttonList,
        ToolbarInterface $toolbar,
        array $data)
    {
        $this->coreRegistry = $registry;
        $this->buttonList = $buttonList;
        $this->toolbar = $toolbar;
        parent::__construct($context, $data);
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->buttonList->add(
            'add',
            [
                'label' => __('Go Back'),
                'onclick' => "window.location='" . $this->getBackUrl() . "'",
                'class' => 'add primary add-template'
            ]
        );
        $this->toolbar->pushButtons($this, $this->buttonList);
        return parent::_prepareLayout();
    }

    /**
     * @return Log
     */
    public function getCurrentLog()
    {
        if (null === $this->currentLog) {
            $this->currentLog = $this->coreRegistry->registry('current_log');
        }
        return $this->currentLog;
    }

    /**
     * @return string
     */
    public function getLogMessage() {
        return $this->getCurrentLog()->getMessage();
    }

    /**
     * @param string $buttonId
     * @param null|string $key
     * @param string $data
     * @return $this
     */
    public function updateButton($buttonId, $key, $data)
    {
        $this->buttonList->update($buttonId, $key, $data);
        return $this;
    }

    /**
     * @param string $buttonId
     * @param array $data
     * @param int $level
     * @param int $sortOrder
     * @param string $region
     * @return $this
     */
    public function addButton($buttonId, $data, $level = 0, $sortOrder = 0, $region = 'toolbar')
    {
        $this->buttonList->add($buttonId, $data, $level, $sortOrder, $region);
        return $this;
    }

    /**
     * @param string $buttonId
     * @return $this
     */
    public function removeButton($buttonId)
    {
        $this->buttonList->remove($buttonId);
        return $this;
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('*/*/index');
    }

    /**
     * @param Item $item
     * @return bool
     */
    public function canRender(Item $item)
    {
        return !$item->isDeleted();
    }
}
