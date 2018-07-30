<?php
/**
 * Copyright (c) 2017 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */
namespace Paazl\Shipping\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListOrdersCommand extends Command
{
    /**
     * @var \Paazl\Shipping\Model\PaazlManagement
     */
    protected $_paazlManagement;

    /**
     * ListOrdersCommand constructor.
     * @param \Paazl\Shipping\Model\PaazlManagement $_paazlManagement
     * @param null $name
     */
    public function __construct(
        \Paazl\Shipping\Model\PaazlManagement $_paazlManagement,
        $name = null
    ) {
        $this->_paazlManagement = $_paazlManagement;
        parent::__construct($name);
    }

    /**
     * Configures the current command.
     */
    public function configure()
    {
        $this->setName('paazl:order:list');
        $this->setDescription(__('Show orders'));
    }

    /**
     * @param InputInterface $input An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     * @return null|int null or 0 if everything went fine, or an error code
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>ListOrdersCommand</info>');

        $date = new \DateTime();
        $orders = $this->listOrders($date);

        // In case of 1 order, ['orders']['order'] is the first result (object conversion)
        if (!isset($orders[0])) $orders = [$orders];

        foreach ($orders as $order) {
            $data = print_r($order, true);
            $output->writeln('<info>' . $data . '</info>');
        }

        $result = (int)(count($orders));
        return $result;
    }

    /**
     * @param \DateTime $dateTime
     * @return mixed
     */
    protected function listOrders(\DateTime $dateTime)
    {
        $response = $this->_paazlManagement->processListOrdersRequest($dateTime);

        $orders = [];
        if (isset($response['orders']['order'])) $orders = $response['orders']['order'];

        return $orders;
    }
}
