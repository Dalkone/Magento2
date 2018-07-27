<?php
namespace WICS\Connector\Ui\Component\Listing\Column;

use \Magento\Sales\Api\OrderRepositoryInterface;
use \Magento\Framework\View\Element\UiComponent\ContextInterface;
use \Magento\Framework\View\Element\UiComponentFactory;
use \Magento\Ui\Component\Listing\Columns\Column;
use \Magento\Framework\Api\SearchCriteriaBuilder;
use WICS\Connector\Helper\ConfigHelper;

class Status extends Column {
    /**
     * @var ConfigHelper
     */
    protected $config;

    /**
     * @var OrderRepositoryInterface
     */
    protected $_orderRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $_searchCriteria;

    /**
     * Status constructor.
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $criteria
     * @param ConfigHelper $config
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $criteria,
        ConfigHelper $config,
        array $components = [],
        array $data = [])
    {
        $this->_orderRepository = $orderRepository;
        $this->_searchCriteria  = $criteria;
        $this->config = $config;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource) {
        if(isset($dataSource["data"]["items"])) {
            foreach ($dataSource["data"]["items"] as & $item) {

                $order  = $this->_orderRepository->get($item["entity_id"]);
                $status = $order->getData("wics_status");

                switch($status) {
                    case "pending":
                        $export_status = __("Pending");
                        break;
                    case "processing";
                        $export_status = __("Processing");
                        break;
                    case "on_hold";
                        $export_status = __("On hold");
                        break;
                    case "backorder";
                        $export_status = __("Backorder");
                        break;
                    case "partial_delivered";
                        $export_status = __("Partial delivered");
                        break;
                    case "shipped";
                        $export_status = __("Shipment created");
                        break;
                    case "error";
                        $export_status = __("Error");
                        break;
                    default:
                        $export_status = "";
                        break;
                }

                $item[$this->getData("name")] = $export_status;
            }
        }
        
        return $dataSource;
    }
}