<?php
/**
 * Copyright (c) 2017 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */
namespace Paazl\Shipping\Model;

use Paazl\Shipping\Model\Api\RequestBuilder;
use Magento\Quote\Model\Quote\Address\RateResult\Error;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Carrier\AbstractCarrierOnline;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Simplexml\Element;
use Magento\Framework\Xml\Security;
use Paazl\Shipping\Model\Api\Request as PaazlRequest;

/**
 * Paazl shipping
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Carrier extends AbstractCarrierOnline implements \Magento\Shipping\Model\Carrier\CarrierInterface
{
    /** Paazl carrier code */
    const CODE = 'paazl';

    /** @var RateRequest */
    protected $_request;

    /** @var \Magento\Shipping\Model\Rate\Result */
    protected $_result;

    /** @var array */
    protected $_paazlData = [];

    /** @var int */
    protected $_quoteId;

    /** @var string[] */
    protected $_errors = [];

    /** @var array */
    protected $_rates = [];

    /** @var string */
    protected $_code = self::CODE;

    /** @var RequestBuilder */
    protected $_requestBuilder;

    /** @var Api\RequestManager */
    protected $_requestManager;

    /** @var \Paazl\Shipping\Helper\Utility\Address */
    protected $_addressHelper;

    /** @var \Paazl\Shipping\Helper\Request\Order */
    protected $_orderHelper;

    /**
     * @var \Paazl\Shipping\Model\PaazlManagement
     */
    protected $_paazlManagement;

    /**
     * @var \Magento\Quote\Api\Data\ShippingMethodExtensionFactory
     */
    protected $shippingMethodExtensionFactory;

    /**
     * @var bool
     */
    protected $accessToPaazlPerfect;

    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * Array of quotes
     *
     * @var array
     */
    protected $_quotesSharedCache;

    /**
     * Carrier constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param Security $xmlSecurity
     * @param \Magento\Shipping\Model\Simplexml\ElementFactory $xmlElFactory
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory
     * @param \Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory
     * @param \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Directory\Helper\Data $directoryData
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param RequestBuilder $requestBuilder
     * @param Api\RequestManager $requestManager
     * @param \Paazl\Shipping\Helper\Utility\Address $addressHelper
     * @param \Paazl\Shipping\Helper\Request\Order $orderHelper
     * @param \Paazl\Shipping\Model\PaazlManagement $_paazlManagement
     * @param \Magento\Quote\Api\Data\ShippingMethodExtensionFactory $shippingMethodExtensionFactory
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        Security $xmlSecurity,
        \Magento\Shipping\Model\Simplexml\ElementFactory $xmlElFactory,
        \Magento\Shipping\Model\Rate\ResultFactory $rateFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory,
        \Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory,
        \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Directory\Helper\Data $directoryData,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Paazl\Shipping\Model\Api\RequestBuilder $requestBuilder,
        \Paazl\Shipping\Model\Api\RequestManager $requestManager,
        \Paazl\Shipping\Helper\Utility\Address $addressHelper,
        \Paazl\Shipping\Helper\Request\Order $orderHelper,
        \Paazl\Shipping\Model\PaazlManagement $_paazlManagement,
        \Magento\Quote\Api\Data\ShippingMethodExtensionFactory $shippingMethodExtensionFactory,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_requestBuilder = $requestBuilder;
        $this->_requestManager = $requestManager;
        $this->_addressHelper = $addressHelper;
        $this->_orderHelper = $orderHelper;
        $this->_paazlManagement = $_paazlManagement;
        $this->shippingMethodExtensionFactory = $shippingMethodExtensionFactory;
        $this->quoteFactory = $quoteFactory;
        $this->storeManager = $storeManager;
        $this->registry = $registry;
        parent::__construct(
            $scopeConfig,
            $rateErrorFactory,
            $logger,
            $xmlSecurity,
            $xmlElFactory,
            $rateFactory,
            $rateMethodFactory,
            $trackFactory,
            $trackErrorFactory,
            $trackStatusFactory,
            $regionFactory,
            $countryFactory,
            $currencyFactory,
            $directoryData,
            $stockRegistry,
            $data
        );
    }

    /**
     * Collect and get rates
     * @param RateRequest $request
     * @return Result|bool|null
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        if (!$this->canCollectRates()) {
            return $this->getErrorMessage();
        }

        $paazlData = $this->registry->registry('paazlData');

        $this->registry->register('paazl_current_store', $request->getStoreId(), true);

        // If we switch Paazl account the session get's mixed up. Easiest to see if checkoutStatusRequest has more than 1 result
        if (isset($paazlData['results']) && isset($paazlData['results']['checkoutStatusRequest'])) {
            if (count($paazlData['results']['checkoutStatusRequest']) > 1) {
                // Session mixed up. Reset it.
                $paazlData = null;
            }
        }

        $this->_paazlData = (!is_null($paazlData))
            ? $paazlData
            : ['orderReference' => false, 'requests' => [], 'results' => []];
        $this->_paazlManagement->setPaazlData($this->_paazlData);

        $this->setRequest($request);
        $this->_getQuotes();
        $this->_updateFreeMethodQuote($request);

        $this->registry->unregister('paazlData');
        $this->registry->register('paazlData', $this->_paazlManagement->getPaazlData());

        // If we have access to Paazl Perfect than disable Paazl Default
        if ($this->hasAccessToPaazlPerfect()) {
            return false;
        }

        return $this->getResult();
    }

    /**
     * Prepare (API) requests
     * @param RateRequest $request
     * @return $this
     */
    public function setRequest(RateRequest $request)
    {
        $this->_paazlData = $this->_paazlManagement->setRequest($request);
        $this->_request = $this->_paazlManagement->getRequest();

        return $this;
    }

    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        $methods = [];
        $requestKey = (string)$this->_paazlManagement->getShoppingOptionKey();
        $this->_paazlData = $this->_paazlManagement->getPaazlData();

        // Read shippingOption response
        if (isset($this->_paazlData['results']['shippingOption'][$requestKey]['shippingOptions'])) {
            $shippingOptionNode =
                $this->_paazlData['results']['shippingOption'][$requestKey]['shippingOptions']['shippingOption'];

            /**
             * <shippingOption type="AVG">
             * <type>AVG</type>
             * <description>AVG</description>
             * <deliverySchemeLineId>0</deliverySchemeLineId>
             * <distributor>TNT</distributor>
             * <price>5.0</price>
             * <deliveryDayOfWeekRange>MONDAY_SATURDAY</deliveryDayOfWeekRange>
             * <cod>false</cod>
             * <insurance>false</insurance>
             * <shipperNotification>false</shipperNotification>
             * <deliveryDates>
             * <deliveryDate>2020-01-01+01:00</deliveryDate>
             * </deliveryDates>
             * </shippingOption>
             */

            $shippingOptions = (isset($shippingOptionNode['type'])) ? [$shippingOptionNode] : $shippingOptionNode;
            $firstServicePoint = null;

            foreach ($shippingOptions as $shippingOption) {
                if (isset($shippingOption['servicePoints'])) {
                    //@todo Pickup point logic
                    if (isset($shippingOption['servicePoints']['servicePoint'][0]) && is_null($firstServicePoint)) {
                        $firstServicePoint = $shippingOption['servicePoints']['servicePoint'][0];
                        $methods[$shippingOption['type']]['servicePoint'] = $firstServicePoint;

                        // If there is no price than the shipping option is free.
                        if (!isset($firstServicePoint['price'])) {
                            $firstServicePoint['price'] = 0.0;
                        }

                        $methods[$shippingOption['type']] = [
                            'distributor' =>  $firstServicePoint['distributor'],
                            'title' => $firstServicePoint['distributor'],
                            'price' => $firstServicePoint['price'],
                            'method' => $shippingOption['type'],
                            'description' => $shippingOption['description'],
                            'identifier' => $firstServicePoint['code'],
                            'paazl_option' => $firstServicePoint['shippingOption'],
                        ];
                        $methods[$shippingOption['type']]['servicePoint'] = $firstServicePoint;
                    }
                } else {
                    if (isset($shippingOption['distributor'])) {
                        // If there is no price than the shipping option is free.
                        if (!isset($shippingOption['price'])) {
                            $shippingOption['price'] = 0.0;
                        }

                        $methods[$shippingOption['type']] = [
                            'distributor' =>  $shippingOption['distributor'],
                            'title' => $shippingOption['distributor'],
                            'price' => $shippingOption['price'],
                            'method' => $shippingOption['type'],
                            'description' => $shippingOption['description'],
                        ];

                        if (isset($shippingOption['deliveryDates']) && $shippingOption['deliveryDates']) {
                            $methods[$shippingOption['type']]['deliveryDates'] = $shippingOption['deliveryDates']['deliveryDateOption'];
                        }
                    } else {
                        $this->_logger->debug('$shippingOption', [$shippingOption]);
                    }
                }
            }
        }

        return $methods;
    }

    /**
     * @return Result|null
     */
    public function getResult()
    {
        return $this->_result;
    }

    /**
     * @todo Titles based on config
     * @param string $type
     * @param string $code
     * @return array|bool
     */
    public function getCode($type, $code = '')
    {
        $codes = [
            'method' => [
                'UPS_STANDARD' => __('Standard'),
                'AVG' => __('Daytime delivery'),
                'free' => __('Free')
            ],
            'free_methods' => [
                'free'
            ],
            'carriers' => [
                'kiala',
                'TNT' => 'PostNL',
                'dhl',
                'dhl_express',
                'bpost',
                'mondialrelay',
                'dpd',
                'UPS' => 'UPS'
            ]
        ];

        if (!isset($codes[$type])) {
            return false;
        } elseif ('' === $code) {
            return $codes[$type];
        }

        if (!isset($codes[$type][$code])) {
            return false;
        } else {
            return $codes[$type][$code];
        }
    }

    /**
     * @return Result
     */
    protected function _getQuotes()
    {
        $this->_result = $this->_rateFactory->create();
        $this->_paazlData = $this->_paazlManagement->getPaazlData();

        if (isset($this->_paazlData['requests'])) {
            if (count($this->_paazlData['requests'])) {
                // Gather previous results from session
                foreach ($this->_paazlData['results'] as $method => $results) {
                    foreach ($results as $dataKey => $result) {
                        $this->_setCachedQuotes($dataKey, $result);
                    }
                }
                // Make sure shippingOptionRequest is last and done after updateOrder again.
                $shippingOptionRequest = $this->_paazlData['requests']['shippingOption'];
                unset($this->_paazlData['requests']['shippingOption']);
                $this->_paazlData['requests']['shippingOption'] = $shippingOptionRequest;
                if (isset($this->_paazlData['requests']['updateOrderRequest'])) {
                    $this->_setCachedQuotes($this->_paazlData['requests']['shippingOption']->getRequestKey(), null);
                }

                foreach ($this->_paazlData['requests'] as $requestMethod => $request) {
                    $response = $this->_getCachedQuotes($request->getRequestKey());
                    if (is_null($response)) {
                        $request = $this->_requestManager->doRequest($request);
                        $response = $request->getResponse();

                        if (!count($request->getErrors()) && !is_null($response)) {
                            if (!is_null($request->getIdentifier())) {
                                $response['identifier'] = $request->getIdentifier();
                            }
                            $this->_paazlData['results'][$requestMethod][$request->getRequestKey()] = $response;
                            $this->_setCachedQuotes($request->getRequestKey(), $response);
                        }
                    }
                }
            }
            unset ($this->_paazlData['requests']);
        }

        if (isset($this->_paazlData['results']['orderRequest']['success'])) {
            // Set order reference
            $this->_paazlData['orderReference'] = $this->_paazlManagement->_getQuoteId();
        }
        elseif (isset($this->_paazlData['results']['updateOrderRequest']['success'])) {
            // Set order reference
            $this->_paazlData['orderReference'] = $this->_paazlManagement->_getQuoteId();
        }
        elseif (count($this->_paazlData['results']['orderRequest'] > 0)) {
            $orderRequest = current($this->_paazlData['results']['orderRequest']);
            if (isset($orderRequest['error']) && $orderRequest['error']['code'] == 1003) {
                // Set order reference
                $this->_paazlData['orderReference'] = $this->_paazlManagement->_getQuoteId();
            }
        }

        $this->_paazlManagement->setPaazlData($this->_paazlData);

        $freeShippingThreshold = (float)$this->getConfigData('free_shipping_subtotal');
        $allowedMethods = $this->getAllowedMethods();

        foreach ($allowedMethods as $method => $methodData) {
            $methodPrice = (in_array($method, $this->getCode('free_methods')))
                ? 0
                : $methodData['price'];

            if ($freeShippingThreshold > 0) {
                if ($this->_request->getPackageValueWithDiscount() > $freeShippingThreshold) {
                    if (in_array($method, $this->getCode('free_shipping_allowed_methods'))) {
                        $methodPrice = 0;
                    }
                }
            }

            $rate = $this->_rateMethodFactory->create();
            $rate->setCarrier(static::CODE);
            $rate->setCarrierTitle($methodData['title']);
            //$rate->setCarrierTitle(static::CODE);
            $rate->setMethod($method);
            //$rate->setMethod($methodData['method']);
            //$rate->setMethodTitle($methodData['distributor'] . ' - ' . $methodData['description']);
            $rate->setMethodTitle($methodData['description']);
            $rate->setCarrierTitle('');
            $rate->setCost($methodPrice);
            $rate->setPrice($methodPrice);

            $this->_result->append($rate);
        }

        return $this->_result;
    }

    /**
     * @return bool
     */
    public function hasAccessToPaazlPerfect()
    {
        if (!isset($this->accessToPaazlPerfect)) {
            $this->accessToPaazlPerfect = true;

            // Check if has access to Paazl Perfect
            $this->_paazlData = $this->_paazlManagement->getPaazlData();

            if (isset($this->_paazlData['results']) && isset($this->_paazlData['results']['checkoutStatusRequest'])) {
                $key = key($this->_paazlData['results']['checkoutStatusRequest']);
                $checkoutStatusRequest = $this->_paazlData['results']['checkoutStatusRequest'][$key];

                if (isset($checkoutStatusRequest['error'])) {
                    if ($checkoutStatusRequest['error']['code'] == 1053) {
                        $this->accessToPaazlPerfect = false;
                    }
                }
            }

            if (isset($this->_paazlData['results']) && isset($this->_paazlData['results']['checkoutRequest'])) {
                $key = key($this->_paazlData['results']['checkoutRequest']);
                $checkoutStatusRequest = $this->_paazlData['results']['checkoutRequest'][$key];

                if (isset($checkoutStatusRequest['error'])) {
                    if ($checkoutStatusRequest['error']['code'] == 1053) {
                        $this->accessToPaazlPerfect = false;
                    }
                }
            }
        }

        return $this->accessToPaazlPerfect;
    }

    /**
     * @param \Magento\Framework\DataObject $request
     * @return \Magento\Framework\DataObject
     */
    protected function _doShipmentRequest(\Magento\Framework\DataObject $request)
    {
        $result = new \Magento\Framework\DataObject();
        $context = $request->getOrderShipment()->getOrder()->getExtOrderId();

        $requestData = [
            'context' => $context,
            'body' => [
                'order' => [
                    'hash' => $this->_requestManager->getHash($context),
                    'orderReference' => $request->getOrderShipment()->getOrder()->getExtOrderId()
                ],
            ]
        ];

        array_walk($requestData['body']['order'], array($this->_orderHelper, 'soapvar'));

        array_walk($requestData['body'], array($this->_orderHelper, 'soapvarObj'));

        $generateImageLabelsRequest = $this->_requestBuilder->build('PaazlGenerateImageLabelsRequest', $requestData);
        try {
            $response = $this->_requestManager->doRequest($generateImageLabelsRequest)->getResponse();

            $label = $response['label'];
            // Check if more than 1 label then get the first label
            if (!isset($label['trackingNumber'])) {
                $label = $label[0];
            }

            $result->setShippingLabelContent($label['_']);
            $result->setTrackingNumber($label['trackingNumber']);
            $result->setGatewayResponse($response);
        }
        catch (\Exception $e) {
            $result->setErrors($e->getMessage());
        }

        return $result;
    }

    /**
     * Checks whether some request to rates have already been done, so we have cache for it
     * Used to reduce number of same requests done to carrier service during one session
     *
     * Returns cached response or null
     *
     * @param string|array $requestParams
     * @return null|string
     */
    protected function _getCachedQuotes($requestParams)
    {
        $key = $this->_getQuotesCacheKey($requestParams);

        return isset($this->_quotesSharedCache[$key]) ? $this->_quotesSharedCache[$key] : null;
    }

    /**
     * Sets received carrier quotes to cache
     *
     * @param string|array $requestParams
     * @param string $response
     * @return $this
     */
    protected function _setCachedQuotes($requestParams, $response)
    {
        $key = $this->_getQuotesCacheKey($requestParams);
        $this->_quotesSharedCache[$key] = $response;

        return $this;
    }
}
