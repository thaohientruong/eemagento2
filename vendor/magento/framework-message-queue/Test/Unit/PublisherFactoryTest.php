<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\MessageQueue\Test\Unit;

use Magento\Framework\MessageQueue\Config\Data as QueueConfig;
use Magento\Framework\MessageQueue\Config\Converter as QueueConfigConverter;
use Magento\Framework\MessageQueue\PublisherFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class PublisherFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PublisherFactory
     */
    private $publisherFactory;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var QueueConfig
     */
    private $queueConfigMock;

    const TEST_TOPIC = "test_topic";
    const TEST_PUBLISHER = "test_publisher";
    const TEST_PUBLISHER_CONNECTION = "test_publisher_connection";

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->queueConfigMock = $this->getMockBuilder('Magento\Framework\MessageQueue\Config\Data')
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();
        $this->publisherFactory = $this->objectManager->getObject(
            'Magento\Framework\MessageQueue\PublisherFactory',
            [
                'queueConfig' => $this->queueConfigMock,
            ]
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Specified topic "test_topic" is not declared.
     */
    public function testUndeclaredTopic()
    {
        $this->queueConfigMock->expects($this->once())
            ->method('get')
            ->will($this->returnValue([
                QueueConfigConverter::TOPICS => []
            ]));
        $this->publisherFactory->create(self::TEST_TOPIC);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Specified publisher "test_publisher" is not declared.
     */
    public function testUndeclaredPublisher()
    {
        $this->queueConfigMock->expects($this->any())
            ->method('get')
            ->will($this->returnValue([
                QueueConfigConverter::TOPICS => [
                    self::TEST_TOPIC => [
                        QueueConfigConverter::TOPIC_PUBLISHER => self::TEST_PUBLISHER
                    ]
                ],
                QueueConfigConverter::PUBLISHERS => []
            ]));
        $this->publisherFactory->create(self::TEST_TOPIC);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Could not find an implementation type for connection "test_publisher_connection".
     */
    public function testPublisherNotInjectedIntoClass()
    {
        $this->queueConfigMock->expects($this->any())
            ->method('get')
            ->will($this->returnValue([
                QueueConfigConverter::TOPICS => [
                    self::TEST_TOPIC => [
                        QueueConfigConverter::TOPIC_PUBLISHER => self::TEST_PUBLISHER
                    ]
                ],
                QueueConfigConverter::PUBLISHERS => [
                    self::TEST_PUBLISHER => [
                        QueueConfigConverter::PUBLISHER_CONNECTION => self::TEST_PUBLISHER_CONNECTION
                    ]
                ]
            ]));
        $this->publisherFactory->create(self::TEST_TOPIC);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Could not find an implementation type for connection "test_publisher_connection".
     */
    public function testNoPublishersForConnection()
    {
        $this->queueConfigMock->expects($this->any())
            ->method('get')
            ->will($this->returnValue([
                QueueConfigConverter::TOPICS => [
                    self::TEST_TOPIC => [
                        QueueConfigConverter::TOPIC_PUBLISHER => self::TEST_PUBLISHER
                    ]
                ],
                QueueConfigConverter::PUBLISHERS => [
                    self::TEST_PUBLISHER => [
                        QueueConfigConverter::PUBLISHER_CONNECTION => self::TEST_PUBLISHER_CONNECTION
                    ]
                ]
            ]));

        $publisherMock = $this->getMockBuilder('Magento\Framework\MessageQueue\PublisherInterface')
            ->getMockForAbstractClass();

        $this->publisherFactory = $this->objectManager->getObject(
            'Magento\Framework\MessageQueue\PublisherFactory',
            [
                'queueConfig' => $this->queueConfigMock,
                'publishers' => [
                    [
                        'type' => $publisherMock,
                        'connectionName' => 'randomPublisherConnection',
                    ]
                ]
            ]
        );

        $this->publisherFactory->create(self::TEST_TOPIC);
    }

    public function testPublisherReturned()
    {
        $this->queueConfigMock->expects($this->any())
            ->method('get')
            ->will($this->returnValue([
                QueueConfigConverter::TOPICS => [
                    self::TEST_TOPIC => [
                        QueueConfigConverter::TOPIC_PUBLISHER => self::TEST_PUBLISHER
                    ]
                ],
                QueueConfigConverter::PUBLISHERS => [
                    self::TEST_PUBLISHER => [
                        QueueConfigConverter::PUBLISHER_CONNECTION => self::TEST_PUBLISHER_CONNECTION
                    ]
                ]
            ]));


        $publisherMock = $this->getMockBuilder('Magento\Framework\MessageQueue\PublisherInterface')
            ->getMockForAbstractClass();

        $this->publisherFactory = $this->objectManager->getObject(
            'Magento\Framework\MessageQueue\PublisherFactory',
            [
                'queueConfig' => $this->queueConfigMock,
                'publishers' => [
                    [
                        'type' => $publisherMock,
                        'connectionName' => self::TEST_PUBLISHER_CONNECTION,
                    ]
                ]
            ]
        );

        $this->assertSame($publisherMock, $this->publisherFactory->create(self::TEST_TOPIC));
    }
}
