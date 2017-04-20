<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Config;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\Reflection\MethodsMap;

/**
 * Converts MessageQueue config from \DOMDocument to array
 */
class Converter implements \Magento\Framework\Config\ConverterInterface
{
    const PUBLISHERS = 'publishers';
    const PUBLISHER_NAME = 'name';
    const PUBLISHER_CONNECTION = 'connection';
    const PUBLISHER_EXCHANGE = 'exchange';

    const TOPICS = 'topics';
    const TOPIC_NAME = 'name';
    const TOPIC_PUBLISHER = 'publisher';
    const TOPIC_SCHEMA = 'schema';
    const TOPIC_SCHEMA_TYPE = 'schema_type';
    const TOPIC_SCHEMA_VALUE = 'schema_value';
    const TOPIC_SCHEMA_METHOD_NAME = 'schema_method';

    const TOPIC_SCHEMA_TYPE_OBJECT = 'object';
    const TOPIC_SCHEMA_TYPE_METHOD = 'method_arguments';

    const SCHEMA_METHOD_PARAM_NAME = 'param_name';
    const SCHEMA_METHOD_PARAM_POSITION = 'param_position';
    const SCHEMA_METHOD_PARAM_TYPE = 'param_type';
    const SCHEMA_METHOD_PARAM_IS_REQUIRED = 'is_required';

    const CONSUMERS = 'consumers';
    const CONSUMER_NAME = 'name';
    const CONSUMER_QUEUE = 'queue';
    const CONSUMER_CONNECTION = 'connection';
    const CONSUMER_EXECUTOR = 'executor';
    const CONSUMER_CLASS = 'class';
    const CONSUMER_METHOD = 'method';
    const CONSUMER_MAX_MESSAGES = 'max_messages';

    const BINDS = 'binds';
    const BIND_QUEUE = 'queue';
    const BIND_EXCHANGE = 'exchange';
    const BIND_TOPIC = 'topic';

    /**
     * Map which allows optimized search of queues corresponding to the specified exchange and topic pair.
     */
    const EXCHANGE_TOPIC_TO_QUEUES_MAP = 'exchange_topic_to_queues_map';

    const ENV_QUEUE = 'queue';
    const ENV_TOPICS = 'topics';
    const ENV_CONSUMERS = 'consumers';
    const ENV_CONSUMER_CONNECTION = 'connection';
    const ENV_CONSUMER_MAX_MESSAGES = 'max_messages';

    const SERVICE_METHOD_NAME_PATTERN = '/^([a-zA-Z\\\\]+)::([a-zA-Z]+)$/';

    /**
     * @var \Magento\Framework\App\DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var array
     */
    private $queueConfig;

    /**
     * @var MethodsMap
     */
    private $methodsMap;

    /**
     * Initialize dependencies
     *
     * @param \Magento\Framework\App\DeploymentConfig $deploymentConfig
     * @param MethodsMap $methodsMap
     */
    public function __construct(
        \Magento\Framework\App\DeploymentConfig $deploymentConfig,
        MethodsMap $methodsMap
    ) {
        $this->deploymentConfig = $deploymentConfig;
        $this->methodsMap = $methodsMap;
    }

    /**
     * Convert dom node tree to array
     *
     * @param \DOMDocument $source
     * @return array
     */
    public function convert($source)
    {
        $publishers = $this->extractPublishers($source);
        $topics = $this->extractTopics($source);
        $this->overridePublishersForTopics($topics, $publishers);
        $consumers = $this->extractConsumers($source);
        $this->overrideConsumersData($consumers);
        $binds = $this->extractBinds($source);
        return [
            self::PUBLISHERS => $publishers,
            self::TOPICS => $topics,
            self::CONSUMERS => $consumers,
            self::BINDS => $binds,
            self::EXCHANGE_TOPIC_TO_QUEUES_MAP => $this->buildExchangeTopicToQueuesMap($binds, $topics)
        ];
    }

    /**
     * Extract topics configuration.
     *
     * @param \DOMDocument $config
     * @return array
     */
    protected function extractTopics($config)
    {
        $output = [];
        /** @var $topicNode \DOMNode */
        foreach ($config->getElementsByTagName('topic') as $topicNode) {
            $topicName = $topicNode->attributes->getNamedItem('name')->nodeValue;
            $schemaId = $topicNode->attributes->getNamedItem('schema')->nodeValue;
            $schemaType = $this->identifySchemaType($schemaId);
            $schemaValue = ($schemaType == self::TOPIC_SCHEMA_TYPE_METHOD)
                ? $this->getSchemaDefinedByMethod($schemaId, $topicName)
                : $schemaId;
            $output[$topicName] = [
                self::TOPIC_NAME => $topicName,
                self::TOPIC_SCHEMA => [
                    self::TOPIC_SCHEMA_TYPE => $schemaType,
                    self::TOPIC_SCHEMA_VALUE => $schemaValue
                ],
                self::TOPIC_PUBLISHER => $topicNode->attributes->getNamedItem('publisher')->nodeValue
            ];
            if ($schemaType == self::TOPIC_SCHEMA_TYPE_METHOD) {
                $output[$topicName][self::TOPIC_SCHEMA][self::TOPIC_SCHEMA_METHOD_NAME] = $schemaId;
            }
        }
        return $output;
    }

