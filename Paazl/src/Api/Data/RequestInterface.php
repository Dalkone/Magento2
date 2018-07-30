<?php
/**
 * Copyright (c) 2017 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */
namespace Paazl\Shipping\Api\Data;

interface RequestInterface
{
    const RESPONSE = 'response';
    const ERRORS = 'errors';
    
    /**
     * @return array
     */
    public function getClientConfig();

    /**
     * @return string|int
     */
    public function getMethod();

    /**
     * @return array
     */
    public function getHeaders();
    
    /**
     * @return array|string
     */
    public function getBody();

    /**
     * @return array|string
     */
    public function getAuth();
    
    /**
     * @return mixed
     */
    public function getResponse();

    /**
     * @param array $response
     * @return mixed
     */
    public function setResponse($response);

    /**
     * @return mixed
     */
    public function getErrors();

    /**
     * @param array $errors
     * @return mixed
     */
    public function setErrors($errors);
}
