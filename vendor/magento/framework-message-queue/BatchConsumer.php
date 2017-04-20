<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue;

use Magento\Framework\MessageQueue\Config\Data as MessageQueueConfig;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\MessageQueue\ConnectionLostException;

/**
 * Class BatchConsumer
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BatchConsumer implements ConsumerInterface
{
    /**
     * @var ConsumerConfigurationInterface
     */
    private $configuration;

    /**
     * @var MessageQueueConfig
     */
    private $messageQueueConfig;

    /**
     * @var MessageEncoder
     */
    private $messageEncoder;

    /**
     * @var QueueRepository
     */
    private $queueRepository;

    /**
     * @var MergerFactory
     */
    private $mergerFactory;

    /**
     * @var int
     */
    private $interval;

    /**
     * @var Resource
     */
    private $resource;

    /**
     * @param MessageQueueConfig $messageQueueConfig
     * @param MessageEncoder $messageEncoder
     * @param QueueRepository $queueRepository
     * @param MergerFactory $mergerFactory
     * @param ResourceConnection $resource
     * @param int $interval
     */
    public function __construct(
        MessageQueueConfig $messageQueueConfig,
        MessageEncoder $messageEncoder,
        QueueRepository $queueRepository,
        MergerFactory $mergerFactory,
        ResourceConnection $resource,
        $interval = 5
    ) {
        $this->messageQueueConfig = $messageQueueConfig;
        $this->messageEncoder = $messageEncoder;
        $this->queueRepository = $queueRepository;
        $this->mergerFactory = $mergerFactory;
        $this->interval = $interval;
        $this->resource = $resource;
    }

    /**
     * {@inheritdoc}
     */
    public function configure(ConsumerConfigurationInterface $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function process($maxNumberOfMessages = null)
    {
        $queueName = $this->configuration->getQueueName();
        $consumerName = $this->configuration->getConsumerName();
        $connectionName = $this->messageQueueConfig->getConnectionByConsumer($consumerName);

        $queue = $this->queueRepository->get($connectionName, $queueName);
        $merger = $this->mergerFactory->create($consumerName);

        if (!isset($maxNumberOfMessages)) {
            $this->runDaemonMode($queue, $merger);
        } else {
            $this->run($queue, $merger, $maxNumberOfMessages);
        }
    }

    /**
     * Decode message and invoke callback method
     *
     * @param object[] $messages
     * @return void
     * @throws LocalizedException
     */
    private function dispatchMessage(array $messages)
    {
        $callback = $this->configuration->getCallback();
        foreach ($messages as $message) {
            call_user_func($callback, $message);
        }
    }

    /**
     * Run process in the daemon mode
     *
     * @param QueueInterface $queue
     * @param MergerInterface $merger
     * @return void
     */
    private function runDaemonMode(QueueInterface $queue, MergerInterface $merger)
    {
        $transactionCallback = $this->getTransactionCallback($queue, $merger);
        while (true) {
            $messages = $this->getAllMessages($queue);
            $transactionCallback($messages);
            sleep($this->interval);
        }
    }

    /**
     * Run short running process
     *
     * @param QueueInterface $queue
     * @param MergerInterface $merger
     * @param int $maxNumberOfMessages
     * @return void
     */
    private function run(QueueInterface $queue, MergerInterface $merger, $maxNumberOfMessages)
    {
        $count = $maxNumberOfMessages
            ? $maxNumberOfMessages
            : $this->configuration->getMaxMessages() ?: 1;

        $messages = $this->getMessages($queue, $count);
        $transactionCallback = $this->getTransactionCallback($queue, $merger);
        $transactionCallback($messages);
    }

    /**
     * @param QueueInterface $queue
     * @param EnvelopeInterface[] $messages
     * @return void
     */
    private function acknowledgeAll(QueueInterface $queue, array $messages)
    {
        foreach ($messages as $message) {
            $queue->acknowledge($message);
        }
    }

    /**
     * @param QueueInterface $queue
     * @return EnvelopeInterface[]
     */
    private function getAllMessages(QueueInterface $queue)
    {
        $messages = [];
        while ($message = $queue->dequeue()) {
            $messages[] = $message;
        }

        return $messages;
    }

    /**
     * @param QueueInterface $queue
     * @param int $count
     * @return EnvelopeInterface[]
     */
    private function getMessages(QueueInterface $queue, $count)
    {
        $messages = [];
        for ($i = $count; $i > 0; $i--) {
            $message = $queue->dequeue();
            if ($message === null) {
                break;
            }
            $messages[] = $message;
        }

        return $messages;
    }

    /**
     * @param QueueInterface $queue
     * @param EnvelopeInterface[] $messages
     * @return void
     */
    private function rejectAll(QueueInterface $queue, array $messages)
    {
        foreach ($messages as $message) {
            $queue->reject($message);
        }
    }


    /**
     * @param EnvelopeInterface[] $messages
     * @return object[]
     */
    private function decodeMessages(array $messages)
    {
        $decodedMessages = [];
        foreach ($messages as $message) {
            $properties = $message->getProperties();
            $topicName = $properties['topic_name'];

            $decodedMessages[] = $this->messageEncoder->decode($topicName, $message->getBody());
        }

        return $decodedMessages;
    }

    /**
     * @param QueueInterface $queue
     * @param MergerInterface $merger
     * @return \Closure
     */
    private function getTransactionCallback(QueueInterface $queue, MergerInterface $merger)
    {
        return function (array $messages) use ($queue, $merger) {
            try {
                $this->resource->getConnection()->beginTransaction();
                $decodedMessages = $this->decodeMessages($messages);
                $mergedMessages = $merger->merge($decodedMessages);
                $this->dispatchMessage($mergedMessages);
                $this->acknowledgeAll($queue, $messages);
                $this->resource->getConnection()->commit();
            } catch (ConnectionLostException $e) {
                $this->resource->getConnection()->rollBack();
            } catch (\Exception $e) {
                $this->resource->getConnection()->rollBack();
                $this->rejectAll($queue, $messages);
            }
        };
    }
}
