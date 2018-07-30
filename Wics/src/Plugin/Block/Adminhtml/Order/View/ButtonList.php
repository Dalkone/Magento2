<?php
namespace WICS\Connector\Plugin\Block\Adminhtml\Order\View;

use Magento\Backend\Block\Widget\Button\ItemFactory;
use Magento\Framework\Registry;
use WICS\Connector\Helper\ConfigHelper;
use WICS\Connector\Helper\Connectors\HagemanConnector;
use WICS\Connector\Helper\Connectors\WelppConnector;
use WICS\Connector\Helper\Connectors\WicsConnector;
use Magento\Backend\Model\UrlInterface;

class ButtonList extends \Magento\Backend\Block\Widget\Button\ButtonList {
    /**
     * @var UrlInterface
     */
    protected $backend;

    /**
     * @var ConfigHelper
     */
    protected $config;

    /**
     * @var HagemanConnector
     */
    protected $hagemanConnector;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var ItemFactory
     */
    protected $itemFactory;

    /**
     * @var
     */
    protected $connector;

    public function __construct(
        ItemFactory $itemFactory,
        Registry $registry,
        ConfigHelper $config,
        UrlInterface $backend,
        HagemanConnector $hagemanConnector
    ) {
        $this->registry = $registry;
        $this->itemFactory = $itemFactory;
        $this->config = $config;
        $this->backend = $backend;

        if($this->config->get(ConfigHelper::API_TYPE) == "hageman") {
            $this->connector = $hagemanConnector;
        }

        parent::__construct($itemFactory);

        $currentOrder =  $registry->registry("current_order");

        $availableOrderStates = explode(",", $config->get(ConfigHelper::STATUS_CODES));

        /* Add "Send to wics" button to order view if order has correct state, and is not yet send to wics */
        if(in_array($currentOrder->getState(), $availableOrderStates) && !in_array($currentOrder->getWics_status(), ["processing", "shipped"])) {

            $orderMessage = __("Are you sure you want to send this order to %1 ?", $this->connector->getLabel());
            $orderLabel = __("Send order to %1", $this->connector->getLabel());
            $orderUrl = $this->backend->getUrl("wics/process/order/" . $currentOrder->getIncrementId(), ["_current" => true]);

            $this->add("sendtowics", [
                "label" => $orderLabel,
                "class" => "action-secondary",
                "onclick" => "confirmSetLocation('{$orderMessage}', '{$orderUrl}')"

            ]);
        }

        /* Add "Get shipment data" button to order view if order has correct state, and shipment is not yet created. */
        if(in_array($currentOrder->getWics_status(), ["processing"])) {
            $shipLabel = __("Get shipment data from %1", $this->connector->getLabel());
            $shipUrl = $this->backend->getUrl("wics/process/shipment/" . $currentOrder->getIncrementId(), ["_current" => true]);

            $this->add("updateOrder", [
                "label" => $shipLabel,
                "onclick" => "setLocation('{$shipUrl}')"
            ]);
        }
    }
}