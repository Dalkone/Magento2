<?php
/**
 * Copyright (c) 2017 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */
namespace Paazl\Shipping\Model\Api;

class RequestBuilder
{
    /** @var array */
    private $clientConfig = [];
    /** @var string */
    private $context = '';
    /** @var array */
    private $headers = [];
    /** @var string */
    private $method;
    /** @var array */
    private $body = [];

    /** @var \Magento\Framework\ObjectManagerInterface */
    protected $objectManager;

    /**
     * RequestFactory constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param string $clientConfig
     * @return $this
     */
    public function setClientConfig($clientConfig)
    {
        $this->clientConfig = $clientConfig;
        return $this;
    }

    /**
     * @param $headers
     * @return $this
     */
    public function setHeaders($headers)
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * @param $method
     * @return $this
     */
    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * @param $body
     * @return $this
     */
    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }

    /**
     * @param $method
     * @param array $data
     * @param bool $clearData
     * @return mixed
     */
    public function build($method, $data = [], $clearData = true)
    {
        $requestData = [
            'clientConfig' => $this->clientConfig,
            'context' => $this->context,
            'headers' => $this->headers,
            'body' => $this->body,
            'data' => []
        ];
        $requestData = array_replace_recursive($requestData, $data);
        
        // Clear data
        if ($clearData) {
            $this->clientConfig = [];
            $this->context = '';
            $this->headers = [];
            $this->method = null;
            $this->body = [];
        }

        $requestObject = $this->objectManager->create($method, $requestData);
        return $requestObject;
    }
}
