<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\MessageQueue\Config;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

/**
 * Class for access to MessageQueue configuration.
 */
class Data extends \Magento\Framework\Config\Data
{
    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\MessageQueue\Config\Reader $reader
     * @param \Magento\Framework\Config\CacheInterface $cache
     * @param string $cacheId
     */
    public function __construct(
        \Magento\Framework\MessageQueue\Config\Reader $reader,
        \Magento\Framework\Config\CacheInterface $cache,
        $cacheId = 'message_queue_config_cache'
    ) {
        parent::__construct($reader, $cache, $cacheId);
    }

    /**
     * Identify configured exchange for the provided topic.
     *
     * @param string $topicName
     * @return string
     * @throws LocalizedException
     */
    public function getExchangeByTopic($topicName)
    {
        if (isset($this->_data[Converter::TOPICS][$topicName])) {
            $publisherName = $this->_data[Converter::TOPICS][$topicName][Converter::TOPIC_PUBLISHER];
            if (isset($this->_data[Converter::PUBLISHERS][$publisherName])) {
                return $this->_data[Converter::PUBLISHERS][$publisherName][Converter::PUBLISHER_EXCHANGE];
            } else {
                throw new LocalizedException(
                    new Phrase(
                        'Message queue publisher "%publisher" is not configured.',
                        ['publisher' => $publisherName]
                    )
                );
            }
        } else {
            throw new LocalizedException(
                new Phrase('Message queue topic "%topic" is not configured.', ['topic' => $topicName])
            );
        }
    }

    /**
     * Identify a list of all queue names corresponding to the specified topic (and implicitly exchange).
     *
     * @param string $topic
     * @return string[]
     * @throws LocalizedException
     */
    public function getQueuesByTopic($topic)
    {
        $exchange = $this->getExchangeByTopic($topic);
        /**
         * Exchange should be taken into account here to avoid retrieving queues, related to another exchange,
         * which is not currently associated with topic, but is configured in binds
         */
        $bindKey = $exchange . '--' . $topic;
        if (isset($this->_data[Converter::EXCHANGE_TOPIC_TO_QUEUES_MAP][$bindKey])) {
            return $this->_data[Converter::EXCHANGE_TOPIC_TO_QUEUES_MAP][$bindKey];
        } else {
            throw new LocalizedException(
                new Phrase(
                    'No bindings configured for the "%topic" topic at "%exchange" exchange.',
                    ['topic' => $topic, 'exchange' => $exchange]
                )
            );
        }
    }

    /**
     * @param string $topic
     * @return string
     * @throws LocalizedException
     */
    public function getConnectionByTopic($topic)
    {
        if (isset($this->_data[Converter::TOPICS][$topic])) {
            $publisherName = $this->_data[Converter::TOPICS][$topic][Converter::TOPIC_PUBLISHER];
            if (isset($this->_data[Converter::PUBLISHERS][$publisherName])) {
                return $this->_data[Converter::PUBLISHERS][$publisherName][Converter::PUBLISHER_CONNECTION];
            } else {
                throw new LocalizedException(
                    new Phrase(
                        'Message queue publisher "%publisher" is not configured.',
                        ['publisher' => $publisherName]
                    )
                );
            }
        } else {
            throw new LocalizedException(
                new Phrase('Message queue topic "%topic" is not configured.', ['topic' => $topic])
            );
        }
    }

    /**
     * @param string $consumer
     * @return string
     * @throws LocalizedException
     */
    public function getConnectionByConsumer($consumer)
    {
        if (!isset($this->_data[Converter::CONSUMERS][$consumer][Converter::CONSUMER_CONNECTION])) {
            throw new LocalizedException(
                new Phrase('Consumer "%consumer" has not connection.', ['consumer' => $consumer])
            );
        }

        return $this->_data[Converter::CONSUMERS][$consumer][Converter::CONSUMER_CONNECTION];
    }

    /**
     * Identify which option is used to define message schema: data interface or service method params
     *
     * @param string $topic
     * @return string
     */
    public function getMessageSchemaType($topic)
    {
        return $this->_data[Converter::TOPICS][$topic][COnverter::TOPIC_SCHEMA][Converter::TOPIC_SCHEMA_TYPE];
    }
}
