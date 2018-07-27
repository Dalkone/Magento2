<?php
namespace WICS\Connector\Controller\Tracking;

use \Magento\Framework\App\ProductMetadataInterface;

class Popup extends \Magento\Shipping\Controller\Tracking\Popup {
    /**
     * @var ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Shipping\Model\InfoFactory
     */
    protected $_shippingInfoFactory;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * Popup constructor.
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Shipping\Model\InfoFactory $shippingInfoFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param ProductMetadataInterface $productMetadata
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Shipping\Model\InfoFactory $shippingInfoFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        ProductMetadataInterface $productMetadata
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_shippingInfoFactory = $shippingInfoFactory;
        $this->_orderFactory = $orderFactory;
        $this->productMetadata = $productMetadata;
        parent::__construct($context,$coreRegistry,$shippingInfoFactory,$orderFactory);
    }

    /**
     * Popup action
     * Shows tracking info if it's present, otherwise redirects to 404
     *
     * @todo add extra check for magento version > 2.2.1 and update view to show url in original design
     *
     * @return void
     * @throws NotFoundException
     */
    public function execute() {
        $shippingInfoModel = $this->_shippingInfoFactory->create()->loadByHash($this->getRequest()->getParam('hash'));
        $this->_coreRegistry->register('current_shipping_info', $shippingInfoModel);
        if(count($shippingInfoModel->getTrackingInfo()) == 0) {
            throw new NotFoundException(__('Page not found.'));
        }

        foreach($shippingInfoModel->getTrackingInfo() as $orderId => $tracks) {
            echo('<h1 style="font-family: Arial, Verdana, sans-serif; font-weight: normal;">Shipment #' . $orderId . '</h1>');
            foreach ($tracks as $track) {
                if ( strstr($track['number'], 'http') ) {
                    echo('<p style="font-family: Arial, Verdana, sans-serif; font-weight: normal;">' . $track['title'] . ' : ' . '<a href="' . $track['number'] . '">' . $track['number'] . '</a></p>');
                } else {
                    echo('<p style="font-family: Arial, Verdana, sans-serif; font-weight: normal;">' . $track['title'] . ' : ' . $track['number'] . '</p>');
                }
            }
        }
        
        exit();
    }
}