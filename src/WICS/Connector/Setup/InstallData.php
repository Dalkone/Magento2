<?php
namespace WICS\Connector\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Sales\Model\Order\Status;

class InstallData implements InstallDataInterface {
    /**
     * @var Status
     */
    protected $statusModel;

    /**
     * InstallData constructor.
     *
     * @param Status $statusModel
     */
    public function __construct(
        Status $statusModel
    ) {
        $this->statusModel = $statusModel;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context) {
        /* No install data */
    }
}