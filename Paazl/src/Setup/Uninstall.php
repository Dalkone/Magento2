<?php
/**
 * Copyright (c) 2017 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */
namespace Paazl\Shipping\Setup;

use Paazl\Shipping\Setup\PaazlSetup;
use Paazl\Shipping\Setup\PaazlSetupFactory;
use Magento\Framework\Setup\UninstallInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class Uninstall implements UninstallInterface
{

    /**
     * EAV setup factory
     *
     * @var PaazlSetupFactory
     */
    private $eavSetupFactory;

    /**
     * Init
     *
     * @param PaazlSetupFactory $eavSetupFactory
     */
    public function __construct(PaazlSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * Invoked when remove-data flag is set during module uninstall.
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->getConnection()->dropTable(
            $setup->getTable('paazl_log')
        );

        $setup->getConnection()->dropColumn($setup->getTable('quote_shipping_rate'), 'identifier');
        $setup->getConnection()->dropColumn($setup->getTable('quote_shipping_rate'), 'paazl_option');
        $setup->getConnection()->dropColumn($setup->getTable('quote_shipping_rate'), 'paazl_notification');
        $setup->getConnection()->dropColumn($setup->getTable('quote_shipping_rate'), 'paazl_preferred_date');

        /** @var PaazlSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create();

        foreach($eavSetup->getAttributeList() as $attributeInfo) {
            $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, $attributeInfo['attributeCode']);
        }
        $eavSetup->removeAttributeGroup(\Magento\Catalog\Model\Product::ENTITY, 'Default', 'Paazl');

        $eavSetup->removeAttribute(\Magento\Customer\Api\AddressMetadataInterface::ENTITY_TYPE_ADDRESS, 'street_name');
        $eavSetup->removeAttribute(\Magento\Customer\Api\AddressMetadataInterface::ENTITY_TYPE_ADDRESS, 'house_number');
        $eavSetup->removeAttribute(\Magento\Customer\Api\AddressMetadataInterface::ENTITY_TYPE_ADDRESS, 'house_number_addition');
    }
}