    /**
     * Get message schema defined by service method signature.
     *
     * @param string $schemaId
     * @param string $topic
     * @return array
     */
    protected function getSchemaDefinedByMethod($schemaId, $topic)
    {
        if (!preg_match(self::SERVICE_METHOD_NAME_PATTERN, $schemaId, $matches)) {
            throw new \LogicException(
                sprintf(
                    'Message schema definition for topic "%s" should reference existing service method. Given "%s"',
                    $topic,
                    $schemaId
                )
            );
        }
        $serviceClass = $matches[1];
        $serviceMethod = $matches[2];
        $result = [];
        $paramsMeta = $this->methodsMap->getMethodParams($serviceClass, $serviceMethod);
        foreach ($paramsMeta as $paramPosition => $paramMeta) {
            $result[] = [
                self::SCHEMA_METHOD_PARAM_NAME => $paramMeta[MethodsMap::METHOD_META_NAME],
                self::SCHEMA_METHOD_PARAM_POSITION => $paramPosition,
                self::SCHEMA_METHOD_PARAM_IS_REQUIRED => !$paramMeta[MethodsMap::METHOD_META_HAS_DEFAULT_VALUE],
                self::SCHEMA_METHOD_PARAM_TYPE => $paramMeta[MethodsMap::METHOD_META_TYPE],
            ];
        }
        return $result;
    }

    /**
     * Identify which option is used to define message schema: data interface or service method params
     *
     * @param string $schemaId
     * @return string
     */
    protected function identifySchemaType($schemaId)
    {
        return preg_match(self::SERVICE_METHOD_NAME_PATTERN, $schemaId)
            ? self::TOPIC_SCHEMA_TYPE_METHOD
            : self::TOPIC_SCHEMA_TYPE_OBJECT;
    }

    /**
     * Extract publishers configuration.
     *
     * @param \DOMDocument $config
     * @return array
     */
    protected function extractPublishers($config)
    {
        $output = [];
        /** @var $publisherNode \DOMNode */
        foreach ($config->getElementsByTagName('publisher') as $publisherNode) {
            $publisherName = $publisherNode->attributes->getNamedItem('name')->nodeValue;
            $output[$publisherName] = [
                self::PUBLISHER_NAME => $publisherName,
                self::PUBLISHER_CONNECTION => $publisherNode->attributes->getNamedItem('connection')->nodeValue,
                self::PUBLISHER_EXCHANGE => $publisherNode->attributes->getNamedItem('exchange')->nodeValue
            ];
        }
        return $output;
    }

    /**
     * Extract consumers configuration.
     *
     * @param \DOMDocument $config
     * @return array
     */
    protected function extractConsumers($config)
    {
        $output = [];
        /** @var $consumerNode \DOMNode */
        foreach ($config->getElementsByTagName('consumer') as $consumerNode) {
            $consumerName = $consumerNode->attributes->getNamedItem('name')->nodeValue;
            $maxMessages = $consumerNode->attributes->getNamedItem('max_messages');
            $connections = $consumerNode->attributes->getNamedItem('connection');
            $executor = $consumerNode->attributes->getNamedItem('executor');
            $output[$consumerName] = [
                self::CONSUMER_NAME => $consumerName,
                self::CONSUMER_QUEUE => $consumerNode->attributes->getNamedItem('queue')->nodeValue,
                self::CONSUMER_CONNECTION => $connections ? $connections->nodeValue : null,
                self::CONSUMER_CLASS => $consumerNode->attributes->getNamedItem('class')->nodeValue,
                self::CONSUMER_METHOD => $consumerNode->attributes->getNamedItem('method')->nodeValue,
                self::CONSUMER_MAX_MESSAGES => $maxMessages ? $maxMessages->nodeValue : null,
                self::CONSUMER_EXECUTOR => $executor ? $executor->nodeValue : null,
            ];
        }
        return $output;
    }

    /**
     * Extract binds configuration.
     *
     * @param \DOMDocument $config
     * @return array
     */
    protected function extractBinds($config)
    {
        $output = [];
        /** @var $bindNode \DOMNode */
        foreach ($config->getElementsByTagName('bind') as $bindNode) {
            $output[] = [
                self::BIND_QUEUE => $bindNode->attributes->getNamedItem('queue')->nodeValue,
                self::BIND_EXCHANGE => $bindNode->attributes->getNamedItem('exchange')->nodeValue,
                self::BIND_TOPIC => $bindNode->attributes->getNamedItem('topic')->nodeValue,
            ];
        }
        return $output;
    }

