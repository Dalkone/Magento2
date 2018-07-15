<?php
namespace WICS\Connector\Cron;

use Psr\Log\LoggerInterface;
use WICS\Connector\Helper\Connectors\HagemanConnector;
use WICS\Connector\Helper\ConfigHelper;
use Magento\CatalogInventory\Api\StockRegistryInterface;

class Stock {
    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * @var HagemanConnector
     */
    protected $hagemanConnector;

    /**
     * @var ConfigHelper
     */
    protected $config;

    /**
     * @var StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * StockUpdate constructor.
     *
     * @param LoggerInterface $logger
     * @param HagemanConnector $hagemanConnector
     * @param ConfigHelper $config
     * @param StockRegistryInterface $stockRegistry
     */
    public function __construct(
        LoggerInterface $logger,
        HagemanConnector $hagemanConnector,
        ConfigHelper $config,
        StockRegistryInterface $stockRegistry
    ) {
        $this->_logger = $logger;
        $this->config = $config;
        $this->stockRegistry = $stockRegistry;

        if ($this->config->get(ConfigHelper::API_TYPE) == "hageman") {
            $this->connector = $hagemanConnector;
        }
    }

    /**
     * Get Stock from Server and update local product stock
     *
     * @return $this
     */
    public function execute() {
        if($this->config->get(ConfigHelper::ENABLE_STOCK)) {
            $this->_logger->debug($this->connector->getLabel().": Stock update started");
            
            $stock = $this->connector->getStock();
            $this->_logger->debug($this->connector->getLabel().": Connector loaded");

            if(!empty($stock)) {
                foreach($stock as $item) {
                    if($this->config->get(ConfigHelper::API_TYPE) == "hageman") {
                        try {
                            $stockItem = $this->stockRegistry->getStockItemBySku($item->article_code);
                            $stockItem->setQty($item->total->salable);
                            $stockItem->setIsInStock($item->total->salable >= ConfigHelper::OOS_LEVEL ? true : false);
                            $this->stockRegistry->updateStockItemBySku($item->article_code, $stockItem);
                            $this->_logger->debug($this->connector->getLabel().": Stock for ".$item->article_code." set to ".$item->total->salable);

                        } catch (\Exception $e) {
                            $this->_logger->debug($this->connector->getLabel().": Stock not ".$item->article_code." updated. Reason: ".$e->getMessage());
                        }
                    }
                }
            } else {
                $this->_logger->debug($this->connector->getLabel().": No stock to update");
            }

            $this->_logger->debug($this->connector->getLabel().": Stock update finished");
        }

        return $this;
    }
}