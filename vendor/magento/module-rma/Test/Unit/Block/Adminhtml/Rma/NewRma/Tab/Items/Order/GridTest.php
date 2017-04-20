<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Rma\Test\Unit\Block\Adminhtml\Rma\NewRma\Tab\Items\Order;

/**
 * Class GridTest
 */
class GridTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Rma\Block\Adminhtml\Rma\Edit\Tab\Items\Grid
     */
    protected $grid;

    /**
     * @var \Magento\Rma\Model\Item|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $rmaItemMock;

    /**
     * @var \Magento\Sales\Model\Order\Item|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $salesItemMock;
    /**
     * Test setUp
     */
    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->rmaitemMock = $this->getMock(
            'Magento\Rma\Model\Item',
            ['getReturnableQty', '__wakeup'],
            [],
            '',
            false
        );
        $this->salesItemMock = $this->getMock(
            'Magento\Sales\Model\Order\Item',
            [],
            [],
            '',
            false
        );
        $this->grid = $objectManager->getObject('Magento\Rma\Block\Adminhtml\Rma\NewRma\Tab\Items\Order\Grid', [
            'rmaItem' => $this->rmaitemMock
        ]);
    }

    /**
     *  test method getRemainingQty
     */
    public function testGetRemainingQty()
    {
        $this->rmaitemMock->expects($this->once())
            ->method('getReturnableQty')
            ->will($this->returnValue(100.50));

        $this->assertEquals(100.50, $this->grid->getRemainingQty($this->salesItemMock));
    }
}
