<?php
namespace WICS\Connector\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

/**
 * Class ConfigHelper
 * @package WICS\Connector\Helper
 */
class ConfigHelper extends AbstractHelper {
    /**
     * API Type
     * This is the wms type to connect with
     */
    const API_TYPE              = 'wics_connector/general/api_type';

    /**
     * API Url
     * Only for enterprise WMS, the url of the API to connect with.
     */
    const API_URL               = 'wics_connector/general/api_url';

    /**
     * API Environment
     * Only for hageman.
     */
    const API_ENVIRONMENT       = 'wics_connector/general/api_environment';

    /**
     * API Client
     * ID of the client using the API
     */
    const API_CLIENT            = 'wics_connector/general/api_client';

    /**
     * API Key
     */
    const API_KEY               = 'wics_connector/general/api_key';

    /**
     * API Secret
     */
    const API_SECRET            = 'wics_connector/general/api_secret';

    /**
     * Send Shipping Email
     * Weather Magento or the WMS sends the e-mail with tracking information
     */
    const SEND_SHIPPING_EMAIL   = 'wics_connector/general/send_shipping_email';

    /**
     * Enable orders
     * Should the module send new orders to the WMS
     */
    const ENABLE_ORDERS         = 'wics_connector/general/enable_orders';

    /**
     * Enable Stock
     * Should the module fetch stock updates from the WMS
     */
    const ENABLE_STOCK          = 'wics_connector/general/enable_stock';

    /**
     * OOS Level
     * Level of stock before labeled: Out Of Stock
     */
    const OOS_LEVEL          = 'wics_connector/general/oos_level';

    /**
     * Status code
     * Status code to send the orders to WMS
     */
    const STATUS_CODES          = 'wics_connector/general/order_process_statuscode';

    /**
     * Testmode
     * Only for WELPP WMS. Use test or production URL
     */
    const TESTMODE              = 'wics_connector/general/testmode';


    /**
     * URL of WICS tussenlaag server
     */
    const WICS_SERVER_URL       = 'https://wics-api.wics.nl/ws/3/';

    /**
     * ID of the webshop (WICS Enterprise only)
     */
    const WEBSHOP_ID            = 'wics_connector/general/webshop_id';

    /**
     * Type of order tracking (URL or Code)
     */
    const TRACKING_TYPE         = 'wics_connector/general/tracking_type';

    /**
     * LAYER_HOST
     */
    const LAYER_HOST            = 'wics_connector/advanced/layer_host';

    /**
     * LAYER_USER
     */
    const LAYER_USER            = 'wics_connector/advanced/layer_user';

    /**
     * LAYER_PASSWORD
     */
    const LAYER_PASSWORD        = 'wics_connector/advanced/layer_password';

    /**
     * LAYER_DATABASE
     */
    const LAYER_DATABASE        = 'wics_connector/advanced/layer_database';

    /**
     * LAYER_PORT
     */
    const LAYER_PORT            = 'wics_connector/advanced/layer_port';

    /**
     * @var \Magento\Framework\App\DeploymentConfig
     */
    protected $deploymentConfig;

    /**
     * @var \Magento\Framework\Component\ComponentRegistrarInterface
     */
    protected $componentRegistrar;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadFactory
     */
    protected $readFactory;

    /**
     * @param \Magento\Framework\App\Helper\Context $context,
     * @param \Magento\Framework\App\DeploymentConfig $deploymentConfig
     * @param \Magento\Framework\Component\ComponentRegistrarInterface $componentRegistrar
     * @param \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\DeploymentConfig $deploymentConfig,
        \Magento\Framework\Component\ComponentRegistrarInterface $componentRegistrar,
        \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory
    ) {
        $this->deploymentConfig = $deploymentConfig;
        $this->componentRegistrar = $componentRegistrar;
        $this->readFactory = $readFactory;

        parent::__construct($context);
    }

    /**
     * Get configuration value
     *
     * @param string $path
     * @return mixed
     */
    public function get($path) {
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get module composer version
     *
     * @param $moduleName
     * @return string module version
     */
    public function getModuleVersion($moduleName) {
        $path = $this->componentRegistrar->getPath(
            \Magento\Framework\Component\ComponentRegistrar::MODULE,
            $moduleName
        );
        $directoryRead = $this->readFactory->create($path);
        $composerJsonData = $directoryRead->readFile('composer.json');
        $data = json_decode($composerJsonData);

        return !empty($data->version) ? $data->version : __('Read error!');
    }


}