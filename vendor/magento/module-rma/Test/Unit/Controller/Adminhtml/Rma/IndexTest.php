<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Rma\Test\Unit\Controller\Adminhtml\Rma;

class IndexTest extends \Magento\Rma\Test\Unit\Controller\Adminhtml\RmaTest
{
    protected $name = 'Index';

    public function testIndexAction()
    {
        $layoutInterfaceMock = $this->getMockBuilder('Magento\Framework\View\LayoutInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $resultPageMock = $this->getMockBuilder('Magento\Framework\View\Result\Page')
            ->disableOriginalConstructor()
            ->getMock();
        $pageConfigMock = $this->getMockBuilder('Magento\Framework\View\Page\Config')
            ->disableOriginalConstructor()
            ->getMock();
        $blockMock = $this->getMock('Magento\Backend\Block\Menu', ['setActive', 'getMenuModel'], [], '', false);
        $menuModelMock = $this->getMock('Magento\Backend\Model\Menu', [], [], '', false);
        $this->viewMock->expects($this->once())
            ->method('getLayout')
            ->will($this->returnValue($layoutInterfaceMock));
        $this->viewMock->expects($this->once())->method('getPage')->will($this->returnValue($resultPageMock));
        $resultPageMock->expects($this->once())->method('getConfig')->will($this->returnValue($pageConfigMock));
        $pageConfigMock->expects($this->any())->method('getTitle')->will($this->returnValue($this->titleMock));
        $layoutInterfaceMock->expects($this->once())
            ->method('getBlock')
            ->with('menu')
            ->will($this->returnValue($blockMock));
        $blockMock->expects($this->once())
            ->method('setActive')
            ->with('Magento_Rma::sales_magento_rma_rma');
        $blockMock->expects($this->once())
            ->method('getMenuModel')
            ->will($this->returnValue($menuModelMock));
        $menuModelMock->expects($this->once())
            ->method('getParentItems')
            ->will($this->returnValue([]));
        $this->titleMock->expects($this->once())
            ->method('prepend')
        ->with(__('Returns'));
        $this->viewMock->expects($this->once())
            ->method('renderLayout');

        $this->assertNull($this->action->execute());
    }
}