    /**
     * Build map which allows optimized search of queues corresponding to the specified exchange and topic pair.
     *
     * @param array $binds
     * @param array $topics
     * @return array
     */
    protected function buildExchangeTopicToQueuesMap($binds, $topics)
    {
        $output = [];
        $wildcardKeys = [];
        foreach ($binds as $bind) {
            $key = $bind[self::BIND_EXCHANGE] . '--' . $bind[self::BIND_TOPIC];
            if (strpos($key, '*') !== false || strpos($key, '#') !== false) {
                $wildcardKeys[] = $key;
            }
            $output[$key][] = $bind[self::BIND_QUEUE];
        }

        foreach (array_unique($wildcardKeys) as $wildcardKey) {
            $keySplit = explode('--', $wildcardKey);
            $exchangePrefix = $keySplit[0];
            $key = $keySplit[1];
            $pattern = $this->buildWildcardPattern($key);
            foreach (array_keys($topics) as $topic) {
                if (preg_match($pattern, $topic)) {
                    $fullTopic = $exchangePrefix . '--' . $topic;
                    if (isset($output[$fullTopic])) {
                        $output[$fullTopic] = array_merge($output[$fullTopic], $output[$wildcardKey]);
                    } else {
                        $output[$fullTopic] = $output[$wildcardKey];
                    }
                }
            }
            unset($output[$wildcardKey]);
        }
        return $output;
    }

    /**
     * Construct perl regexp pattern for matching topic names from wildcard key.
     *
     * @param string $wildcardKey
     * @return string
     */
    protected function buildWildcardPattern($wildcardKey)
    {
        $pattern = '/^' . str_replace('.', '\.', $wildcardKey);
        $pattern = str_replace('#', '.+', $pattern);
        $pattern = str_replace('*', '[^\.]+', $pattern);
        if (strpos($wildcardKey, '#') == strlen($wildcardKey)) {
            $pattern .= '/';
        } else {
            $pattern .= '$/';
        }

        return $pattern;
    }

    /**
     * Override publishers declared for topics in queue.xml using values specified in the etc/env.php
     *
     * Note that $topics argument is modified by reference.
     *
     * Example environment config:
     * <code>
     * 'queue' =>
     *     [
     *         'topics' => [
     *             'some_topic_name' => 'custom_publisher',
     *         ],
     *     ],
     * </code>
     *
     * @param array &$topics
     * @param array $publishers
     * @return void
     * @throws LocalizedException
     */
    protected function overridePublishersForTopics(array &$topics, array $publishers)
    {
        $queueConfig = $this->getQueueConfig();
        if (!isset($queueConfig[self::ENV_TOPICS]) || !is_array($queueConfig[self::ENV_TOPICS])) {
            return;
        }
        foreach ($queueConfig[self::ENV_TOPICS] as $topicName => $publisherName) {
            if (!isset($topics[$topicName])) {
                continue;
            }
            if (isset($publishers[$publisherName])) {
                $topics[$topicName][self::TOPIC_PUBLISHER] = $publisherName;
            } else {
                throw new LocalizedException(
                    new Phrase(
                        'Publisher "%publisher", specified in env.php for topic "%topic" is not declared.',
                        ['publisher' => $publisherName, 'topic' => $topicName]
                    )
                );
            }
        }
    }

    /**
     * Override consumer connections and max messages declared in queue.xml using values specified in the etc/env.php
     *
     * Note that $consumers argument is modified by reference.
     *
     * Example environment config:
     * <code>
     * 'queue' =>
     *     [
     *         'consumers' => [
     *             'customerCreatedListener' => [
     *                  'connection => 'database',
     *                  'max_messages' => '321'
     *              ],
     *         ],
     *     ],
     * </code>
     *
     * @param array &$consumers
     * @return void
     * @throws LocalizedException
     */
    protected function overrideConsumersData(array &$consumers)
    {
        $queueConfig = $this->getQueueConfig();
        if (!isset($queueConfig[self::ENV_CONSUMERS]) || !is_array($queueConfig[self::ENV_CONSUMERS])) {
            return;
        }
        foreach ($queueConfig[self::ENV_CONSUMERS] as $consumerName => $consumerConfig) {
            if (isset($consumers[$consumerName])) {
                if (isset($consumerConfig[self::ENV_CONSUMER_CONNECTION])) {
                    $consumers[$consumerName][self::CONSUMER_CONNECTION]
                        = $consumerConfig[self::ENV_CONSUMER_CONNECTION];
                }
                if (isset($consumerConfig[self::ENV_CONSUMER_MAX_MESSAGES])) {
                    $consumers[$consumerName][self::CONSUMER_MAX_MESSAGES]
                        = $consumerConfig[self::ENV_CONSUMER_MAX_MESSAGES];
                }
            }
        }
    }

    /**
     * Return the queue configuration
     *
     * @return array
     */
    protected function getQueueConfig()
    {
        if ($this->queueConfig == null) {
            $this->queueConfig = $this->deploymentConfig->getConfigData(self::ENV_QUEUE);
        }

        return $this->queueConfig;
    }
}
