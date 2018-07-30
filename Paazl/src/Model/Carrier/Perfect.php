<?php
/**
 * Copyright (c) 2017 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */
namespace Paazl\Shipping\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;

class Perfect extends \Paazl\Shipping\Model\Carrier
{
    /** Paazl carrier code */
    const CODE = 'paazlp';

    /** @var string */
    protected $_code = self::CODE;

    /**
     * Collect and get rates
     * @param RateRequest $request
     * @return Result|bool|null
     */
    public function collectRates(RateRequest $request)
    {
        parent::collectRates($request);

        // If we don't have access to Paazl Perfect then only Paazl Default will be shown.
        if (!$this->hasAccessToPaazlPerfect()) {
            return false;
        }

        return $this->getResult();
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
                // @todo: possibly not needed because this is already done in parent class.
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

        $checkoutStatusRequestData = [
            'context' => $this->_paazlManagement->_getQuoteId(),
            'body' => [
                'orderReference' => $this->_paazlManagement->_getQuoteId(),
            ]
        ];

        $requestMethod = 'checkoutStatusRequest';
        if (!isset($this->_paazlData['requests'][$requestMethod])) {
            $checkoutStatusRequest = $this->_requestBuilder->build('PaazlCheckoutStatusRequest', $checkoutStatusRequestData);
            $this->_paazlData['requests'][$requestMethod] = $checkoutStatusRequest;

            $request = $this->_requestManager->doRequest($checkoutStatusRequest);
            $checkoutStatusKey = $request->getRequestKey();
            $response = $request->getResponse();

            if (!count($request->getErrors()) && !is_null($response)) {
                if (!is_null($request->getIdentifier())) {
                    $response['identifier'] = $request->getIdentifier();
                }
                $this->_paazlData['results'][$requestMethod][$request->getRequestKey()] = $response;
                $this->_setCachedQuotes($request->getRequestKey(), $response);
            }
        }

        $this->_paazlManagement->setPaazlData($this->_paazlData);

        $freeShippingThreshold = (float)$this->getConfigData('free_shipping_subtotal');
        $allowedMethods = $this->getAllowedMethods();

        // Choose something in Paazl Perfect?
        if (isset($this->_paazlData['results'][$requestMethod][$checkoutStatusKey]['callbackUrl'])) {
            if ($this->_paazlData['results'][$requestMethod][$checkoutStatusKey]['callbackUrl'] != "" &&
                isset($this->_paazlData['results'][$requestMethod][$checkoutStatusKey]['delivery']) &&
                $this->_paazlData['results'][$requestMethod][$checkoutStatusKey]['delivery']['option'] != "" &&
                $this->_paazlData['results'][$requestMethod][$checkoutStatusKey]['delivery']['deliveryType'] != ""
            ) {
                $data = $this->_paazlData['results'][$requestMethod][$checkoutStatusKey];
                $methodChosen = $data['delivery']['option'];
                $deliveryType = $data['delivery']['deliveryType'];

                // @todo: for Shipping + Delivery
                $allMethods = parent::getAllowedMethods();
                foreach ($allowedMethods as $method => $methodData) {
                    if ($deliveryType == $method) {
                        if (isset($allMethods[$methodChosen])) {
                            $method = $methodChosen;
                            $methodData = $allMethods[$methodChosen];
                            $title = $methodData['description'];
                        } else {
                            $title = $data['delivery']['description'];
                            if ($deliveryType == 'servicepoint') {
                                $method = 'SERVICE_POINT';
                                //$method = $methodChosen;
                                $methodData = [
                                    'distributor' => 'SERVICE_POINT',
                                    'title' => $data['delivery']['option'],
                                    'price' => $data['delivery']['rate'],
                                    'method' => 'SERVICE_POINT',
                                    'description' => $data['delivery']['description'],
                                    'identifier' => $data['delivery']['servicePoint']['code'],
                                    'paazl_option' => $data['delivery']['option'],
                                    'paazl_notification' => current($data['notification']),
                                    'servicePoint' => $data['delivery']['servicePoint'],
                                ];
                            }
                        }

                        $methodPrice = (in_array($method, $this->getCode('free_methods')))
                            ? 0
                            : $data['delivery']['rate'];

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
                        $rate->setMethod($methodData['method']);
                        if (isset($methodData['servicePoint'])) {
                            $rate->setIdentifier($data['delivery']['servicePoint']['code']);
                            $rate->setPaazlOption($data['delivery']['option']);
                        }
                        else {
                            if (isset($data['delivery']['preferredDeliveryDate'])) {
                                $rate->setPaazlPreferredDate($data['delivery']['preferredDeliveryDate']);
                            }
                        }
                        if (isset($data['notification'])) {
                            $rate->setPaazlNotification(current($data['notification']));
                        }
                        $rate->setMethodTitle($title);
                        $rate->setCarrierTitle('');
                        $rate->setCost($methodPrice);
                        $rate->setPrice($methodPrice);

                        $this->_paazlData['delivery'][$method] = $data['delivery'];
                        $this->_paazlManagement->setPaazlData($this->_paazlData);

                        $this->_result->append($rate);
                    }
                    else {
                        $title = $methodData['description'];

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

                        // set _paazlData['delivery'] for default options
                        if (isset($methodData['servicePoint'])) {
                            $this->_paazlData['delivery'][$methodData['method']] = [
                                'servicePoint' => $methodData['servicePoint'],
                            ];
                        }

                        if (isset($methodData['deliveryDates']) && isset($methodData['deliveryDates'][0]['deliveryDate'])) {
                            $this->_paazlData['delivery'][$methodData['method']] = [
                                'preferredDeliveryDate' => $methodData['deliveryDates'][0]['deliveryDate'],
                            ];
                        }

                        $rate = $this->_rateMethodFactory->create();
                        $rate->setCarrier(static::CODE);
                        $rate->setCarrierTitle($methodData['title']);
                        //$rate->setCarrierTitle(static::CODE);
                        $rate->setMethod($methodData['method']);
                        $rate->setMethodTitle($title);
                        $rate->setCarrierTitle('');
                        $rate->setCost($methodPrice);
                        $rate->setPrice($methodPrice);

                        $this->_paazlManagement->setPaazlData($this->_paazlData);

                        $this->_result->append($rate);
                    }
                }

                return $this->_result;
            }
        }

        // Before choosing something in Paazl Perfect
        foreach ($allowedMethods as $method => $methodData) {
            $title = $methodData['description'];
            $this->_paazlData['delivery'][$methodData['method']] = [];

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

            // set _paazlData['delivery'] for default options
            if (isset($methodData['servicePoint'])) {
                $this->_paazlData['delivery'][$methodData['method']] = [
                    'servicePoint' => $methodData['servicePoint'],
                ];
            }

            if (isset($methodData['deliveryDates']) && isset($methodData['deliveryDates'][0]['deliveryDate'])) {
                $this->_paazlData['delivery'][$methodData['method']] = [
                    'preferredDeliveryDate' => $methodData['deliveryDates'][0]['deliveryDate'],
                ];
            }

            $rate = $this->_rateMethodFactory->create();
            $rate->setCarrier(static::CODE);
            $rate->setCarrierTitle($methodData['title']);
            //$rate->setCarrierTitle(static::CODE);
            //$rate->setMethod($method);
            $rate->setMethod($methodData['method']);
            if (isset($methodData['servicePoint'])) {
                $rate->setIdentifier($methodData['servicePoint']['code']);
                $rate->setPaazlOption($methodData['servicePoint']['shippingOption']);

                $quoteId = str_replace($this->_paazlManagement->getReferencePrefix(), '', $this->_paazlManagement->_getQuoteId());
                $quote = $this->quoteFactory->create()->setStoreId($this->storeManager->getStore()->getId())->load($quoteId);
                $rate->setPaazlNotification($quote->getShippingAddress()->getTelephone()); // Set default to telephone
            }
            else {
                if (isset($methodData['deliveryDates']) && isset($methodData['deliveryDates'][0]['deliveryDate'])) {
                    $rate->setPaazlPreferredDate($methodData['deliveryDates'][0]['deliveryDate']);
                }
            }
            $rate->setMethodTitle($title);
            $rate->setCarrierTitle('');
            $rate->setCost($methodPrice);
            $rate->setPrice($methodPrice);

            $this->_paazlManagement->setPaazlData($this->_paazlData);

            $this->_result->append($rate);
        }

        $this->_paazlManagement->setPaazlData($this->_paazlData);

        return $this->_result;
    }

    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        $methods = parent::getAllowedMethods();

        if (empty($methods)) {
            return [];
        }

        // Sort by price
        uasort($methods, ["\Paazl\Shipping\Model\Carrier\Perfect", "cmp"]);

        $key = key($methods);

        if ($key == 'SERVICE_POINT' && next($methods) !== false) {
            next($methods);
            $key = key($methods);
        }

        $data = [
            'delivery' => $methods[$key],
        ];

        if (isset($methods['SERVICE_POINT'])) {
            $data['servicepoint'] = $methods['SERVICE_POINT'];
        }

        return $data;
    }

    private function cmp($a, $b)
    {
        if ($a['price'] == $b['price']) {
            return 0;
        }
        return ($a['price'] < $b['price']) ? -1 : 1;
    }
}