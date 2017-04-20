<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MysqlMq\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\MessageQueue\Config\Data as MessageQueueConfig;
use Magento\Framework\MessageQueue\Config\Converter as MessageQueueConfigConverter;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var MessageQueueConfig
     */
    private $messageQueueConfig;

    /**
     * Initialize dependencies.
     *
     * @param MessageQueueConfig $messageQueueConfig
     */
    public function __construct(MessageQueueConfig $messageQueueConfig)
    {
        $this->messageQueueConfig = $messageQueueConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $binds = $this->messageQueueConfig->get()[MessageQueueConfigConverter::BINDS];
        $queues = [];
        foreach ($binds as $bind) {
            $queues[] = $bind[MessageQueueConfigConverter::BIND_QUEUE];
        }
        $queues = array_unique($queues);
        /** Populate 'queue' table */
        foreach ($queues as $queueName) {
            $setup->getConnection()->insert($setup->getTable('queue'), ['name' => $queueName]);
        }

        $setup->endSetup();
    }
}
