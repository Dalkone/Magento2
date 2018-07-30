<?php
namespace Paazl\Shipping\Model\Plugin;

use Magento\Sales\Api\Data\OrderAddressExtensionFactory;
use Magento\Sales\Api\Data\OrderAddressExtensionInterface;
use Magento\Sales\Api\Data\OrderAddressInterface;

class SalesOrderApiDataOrderAddressInterfacePlugin
{
    /**
     * @var OrderAddressExtensionFactory
     */
    private $addressExtensionFactory;


    /**
     * SalesOrderApiDataOrderAddressInterfacePlugin constructor.
     *
     * @param OrderAddressExtensionFactory $addressExtensionFactory
     */
    public function __construct(
        OrderAddressExtensionFactory $addressExtensionFactory
    ) {
        $this->addressExtensionFactory = $addressExtensionFactory;
    }


    /**
     * @param OrderAddressInterface               $subject
     * @param OrderAddressExtensionInterface|null $extension
     *
     * @return \Magento\Sales\Api\Data\OrderAddressExtension|OrderAddressExtensionInterface
     */
    public function afterGetExtensionAttributes(
        OrderAddressInterface $subject,
        OrderAddressExtensionInterface $extension = null
    ) {
        if ($extension === null) {
            $extension = $this->addressExtensionFactory->create();
            $subject->setExtensionAttributes($extension);
        }
        $extension->setStreetName($subject->getData('street_name'));
        $extension->setHouseNumber($subject->getData('house_number'));
        $extension->setHouseNumberAddition($subject->getData('house_number_addition'));
        return $extension;
    }
}
