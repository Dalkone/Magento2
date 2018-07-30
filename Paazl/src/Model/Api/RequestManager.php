<?php
/**
 * Copyright (c) 2017 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */
namespace Paazl\Shipping\Model\Api;

use Magento\Framework\Webapi\Soap\ClientFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Paazl\Shipping\Helper\Log as LogHelper;
use Paazl\Shipping\Model\Api\Request as PaazlRequest;

class RequestManager
{
    /** Webshop Identifier config path */
    const XML_PATH_STORECONFIGURATION_PAAZL_WEBSHOP_ID       = 'paazl/api/webshop_id';
    /** Password for API calls config path */
    const XML_PATH_STORECONFIGURATION_PAAZL_PASSWORD         = 'paazl/api/password';
    /** Staging/Production config path */
    const XML_PATH_STORECONFIGURATION_PAAZL_API_TYPE         = 'paazl/api/api_type';
    /** Debug mode config path */
    const XML_PATH_STORECONFIGURATION_PAAZL_API_DEBUG        = 'paazl/debug/log';

    const XML_PATH_STORECONFIGURATION_PAAZL_DISPLAY_REAL_ERR = 'paazl/debug/display_real_error_msg';
    const XML_PATH_STORECONFIGURATION_PAAZL_CUSTOM_ERR       = 'paazl/debug/custom_error_msg';
    /** Staging/Production URL config path */
    const XML_PATH_STORECONFIGURATION_PAAZL_PRODUCTION_WSDL  = 'paazl/api_advanced/production_url';
    const XML_PATH_STORECONFIGURATION_PAAZL_STAGING_WSDL     = 'paazl/api_advanced/staging_url';

    /** @var string $webshopId */
    protected $webshopId;

    /** @var string $token */
    protected $password;

    /** @var mixed */
    protected $apiType;

    /** @var bool */
    protected $debugMode;

    /** @var  */
    private $clientFactory;

    /** @var ScopeConfigInterface $scopeConfig */
    protected $scopeConfig;

    /** @var \Magento\Framework\Api\SimpleDataObjectConverter */
    protected $objectConverter;

    /** @var \Paazl\Shipping\Model\Api\PaazlSoapError */
    protected $paazlError;

    /** @var LogHelper */
    protected $log;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $state;

    /**
     * RequestManager constructor.
     * @param ClientFactory $clientFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Api\SimpleDataObjectConverter $objectConverter
     * @param PaazlSoapError $paazlError
     * @param LogHelper $log
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\State $state
     */
    public function __construct(
        ClientFactory $clientFactory,
        ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Api\SimpleDataObjectConverter $objectConverter,
        \Paazl\Shipping\Model\Api\PaazlSoapError $paazlError,
        LogHelper $log,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\State $state
    ) {
        $this->clientFactory = $clientFactory;
        $this->scopeConfig = $scopeConfig;
        $this->objectConverter = $objectConverter;
        $this->paazlError = $paazlError;
        $this->log = $log;
        $this->registry = $registry;
        $this->state = $state;
    }

