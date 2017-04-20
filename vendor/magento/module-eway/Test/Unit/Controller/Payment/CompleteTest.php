<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eway\Test\Unit\Controller\Payment;

use Magento\Checkout\Model\Session;
use Magento\Eway\Controller\Payment\Complete;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Session\SessionManager;
use Magento\Framework\View\Layout\ProcessorInterface;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Class AcceptTest
 *
 * @see \Magento\Eway\Controller\Payment\Complete
 */
class CompleteTest extends \PHPUnit_Framework_TestCase
{
    const ORDER_ID = 10;

    /**
     * @var Complete
     */
    private $controller;

    /**
     * @var CommandPoolInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $commandPoolMock;

    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    /**
     * @var LayoutFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $layoutFactoryMock;

    /**
     * @var Session|\PHPUnit_Framework_MockObject_MockObject
     */
    private $checkoutSessionMock;

    /**
     * @var PaymentDataObjectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentDataObjectFactoryMock;

    /**
     * @var OrderRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderRepositoryMock;

    /**
     * @var SessionManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sessionManagerMock;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var ProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $processorMock;

    /**
     * @var PaymentDataObjectInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentDataObjectMock;

    public function setUp()
    {
        $contextMock = $this->getMockBuilder('Magento\Framework\App\Action\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestMock = $this->getMockBuilder('Magento\Framework\App\RequestInterface')
            ->getMockForAbstractClass();
        $this->commandPoolMock = $this
            ->getMockBuilder('Magento\Payment\Gateway\Command\CommandPoolInterface')
            ->getMockForAbstractClass();
        $this->loggerMock = $this->getMockBuilder('Psr\Log\LoggerInterface')
            ->getMockForAbstractClass();
        $this->layoutFactoryMock = $this->getMockBuilder('Magento\Framework\View\Result\LayoutFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->checkoutSessionMock = $this->getMockBuilder('Magento\Checkout\Model\Session')
            ->disableOriginalConstructor()
            ->getMock();
        $this->paymentDataObjectFactoryMock = $this->getMockBuilder(
            'Magento\Payment\Gateway\Data\PaymentDataObjectFactory'
        )->disableOriginalConstructor()
            ->getMock();
        $this->orderRepositoryMock = $this->getMockBuilder('Magento\Sales\Api\OrderRepositoryInterface')
            ->getMockForAbstractClass();
        $this->sessionManagerMock = $this->getMockBuilder('Magento\Framework\Session\SessionManager')
            ->setMethods(['getAccessCode'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->processorMock = $this->getMockBuilder('Magento\Framework\View\Layout\ProcessorInterface')
            ->getMockForAbstractClass();

        $contextMock->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->requestMock);

        $this->prepareLayout();
        $this->prepareData();

        $this->controller = new Complete(
            $contextMock,
            $this->commandPoolMock,
            $this->loggerMock,
            $this->layoutFactoryMock,
            $this->checkoutSessionMock,
            $this->paymentDataObjectFactoryMock,
            $this->sessionManagerMock
        );
    }

    private function prepareLayout()
    {
        $resultLayoutMock = $this->getMockBuilder('Magento\Framework\View\Result\Layout')
            ->disableOriginalConstructor()
            ->getMock();
        $layoutMock = $this->getMockBuilder('Magento\Framework\View\Layout')
            ->disableOriginalConstructor()
            ->getMock();

        $this->layoutFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultLayoutMock);

        $resultLayoutMock->expects($this->once())
            ->method('addDefaultHandle');
        $resultLayoutMock->expects($this->once())
            ->method('getLayout')
            ->willReturn($layoutMock);

        $layoutMock->expects($this->once())
            ->method('getUpdate')
            ->willReturn($this->processorMock);
    }

    private function prepareData()
    {
        $orderMock = $this->getMockBuilder('Magento\Sales\Api\Data\OrderInterface')
            ->getMockForAbstractClass();
        $orderPaymentMock = $this->getMockBuilder('Magento\Payment\Model\InfoInterface')
            ->getMockForAbstractClass();
        $this->paymentDataObjectMock = $this->getMockBuilder('Magento\Payment\Gateway\Data\PaymentDataObjectInterface')
            ->getMockForAbstractClass();

        $this->checkoutSessionMock->expects($this->once())
            ->method('getLastRealOrder')
            ->willReturn($orderMock);

        $orderMock->expects($this->once())
            ->method('getPayment')
            ->willReturn($orderPaymentMock);

        $this->paymentDataObjectFactoryMock->expects($this->once())
            ->method('create')
            ->with($orderPaymentMock)
            ->willReturn($this->paymentDataObjectMock);
    }

    public function testExecute()
    {
        $commandMock = $this->getMockBuilder('Magento\Payment\Gateway\CommandInterface')
            ->getMockForAbstractClass();

        $this->requestMock->expects($this->once())
            ->method('getParams')
            ->willReturn('test-params');

        $this->sessionManagerMock->expects($this->once())
            ->method('getAccessCode')
            ->willReturn('access_code');

        $this->commandPoolMock->expects($this->once())
            ->method('get')
            ->with('complete')
            ->willReturn($commandMock);

        $commandMock->expects($this->once())
            ->method('execute')
            ->with(
                [
                    'payment' => $this->paymentDataObjectMock,
                    'access_code' => 'access_code',
                    'request' => 'test-params'
                ]
            );

        $this->processorMock->expects($this->once())
            ->method('load')
            ->with(['response_success']);

        $this->assertInstanceOf(
            'Magento\Framework\View\Result\Layout',
            $this->controller->execute()
        );
    }

    public function testExecuteException()
    {
        $commandMock = $this->getMockBuilder('Magento\Payment\Gateway\CommandInterface')
            ->getMockForAbstractClass();

        $this->requestMock->expects($this->once())
            ->method('getParams')
            ->willReturn('test-params');

        $this->sessionManagerMock->expects($this->once())
            ->method('getAccessCode')
            ->willReturn('access_code');

        $this->commandPoolMock->expects($this->once())
            ->method('get')
            ->with('complete')
            ->willReturn($commandMock);

        $commandMock->expects($this->once())
            ->method('execute')
            ->with(
                [
                    'payment' => $this->paymentDataObjectMock,
                    'access_code' => 'access_code',
                    'request' => 'test-params'
                ]
            )->willThrowException(new \Exception());

        $this->loggerMock->expects($this->once())
            ->method('critical')
            ->with($this->isInstanceOf('\Exception'));

        $this->processorMock->expects($this->once())
            ->method('load')
            ->with(['response_failure']);

        $this->controller->execute();
    }
}
