<?php
/**
 * Copyright (c) 2017 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */
namespace Paazl\Shipping\Model\Api;

use Paazl\Shipping\Api\Data\RequestInterface;
use Magento\Framework\DataObject;

// implements RequestInterface

class Request extends DataObject
{
    const RESPONSE = 'response';
    const ERRORS = 'errors';

    protected $clientConfig;
    protected $context;
    protected $headers;
    protected $body;
    protected $auth;
    protected $method;
    protected $requestKey;

    /**
     * Request constructor.
     * @param array $clientConfig
     * @param string $context
     * @param $method
     * @param array $headers
     * @param array $body
     * @param array $data
     */
    public function __construct(
        array $clientConfig,
        $context,
        $method,
        array $headers,
        array $body,
        array $data
    ) {
        $this->clientConfig = $clientConfig;
        $this->context = $context;
        $this->method = $method;
        $this->headers = $headers;
        $this->body = $body;

        parent::__construct($data);
    }

    /**
     * @return array
     */
    public function getClientConfig()
    {
        return $this->clientConfig;
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
     * @return string
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @param $context
     * @return $this
     */
    public function setContext($context)
    {
        $this->context = $context;
        return $this;
    }

    /**
     * @return array
     */
    public function getMethod()
    {
        return $this->method;
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
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
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
     * @return array
     */
    public function getBody()
    {
        return $this->body;
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
     * @return mixed
     */
    public function getResponse()
    {
        return $this->getData(self::RESPONSE);
    }

    /**
     * @param $response
     * @return $this
     */
    public function setResponse($response)
    {
        return $this->setData(self::RESPONSE, $response);
    }

    /**
     * @return mixed
     */
    public function getErrors()
    {
        return $this->getData(self::ERRORS);
    }

    /**
     * @param array $errors
     * @return $this
     */
    public function setErrors($errors)
    {
        return $this->setData(self::ERRORS, $errors);
    }

    /**
     * Get a unique string based on the request parameters
     * which can be used as an identifier
     * @return string
     */
    public function getRequestKey()
    {
        if (is_null($this->requestKey)) {
            $this->setData('request_parameters', $this->getBody());
            $this->requestKey = (string)$this->getMethod() . '_' . $this->toJson(['request_parameters']);
        }
        return $this->requestKey;
    }

    /**
     * Set requestKey in case this has to be less complex
     * @param $key
     * @return $this
     */
    public function setRequestKey($key)
    {
        $this->requestKey = $key;
        return $this;
    }
}
