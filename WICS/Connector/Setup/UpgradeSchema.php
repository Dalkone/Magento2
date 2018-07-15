<?php
namespace WICS\Connector\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class UpgradeSchema implements UpgradeSchemaInterface {
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $setup->startSetup();

        if(version_compare($context->getVersion(), "0.1.1") < 0) {
            $setup->getConnection()->addColumn(
                $setup->getTable("sales_order"),
                "wics_status",
                [
                    "type" => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    "length" => 255,
                    "nullable" => true,
                    "comment" => "Wics sync status"
                ]
            );
            
            $setup->getConnection()->addColumn(
                $setup->getTable("sales_order_grid"),
                "custom_column",
                [
                    "type" => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    "length" => 20,
                    "nullable" => true,
                    "comment" => "Wics sync status"
                ]
            );
        }

        if(version_compare($context->getVersion(), "1.0.0") < 0) {
            $setup->getConnection()->changeColumn(
                $setup->getTable("sales_order"),
                "wics_status",
                "wics_status",
                [
                    "type" => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    "length" => 20,
                    "comment" => "Wics sync status"
                ]
            );

            $setup->getConnection()->changeColumn(
                $setup->getTable("sales_order_grid"),
                "custom_column",
                "wics_status",
                [
                    "type" => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    "length" => 20,
                    "comment" => "Wics sync status"
                ]
            );
        }

        $setup->endSetup();
    }
}