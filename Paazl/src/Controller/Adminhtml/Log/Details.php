<?php
/**
 * Copyright (c) 2017 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */
namespace Paazl\Shipping\Controller\Adminhtml\Log;

use Paazl\Shipping\Controller\Adminhtml\Log;
use Paazl\Shipping\Helper\Log as LogHelper;
use Paazl\Shipping\Model\LogRepository;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Registry;
use Magento\Framework\Translate\InlineInterface;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Webapi\Exception;

/**
 * Class Index
 * @package Paazl\Shipping\Controller\Adminhtml\Log
 */
class Details extends Log
{
    /** @var LogRepository $logRepository */
    protected $logRepository;

    /**
     * Details constructor.
     * @param Context $context
     * @param Registry $coreRegistry
     * @param FileFactory $fileFactory
     * @param InlineInterface $translateInline
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     * @param LayoutFactory $resultLayoutFactory
     * @param RawFactory $resultRawFactory
     * @param LogRepository $logRepository
     * @param LogHelper $log
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        FileFactory $fileFactory,
        InlineInterface $translateInline,
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory,
        LayoutFactory $resultLayoutFactory,
        RawFactory $resultRawFactory,
        LogRepository $logRepository,
        LogHelper $log
    ) {
        $this->logRepository = $logRepository;
        parent::__construct(
            $context,
            $coreRegistry,
            $fileFactory,
            $translateInline,
            $resultPageFactory,
            $resultJsonFactory,
            $resultLayoutFactory,
            $resultRawFactory,
            $log
        );
    }

    /**
     * @return $this
     */
    public function execute()
    {
        $resultPage = $this->_initAction();
        $resultPage->getConfig()->getTitle()->prepend(__('Paazl Log Details'));

        $logId = $this->getRequest()->getParam('log_id');

        try {
            $log = $this->logRepository->getById($logId);
            $this->_coreRegistry->register('current_log', $log);
        } catch(Exception $ex) {

        }

        return $resultPage;
    }
}
