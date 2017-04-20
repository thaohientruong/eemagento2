<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue;

/**
 * Client class which will publish any message
 */
class PublisherProxy implements PublisherInterface
{
    /**
     * @var PublisherFactory
     */
    private $publisherFactory;

    /**
     * @var MessageEncoder
     */
    private $messageEncoder;

    /**
     * Initialize dependencies.
     *
     * @param PublisherFactory $publisherFactory
     * @param MessageEncoder $messageEncoder
     */
    public function __construct(
        PublisherFactory $publisherFactory,
        MessageEncoder $messageEncoder
    ) {
        $this->publisherFactory = $publisherFactory;
        $this->messageEncoder = $messageEncoder;
    }

    /**
     * Publishes a message on a topic.
     *
     * @param string $topicName
     * @param array|object $data
     * @return void
     */
    public function publish($topicName, $data)
    {
        $publisher = $this->publisherFactory->create($topicName);
        $message = $this->messageEncoder->encode($topicName, $data);
        $publisher->publish($topicName, $message);
    }
}
