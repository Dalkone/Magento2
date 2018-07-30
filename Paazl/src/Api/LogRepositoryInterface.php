<?php
/**
 * Copyright (c) 2017 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */
namespace Paazl\Shipping\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Paazl\Shipping\Api\Data\LogInterface;

/**
 * @api
 * Interface QueueRepositoryInterface
 * @package Paazl\Shipping\Api
 */
interface LogRepositoryInterface
{
    /**
     * @param LogInterface $log
     * @return mixed
     */
    public function save(LogInterface $log);

    /**
     * @param $logId
     * @return LogInterface
     * @throws NoSuchEntityException
     */
    public function getById($logId);

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return mixed
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * @param $logId
     * @return mixed
     */
    public function delete($logId);
}
