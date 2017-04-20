<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Amqp\Model;

use Magento\Framework\MessageQueue\Config\Data as QueueConfig;
use Magento\Framework\MessageQueue\Config\Converter as QueueConfigConverter;

/**
 * Class Topology creates topology for Amqp messaging
 *
 * @package Magento\Amqp\Model
 */
class Topology
{
    /**
     * Type of exchange
     */
    const TOPIC_EXCHANGE = 'topic';

    /**
     * Amqp connection
     */
    const AMQP_CONNECTION = 'amqp';

    /**
     * Durability for exchange and queue
     */
    const IS_DURABLE = true;

    /**
     * @var Config
     */
    private $amqpConfig;

    /**
     * @var QueueConfig
     */
    private $queueConfig;

    /**
     * @var array
     */
    private $queueConfigData;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Initialize dependencies
     *
     * @param Config $amqpConfig
     * @param QueueConfig $queueConfig
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        Config $amqpConfig,
        QueueConfig $queueConfig,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->amqpConfig = $amqpConfig;
        $this->queueConfig = $queueConfig;
        $this->logger = $logger;
    }

    /**
     * Install Amqp Exchanges, Queues and bind them
     *
     * @return void
     */
    public function install()
    {
        $queueConfig = $this->getQueueConfigData();
        if (isset($queueConfig[QueueConfigConverter::BINDS])) {
            $availableQueues = $this->getQueuesList(self::AMQP_CONNECTION);
            $availableExchanges = $this->getExchangesList(self::AMQP_CONNECTION);

            foreach ($queueConfig[QueueConfigConverter::BINDS] as $bind) {
                $queueName = $bind[QueueConfigConverter::BIND_QUEUE];
                $exchangeName = $bind[QueueConfigConverter::BIND_EXCHANGE];
                $topicName = $bind[QueueConfigConverter::BIND_TOPIC];
                if (in_array($queueName, $availableQueues) && in_array($exchangeName, $availableExchanges)) {
                    try {
                        $this->declareQueue($queueName);
                        $this->declareExchange($exchangeName);
                        $this->bindQueue($queueName, $exchangeName, $topicName);
                    } catch (\PhpAmqpLib\Exception\AMQPExceptionInterface $e) {
                        $this->logger->error(
                            sprintf(
                                'There is a problem with creating or binding queue "%s" and an exchange "%s". Error:',
                                $queueName,
                                $exchangeName,
                                $e->getTraceAsString()
                            )
                        );
                    }
                }
            }
        }
    }

    /**
     * Declare Amqp Queue
     *
     * @param string $queueName
     * @return void
     */
    private function declareQueue($queueName)
    {
        $this->getChannel()->queue_declare($queueName, false, self::IS_DURABLE, false, false);
    }

    /**
     * Declare Amqp Exchange
     *
     * @param string $exchangeName
     * @return void
     */
    private function declareExchange($exchangeName)
    {
        $this->getChannel()->exchange_declare($exchangeName, self::TOPIC_EXCHANGE, false, self::IS_DURABLE, false);
    }

    /**
     * Bind queue and exchange
     *
     * @param string $queueName
     * @param string $exchangeName
     * @param string $topicName
     * @return void
     */
    private function bindQueue($queueName, $exchangeName, $topicName)
    {
        $this->getChannel()->queue_bind($queueName, $exchangeName, $topicName);
    }

    /**
     * Return Amqp channel
     *
     * @return \PhpAmqpLib\Channel\AMQPChannel
     */
    private function getChannel()
    {
        return $this->amqpConfig->getChannel();
    }

    /**
     * Return list of queue names, that are available for connection
     *
     * @param string $connection
     * @return array List of queue names
     */
    private function getQueuesList($connection)
    {
        $queues = [];
        $queueConfig = $this->getQueueConfigData();
        if (isset($queueConfig[QueueConfigConverter::CONSUMERS])) {
            foreach ($queueConfig[QueueConfigConverter::CONSUMERS] as $consumer) {
                if ($consumer[QueueConfigConverter::CONSUMER_CONNECTION] === $connection) {
                    $queues[] = $consumer[QueueConfigConverter::CONSUMER_QUEUE];
                }
            }
            $queues = array_unique($queues);
        }
        return $queues;
    }

    /**
     * Return list of exchange names, that are available for connection
     *
     * @param string $connection
     * @return array List of exchange names
     */
    private function getExchangesList($connection)
    {
        $exchanges = [];
        $queueConfig = $this->getQueueConfigData();
        if (isset($queueConfig[QueueConfigConverter::PUBLISHERS])) {
            foreach ($queueConfig[QueueConfigConverter::PUBLISHERS] as $consumer) {
                if ($consumer[QueueConfigConverter::PUBLISHER_CONNECTION] === $connection) {
                    $exchanges[] = $consumer[QueueConfigConverter::PUBLISHER_EXCHANGE];
                }
            }
            $exchanges = array_unique($exchanges);
        }
        return $exchanges;
    }

    /**
     * Returns the queue configuration.
     *
     * @return array
     */
    private function getQueueConfigData()
    {
        if ($this->queueConfigData == null) {
            $this->queueConfigData = $this->queueConfig->get();
        }
        return $this->queueConfigData;
    }
}
