<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogEvent\Test\Unit\Block\Adminhtml\Event;

use Magento\CatalogEvent\Block\Adminhtml\Event\Edit;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Unit test for Magento\CatalogEvent\Block\Adminhtml\Event\Edit
 */
class EditTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogEvent\Block\Adminhtml\Event\Edit
     */
    protected $edit;

    /**
     * @var \Magento\Backend\Block\Widget\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registryMock;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->contextMock = (new ObjectManager($this))->getObject('Magento\Backend\Block\Widget\Context');
        $this->registryMock = $this->getMockBuilder('Magento\Framework\Registry')
            ->disableOriginalConstructor()
            ->getMock();

        $this->edit = new Edit(
            $this->contextMock,
            $this->registryMock
        );
    }

    /**
     * @return void
     */
    public function testGetEvent()
    {
        $this->registryMock
            ->expects($this->once())
            ->method('registry')
            ->with('magento_catalogevent_event')
            ->willReturn('result');

        $this->assertEquals('result', $this->edit->getEvent());
    }
}
