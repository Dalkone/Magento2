<?php
/**
 * Copyright (c) 2017 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */
namespace Paazl\Shipping\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * Upgrade the Catalog module DB scheme
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('quote_shipping_rate'),
                'paazl_preferred_date',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'Preferred date'
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.2.2', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('paazl_log'),
                'response_time',
                [
                    'type' => Table::TYPE_FLOAT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'Response time'
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.3.3', '<')) {
            $this->createNewAddressColumns($setup, 'sales_order_address', 'street_name', 'Street Name');
            $this->createNewAddressColumns($setup, 'quote_address', 'street_name', 'Street Name');
            // @todo: remove these columns. It is being saved in customer_address_entity_varchar already
            $this->createNewAddressColumns($setup, 'customer_address_entity', 'street_name', 'Street Name');

            $this->createNewAddressColumns($setup, 'sales_order_address', 'house_number', 'House Number');
            $this->createNewAddressColumns($setup, 'quote_address', 'house_number', 'House Number');
            // @todo: remove these columns. It is being saved in customer_address_entity_varchar already
            $this->createNewAddressColumns($setup, 'customer_address_entity', 'house_number', 'House Number');

            $this->createNewAddressColumns($setup, 'sales_order_address', 'house_number_addition', 'House Number Addition');
            $this->createNewAddressColumns($setup, 'quote_address', 'house_number_addition', 'House Number Addition');
            // @todo: remove these columns. It is being saved in customer_address_entity_varchar already
            $this->createNewAddressColumns($setup, 'customer_address_entity', 'house_number_addition', 'House Number Addition');
        }
        $setup->endSetup();
    }


    /**
     * @param  $setup
     * @param string $tableName
     * @param string $columnName
     * @param string $columnComment
     */
    public function createNewAddressColumns(SchemaSetupInterface $setup, $tableName, $columnName, $columnComment)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable($tableName),
            $columnName,
            [
                'type'     => Table::TYPE_TEXT,
                'length'   => 255,
                'nullable' => true,
                'comment'  => $columnComment
            ]
        );
    }
}