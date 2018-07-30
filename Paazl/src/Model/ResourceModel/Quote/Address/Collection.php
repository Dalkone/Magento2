<?php

namespace Paazl\Shipping\Model\ResourceModel\Quote\Address;

use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;

class Collection extends \Magento\Quote\Model\ResourceModel\Quote\Address\Collection
{
    /**
     * @var \Paazl\Shipping\Helper\Utility\Address
     */
    protected $addressHelper;

    /**
     * Collection constructor.
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param Snapshot $entitySnapshot
     * @param \Paazl\Shipping\Helper\Utility\Address $addressHelper
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        Snapshot $entitySnapshot,
        \Paazl\Shipping\Helper\Utility\Address $addressHelper,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null)
    {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $entitySnapshot, $connection, $resource);
        $this->addressHelper = $addressHelper;
    }


    /**
     * @param \Magento\Framework\DataObject $item
     * @return \Magento\Framework\DataObject
     */
    protected function beforeAddLoadedItem(\Magento\Framework\DataObject $item)
    {
        $convertedStreet = $this->addressHelper->getMultiLineStreetParts($item->getStreet());
        if (!$convertedStreet['house_number']) {
            $convertedStreet = $this->addressHelper->getStreetParts($item->getStreet());
        }

        $item->addData([
            'street_name' => $convertedStreet['street'],
            'house_number' => $convertedStreet['house_number'],
            'house_number_addition' => $convertedStreet['addition'],
        ]);
        return $item;
    }
}