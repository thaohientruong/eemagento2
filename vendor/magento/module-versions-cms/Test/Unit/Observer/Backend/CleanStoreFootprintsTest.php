<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\VersionsCms\Test\Unit\Observer\Backend;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class CleanStoreFootprintsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\VersionsCms\Model\Hierarchy\NodeFactory|MockObject
     */
    protected $hierarchyNodeFactoryMock;

    /**
     * @var \Magento\Widget\Model\ResourceModel\Widget\Instance\CollectionFactory|MockObject
     */
    protected $widgetCollectionFactoryMock;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\VersionsCms\Observer\Backend\CleanStoreFootprints
     */
    protected $unit;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->hierarchyNodeFactoryMock = $this->getMock(
            'Magento\VersionsCms\Model\Hierarchy\NodeFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->widgetCollectionFactoryMock = $this->getMock(
            'Magento\Widget\Model\ResourceModel\Widget\Instance\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->unit = $this->objectManagerHelper->getObject(
            'Magento\VersionsCms\Observer\Backend\CleanStoreFootprints',
            [
                'hierarchyNodeFactory' => $this->hierarchyNodeFactoryMock,
                'widgetCollectionFactory' => $this->widgetCollectionFactoryMock,
            ]
        );
    }

    public function testCleanStoreFootprints()
    {
        $storeId = 2;

        $this->hierarchyNodeDeleteByScope();
        /** @var \Magento\Widget\Model\Widget\Instance|MockObject $widgetInstanceMock */
        $widgetInstanceMock = $this->getMockBuilder('Magento\Widget\Model\Widget\Instance')
            ->disableOriginalConstructor()
            ->setMethods(['getStoreIds', 'setStoreIds', 'getWidgetParameters', 'setWidgetParameters', 'save'])
            ->getMock();
        $widgetInstanceMock->expects($this->once())
            ->method('getStoreIds')
            ->willReturn([0 => 1, 1 => $storeId, 2 => 3]);
        $widgetInstanceMock->expects($this->once())
            ->method('setStoreIds')
            ->with([0 => 1, 2 => 3]);
        $widgetInstanceMock->expects($this->once())
            ->method('getWidgetParameters')
            ->willReturn([
                'anchor_text_' . $storeId => 'test',
                'title_' . $storeId => 'test',
                'node_id_' . $storeId => 'test',
                'template_' . $storeId => 'test',
                'someParameter'  => 'test'
            ]);
        $widgetInstanceMock->expects($this->once())
            ->method('setWidgetParameters')
            ->with(['someParameter'  => 'test']);
        $widgetInstanceMock->expects($this->once())
            ->method('save');

        /** @var \Magento\Widget\Model\ResourceModel\Widget\Instance\Collection|MockObject $widgetsCollectionMock */
        $widgetsCollectionMock = $this->getMockBuilder('Magento\Widget\Model\ResourceModel\Widget\Instance\Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $widgetsCollectionMock->expects($this->once())
            ->method('addStoreFilter')
            ->with([$storeId, false])
            ->willReturnSelf();
        $widgetsCollectionMock->expects($this->once())
            ->method('addFieldToFilter')
            ->with('instance_type', 'Magento\VersionsCms\Block\Widget\Node')
            ->willReturnSelf();
        $widgetsCollectionMock->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$widgetInstanceMock]));
        $this->widgetCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($widgetsCollectionMock);

        $this->unit->clean($storeId);
    }

    /**
     * @return void
     */
    protected function hierarchyNodeDeleteByScope()
    {
        /** @var \Magento\VersionsCms\Model\Hierarchy\Node|MockObject $hierarchyNode */
        $hierarchyNode = $this->getMock('Magento\VersionsCms\Model\Hierarchy\Node', [], [], '', false);
        $hierarchyNode->expects($this->any())->method('deleteByScope');
        $this->hierarchyNodeFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($hierarchyNode);
    }
}