    /**
     * @param \Paazl\Shipping\Model\Api\Request $requestObject
     * @return \Paazl\Shipping\Model\Api\Request
     */
    public function doRequest(PaazlRequest $requestObject)
    {
        $this->setupVars();
        $errors = [];
        $clientConfig = $requestObject->getClientConfig();

        $options = (isset($clientConfig['options']))
            ? array_merge(['trace' => true], $clientConfig['options'])
            : ['trace' => true];

        $wsdl = $this->getWsdl($requestObject);
        $this->prepareFixedElements($requestObject);

        $client = $this->clientFactory->create(
            $wsdl,
            $options
        );

        try {
            if ($requestObject->getHeaders()) {
                $client->__setSoapHeaders($requestObject->getHeaders());
            }

            $startTime = microtime(true);
            $response = $client->__soapCall(
                $requestObject->getMethod(),
                [$requestObject->getBody()]
            );
            $responseTime = microtime(true) - $startTime;

            if (isset($response->error) && !in_array($response->error->code, [1003,1053])) { // 1053 = missing permission for Paazl Perfect, 1003 = An order with this reference already exists
                $paazlError = [
                    'log_type'  =>  'Paazl Error',
                    'log_code'  =>  $response->error->code,
                    'message'   =>  $this->paazlError->getMessageByCode($response->error->code)
                ];
                //@todo Config
                if ($paazlError['log_code'] != 1004) { // 1004 = zipcode + house number combination is incorrect.
                    $this->log->write($paazlError);
                }
                $errors[] = $paazlError;
            }
            if ($this->debugMode) {
                $requestObject->setLastRequest($client->__getLastRequest());

                $paazlLog = [
                    'log_type'  =>  'Paazl Request: ' . $requestObject->getMethod(),
                    'log_code'  =>  0,
                    'message'   =>  print_r($requestObject->getBody(), true),
                    'response_time' => $responseTime,
                ];
                $this->log->write($paazlLog);

                $paazlLog = [
                    'log_type'  =>  'Paazl Response: ' . $requestObject->getMethod(),
                    'log_code'  =>  0,
                    'message'   =>  print_r($response, true),
                    'response_time' => $responseTime,
                ];
                $this->log->write($paazlLog);
            }

            // Response handling by custom "handler" classes?
            $responseArray = $this->objectConverter->convertStdObjectToArray($response);
            $requestObject->setResponse($responseArray);
        } catch (\Exception $e) {
            $errors = [$e->getMessage()];
            $requestObject->setLastRequest($client->__getLastRequest());

            $paazlLog = [
                'log_type'  =>  'Paazl Request: ' . $requestObject->getMethod(),
                'log_code'  =>  1,
                'message'   =>  print_r($client->__getLastRequest(), true),
                'response_time' => '',
            ];
            $this->log->write($paazlLog);

            $paazlLog = [
                'log_type'  =>  'Request body',
                'log_code'  =>  1,
                'message'   =>  print_r($requestObject->getBody(), true),
                'response_time' => '',
            ];
            $this->log->write($paazlLog);
        }

        $requestObject->setErrors($errors);
        if (count($errors)) {
            if ($this->showRealErrorMsg || $this->state->getAreaCode() == \Magento\Framework\App\Area::AREA_CRONTAB) {
                if (isset($errors[0]['message'])) {
                    $throwMsg = $errors[0]['message'];
                }
                elseif (is_string($errors[0])) {
                    $throwMsg = $errors[0];
                }
                else {
                    $throwMsg = $this->customErrorMsg;
                }

            }
            else {
                $throwMsg = $this->customErrorMsg;
            }

            if ($this->state->getAreaCode() !== \Magento\Framework\App\Area::AREA_CRONTAB) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __($throwMsg)
                );
            }
        }

        return $requestObject;
    }

    /**
     * @param $requestObject
     * @return mixed
     */
    private function getWsdl($requestObject)
    {
        $clientConfig = $requestObject->getClientConfig();

        $wsdlConfigPath = ($this->apiType)
            ? (isset($clientConfig['wsdl'])) ? : self::XML_PATH_STORECONFIGURATION_PAAZL_PRODUCTION_WSDL
            : (isset($clientConfig['wsdl_staging'])) ? : self::XML_PATH_STORECONFIGURATION_PAAZL_STAGING_WSDL;

        $wsdl = $this->scopeConfig->getValue($wsdlConfigPath);

        return $wsdl;
    }

    /**
     * @param $requestObject
     */
    private function prepareFixedElements(&$requestObject)
    {
        $this->setupVars();
        $clientConfig = $requestObject->getClientConfig();
        $context = (string)$requestObject->getContext();

        $body = $requestObject->getBody();
        if ($requestObject->getMethod() == 'listOrders') {
            $context = date('Ymd');
        }

        $body['hash'] = sha1($this->webshopId . $this->password . $context);
        $body['webshop'] = (isset($clientConfig['webshop'])) ? $clientConfig['webshop'] : $this->webshopId;

        $requestObject->setBody($body);
    }

    /**
     * @param $context
     * @return string
     */
    public function getHash($context)
    {
        $this->setupVars();
        return sha1($this->webshopId . $this->password . $context);
    }

    protected function setupVars()
    {
        $currentStoreId = $this->registry->registry('paazl_current_store');
        $this->debugMode = $this->scopeConfig->isSetFlag(self::XML_PATH_STORECONFIGURATION_PAAZL_API_DEBUG, \Magento\Store\Model\ScopeInterface ::SCOPE_STORE, $currentStoreId);
        $this->apiType = $this->scopeConfig->getValue(self::XML_PATH_STORECONFIGURATION_PAAZL_API_TYPE, \Magento\Store\Model\ScopeInterface ::SCOPE_STORE, $currentStoreId);
        $this->webshopId = $this->scopeConfig->getValue(self::XML_PATH_STORECONFIGURATION_PAAZL_WEBSHOP_ID, \Magento\Store\Model\ScopeInterface ::SCOPE_STORE, $currentStoreId);
        $this->password =  $this->scopeConfig->getValue(self::XML_PATH_STORECONFIGURATION_PAAZL_PASSWORD, \Magento\Store\Model\ScopeInterface ::SCOPE_STORE, $currentStoreId);
        $this->showRealErrorMsg =  $this->scopeConfig->getValue(self::XML_PATH_STORECONFIGURATION_PAAZL_DISPLAY_REAL_ERR, \Magento\Store\Model\ScopeInterface ::SCOPE_STORE, $currentStoreId);
        $this->customErrorMsg =  $this->scopeConfig->getValue(self::XML_PATH_STORECONFIGURATION_PAAZL_CUSTOM_ERR, \Magento\Store\Model\ScopeInterface ::SCOPE_STORE, $currentStoreId);
    }
}
