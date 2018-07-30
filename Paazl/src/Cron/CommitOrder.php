<?php
/**
 * Copyright (c) 2017 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */
namespace Paazl\Shipping\Cron;

use Magento\Store\Model\ScopeInterface as StoreScopeInterface;

class CommitOrder
{
    /** Webshop Identifier config path */
    const XML_PATH_CRON_COMMIT_ORDERS = 'paazl/order/cron_export';

    /** @var \Magento\Sales\Api\OrderRepositoryInterface */
    protected $_orderRepository;

    /** @var \Magento\Sales\Model\OrderFactory */
    protected $_orderFactory;

    /** @var \Magento\Sales\Model\ResourceModel\Order */
    protected $_orderResource;

    /** @var \Paazl\Shipping\Model\Api\RequestBuilder */
    protected $_requestBuilder;

    /** @var \Paazl\Shipping\Model\Api\RequestManager */
    protected $_requestManager;

    /** @var \Magento\Framework\Api\SearchCriteriaBuilder */
    protected $_searchCriteriaBuilder;

    /** @var \Magento\Framework\Api\FilterBuilder */
    protected $_filterBuilder;

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface */
    protected $_scopeConfig;

    /**
     * @var \Paazl\Shipping\Model\PaazlManagement
     */
    protected $_paazlManagement;

    /** @var \Psr\Log\LoggerInterface */
    protected $_logger;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * CommitOrder constructor.
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Sales\Model\ResourceModel\Order $orderResource
     * @param \Paazl\Shipping\Model\Api\RequestBuilder $requestBuilder
     * @param \Paazl\Shipping\Model\Api\RequestManager $requestManager
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Paazl\Shipping\Model\PaazlManagement $paazlManagement
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Model\ResourceModel\Order $orderResource,
        \Paazl\Shipping\Model\Api\RequestBuilder $requestBuilder,
        \Paazl\Shipping\Model\Api\RequestManager $requestManager,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Paazl\Shipping\Model\PaazlManagement $_paazlManagement,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Registry $registry
    ) {
        $this->_orderRepository = $orderRepository;
        $this->_orderFactory = $orderFactory;
        $this->_orderResource = $orderResource;
        $this->_requestBuilder = $requestBuilder;
        $this->_requestManager = $requestManager;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_filterBuilder = $filterBuilder;
        $this->_scopeConfig = $scopeConfig;
        $this->_paazlManagement = $_paazlManagement;
        $this->_logger = $logger;
        $this->registry = $registry;
    }

    public function execute()
    {
        if ($this->_scopeConfig->isSetFlag(self::XML_PATH_CRON_COMMIT_ORDERS)) {
            // Orders with ext_order_id 0 are flagged for export
            $filterData = [
                ['ext_order_id', '0', 'eq']
            ];
            $filters = $this->_buildFilters($filterData);
            $searchCriteria = $this->_buildSearchCriteria($filters);
            $orders = $this->_orderRepository->getList($searchCriteria);

            foreach ($orders as $orderToCommit) {
                $order = $this->_orderFactory->create()->loadByIncrementId($orderToCommit->getIncrementId());
                $this->registry->unregister('paazl_current_store');
                $this->registry->register('paazl_current_store', $order->getStoreId());

                if (strpos('error-', $order->getExtOrderId() !== false)) continue;

                $extOrderId = $this->_paazlManagement->getReferencePrefix() . $order->getQuoteId();

                $response = $this->_paazlManagement->processOrderCommitRequest($order);
                if (isset($response['succes'])) {
                    $order->setExtOrderId($extOrderId);
                    $this->_orderResource->save($order);
                } else {
                    if (isset($response['error']['code'])) {
                        //@todo create new order reference and redo commit
                        $order->setExtOrderId('error-' . $response['error']['code']);
                        $this->_orderResource->save($order);
                    }
                }
            }
        }
    }

    /**
     * [['field', 'value']['field2', 'value2', 'like']]
     *
     * @param array $customFilters
     * @return array
     */
    protected function _buildFilters($customFilters = [])
    {
        $filters = [];

        foreach ($customFilters as $key => $data) {
            list($field, $value, $conditionType) = $data;

            if (is_null($conditionType)) $conditionType = 'eq';

            $newFilter = $this->_filterBuilder
                ->setField($field)
                ->setValue($value)
                ->setConditionType($conditionType)
                ->create();

            $filters[] = $newFilter;
        }

        return $filters;
    }

    /**
     * @param array $filters
     * @return \Magento\Framework\Api\SearchCriteria
     */
    protected function _buildSearchCriteria(array $filters = [])
    {
        $searchCriteria = $this->_searchCriteriaBuilder->addFilters($filters)->create();
        return $searchCriteria;
    }
}
