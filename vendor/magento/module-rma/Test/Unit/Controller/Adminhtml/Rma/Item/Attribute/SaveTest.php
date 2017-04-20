<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Rma\Test\Unit\Controller\Adminhtml\Rma\Item\Attribute;

/**
 * Class SaveTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class SaveTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Rma\Controller\Adminhtml\Rma\Item\Attribute\Save
     */
    protected $action;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Backend\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\App\Response\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseMock;

    /**
     * @var \Magento\Framework\ObjectManager\ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \Magento\Rma\Model\Item\Attribute|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $attributeMock;
    /**
     * @var \Magento\Eav\Model\Entity\Type|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityTypeMock;

    /**
     * @var \Magento\Backend\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sessionMock;

    /**
     * @var \Magento\Framework\App\View|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $viewMock;

    /**
     * @var \Magento\Framework\View\Element\BlockInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $blockMock;

    /**
     * @var \Magento\Backend\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helperMock;

    /**
     * @var \Magento\CustomAttributeManagement\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $attributeHelperMock;

    /**
     * @var \Magento\Framework\App\ActionFlag|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $flagMock;

    /**
     * @var \Magento\Eav\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eavConfigMock;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute\Set|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $attributeSetMock;

    /**
     * @var \Magento\Framework\Message\Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $websiteFactoryMock;

    /**
     * Set up before each test
     * @return void
     */
    public function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->websiteFactoryMock = $this->getMock('Magento\Store\Model\WebsiteFactory', ['create'], [], '', false);
        $this->contextMock = $this->getMock('Magento\Backend\App\Action\Context', [], [], '', false);
        $this->requestMock = $this->getMock('Magento\Framework\App\Request\Http', [], [], '', false);
        $this->responseMock = $this->getMock('Magento\Framework\App\Response\Http', [], [], '', false);
        $this->attributeMock = $this->getMock('Magento\Rma\Model\Item\Attribute', [], [], '', false);
        $this->attributeSetMock = $this->getMock('Magento\Eav\Model\Entity\Attribute\Set', [], [], '', false);
        $this->entityTypeMock = $this->getMock('Magento\Eav\Model\Entity\Type', [], [], '', false);
        $this->objectManagerMock = $this->getMock('Magento\Framework\ObjectManager\ObjectManager', [], [], '', false);
        $this->sessionMock = $this->getMock('Magento\Backend\Model\Session', [], [], '', false);
        $this->viewMock = $this->getMock('Magento\Framework\App\View', [], [], '', false);
        $this->helperMock = $this->getMock('Magento\Backend\Helper\Data', [], [], '', false);
        $this->attributeHelperMock = $this->getMock('Magento\CustomAttributeManagement\Helper\Data', [], [], '', false);
        $this->flagMock = $this->getMock('Magento\Framework\App\ActionFlag', [], [], '', false);
        $this->eavConfigMock = $this->getMock('Magento\Eav\Model\Config', [], [], '', false);
        $this->messageManagerMock = $this->getMock('Magento\Framework\Message\Manager', [], [], '', false);
        $this->blockMock = $this->getMockForAbstractClass(
            'Magento\Framework\View\Element\BlockInterface',
            [],
            '',
            false,
            false,
            true,
            ['setActive', 'getMenuModel', 'getParentItems', 'addLink', 'getConfig', 'getTitle', 'prepend']
        );
        $this->contextMock->expects($this->atLeastOnce())
            ->method('getRequest')
            ->will($this->returnValue($this->requestMock));
        $this->contextMock->expects($this->atLeastOnce())
            ->method('getHelper')
            ->will($this->returnValue($this->helperMock));
        $this->contextMock->expects($this->atLeastOnce())
            ->method('getResponse')
            ->will($this->returnValue($this->responseMock));
        $this->contextMock->expects($this->atLeastOnce())
            ->method('getSession')
            ->will($this->returnValue($this->sessionMock));
        $this->contextMock->expects($this->atLeastOnce())
            ->method('getObjectManager')
            ->will($this->returnValue($this->objectManagerMock));
        $this->contextMock->expects($this->atLeastOnce())
            ->method('getView')
            ->will($this->returnValue($this->viewMock));
        $this->contextMock->expects($this->atLeastOnce())
            ->method('getActionFlag')
            ->willReturn($this->flagMock);
        $this->contextMock->expects($this->atLeastOnce())
            ->method('getMessageManager')
            ->willReturn($this->messageManagerMock);
        $this->action = $this->objectManager->getObject(
            'Magento\Rma\Controller\Adminhtml\Rma\Item\Attribute\Save',
            [
                'context' => $this->contextMock,
                'websiteFactory' => $this->websiteFactoryMock
            ]
        );
    }

    /**
     * Test for execute method
     * @return void
     */
    public function testExecute()
    {
        $this->requestMock->expects($this->once())
            ->method('getPostValue')
            ->willReturn([
                'frontend_input'=> '',
            ]);
        $this->attributeHelperMock->expects($this->once())
            ->method('filterPostData')
            ->willReturn(['frontend_input' => 'frontend_input']);
        $this->attributeHelperMock->expects($this->once())
            ->method('getAttributeBackendModelByInputType')
            ->willReturn('AttributeBackendModelByInputType');
        $this->attributeHelperMock->expects($this->once())
            ->method('getAttributeSourceModelByInputType')
            ->willReturn('AttributeSourceModelByInputType');
        $this->attributeHelperMock->expects($this->once())
            ->method('getAttributeBackendTypeByInputType')
            ->willReturn('AttributeBackendTypeByInputType');

        $this->eavConfigMock->expects($this->once())
            ->method('getEntityType')
            ->with('rma_item')
            ->willReturn($this->entityTypeMock);
        $this->entityTypeMock->expects($this->once())
            ->method('getDefaultAttributeSetId')
            ->willReturn(1);
        $this->requestMock->expects($this->once())
            ->method('isPost')
            ->willReturn(true);
        $this->objectManagerMock->expects($this->any())
            ->method('create')
            ->will(
                $this->returnValueMap(
                    [
                        ['Magento\Rma\Model\Item\Attribute', [], $this->attributeMock],
                        ['Magento\Eav\Model\Entity\Attribute\Set', [], $this->attributeSetMock],
                    ]
                )
            );

        $this->objectManagerMock->expects($this->any())
            ->method('get')
            ->will(
                $this->returnValueMap(
                    [
                        ['Magento\CustomAttributeManagement\Helper\Data', $this->attributeHelperMock],
                        ['Magento\Eav\Model\Config', $this->eavConfigMock],
                    ]
                )
            );
        $this->messageManagerMock->expects($this->once())->method('addSuccess');
        $this->assertEmpty($this->action->execute());
    }
}
