<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Rma\Test\Unit\Model\Rma;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class RmaDataMapperTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Rma\Model\Rma\RmaDataMapper */
    protected $rmaDataMapper;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $dateTimeFactoryMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $collectionFactoryMock;

    protected function setUp()
    {
        $this->dateTimeFactoryMock = $this->getMock(
            'Magento\Framework\Stdlib\DateTime\DateTimeFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->collectionFactoryMock = $this->getMock(
            'Magento\Rma\Model\ResourceModel\Item\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->rmaDataMapper = $this->objectManagerHelper->getObject(
            'Magento\Rma\Model\Rma\RmaDataMapper',
            [
                'dateTimeFactory' => $this->dateTimeFactoryMock,
                'itemCollectionFactory' => $this->collectionFactoryMock
            ]
        );
    }

    public function testFilterRmaSaveRequestException()
    {
        $saveRequest = [];

        $this->setExpectedException('Magento\Framework\Exception\LocalizedException');

        $this->rmaDataMapper->filterRmaSaveRequest($saveRequest);
    }

    public function testFilterRmaSaveRequest()
    {
        $items = [
            0 => ['item'],
            1 => ['qty_authorized' => 5],
            'php_id' => ['qty_authorized' => 5],
        ];
        $expectedItems = [
            1 => ['qty_authorized' => 5, 'entity_id' => 1],
            'php_id' => ['qty_authorized' => 5, 'entity_id' => false],
        ];

        $this->assertEquals(
            ['items' => $expectedItems],
            $this->rmaDataMapper->filterRmaSaveRequest(['items' => $items])
        );
    }

    /**
     * @dataProvider saveRequestEmailDataProvider
     * @param array $saveRequest
     * @param string $emailExpectation
     */
    public function testPrepareNewRmaInstanceData($saveRequest, $emailExpectation)
    {
        $dateMock = $this->getMockBuilder('Magento\Framework\Stdlib\DateTime\DateTime')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $orderMock = $this->getMockBuilder('Magento\Sales\Model\Order')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getId',
                    'getIncrementId',
                    'getStoreId',
                    'getCustomerId',
                    'getCreatedAt',
                    'getCustomerName',
                    '__wakeup',
                ]
            )
            ->getMock();
        $expectedRmaData = [
            'status' => \Magento\Rma\Model\Rma\Source\Status::STATE_PENDING,
            'date_requested' => '2038-00-00 00:00:00',
            'order_id' => '1',
            'order_increment_id' => '1000101',
            'store_id' => '7',
            'customer_id' => '5',
            'order_date' => '2037-00-00 00:00:00',
            'customer_name' => 'Brian',
            'customer_custom_email' => $emailExpectation,
        ];

        $this->dateTimeFactoryMock->expects($this->once())->method('create')
            ->will($this->returnValue($dateMock));
        $dateMock->expects($this->once())->method('gmtDate')
            ->will($this->returnValue($expectedRmaData['date_requested']));
        $orderMock->expects($this->once())->method('getId')
            ->will($this->returnValue($expectedRmaData['order_id']));
        $orderMock->expects($this->once())->method('getIncrementId')
            ->will($this->returnValue($expectedRmaData['order_increment_id']));
        $orderMock->expects($this->once())->method('getStoreId')
            ->will($this->returnValue($expectedRmaData['store_id']));
        $orderMock->expects($this->once())->method('getCustomerId')
            ->will($this->returnValue($expectedRmaData['customer_id']));
        $orderMock->expects($this->once())->method('getCreatedAt')
            ->will($this->returnValue($expectedRmaData['order_date']));
        $orderMock->expects($this->once())->method('getCustomerName')
            ->will($this->returnValue($expectedRmaData['customer_name']));

        $this->assertEquals(
            $expectedRmaData,
            $this->rmaDataMapper->prepareNewRmaInstanceData($saveRequest, $orderMock)
        );
    }

    public function testCombineItemStatuses()
    {
        $rmaId = 1;
        $requestedItems = [
            0 => [],
            1 => ['status' => 'awful'],
        ];
        $expectedStatuses = [
            'awful',
            'pending_to_be_awful',
        ];
        $itemCollection = $this->getMockBuilder('Magento\Rma\Model\ResourceModel\Item\Collection')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $itemMock = $this->getMockBuilder('Magento\Rma\Model\Item')
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getStatus', '__wakeup'])
            ->getMock();

        $this->collectionFactoryMock->expects($this->once())->method('create')
            ->will($this->returnValue($itemCollection));
        $itemCollection->expects($this->once())->method('addAttributeToFilter')
            ->with('rma_entity_id', $rmaId);
        $itemCollection->expects($this->once())->method('getIterator')
            ->will($this->returnValue(new \ArrayIterator([$itemMock])));
        $itemMock->expects($this->once())->method('getId')->will($this->returnValue(2));
        $itemMock->expects($this->once())->method('getStatus')->will($this->returnValue('pending_to_be_awful'));

        $this->assertEquals($expectedStatuses, $this->rmaDataMapper->combineItemStatuses($requestedItems, $rmaId));
    }

    /**
     * @return array
     */
    public function saveRequestEmailDataProvider()
    {
        return [
            [[], ''],
            [['contact_email' => 'learnpython.org'], 'learnpython.org']
        ];
    }
}
