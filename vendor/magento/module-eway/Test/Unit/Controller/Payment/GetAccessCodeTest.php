<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eway\Test\Unit\Controller\Payment;

use Magento\Eway\Controller\Payment\GetAccessCode;
use Magento\Eway\Gateway\Validator\AbstractResponseValidator;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Webapi\Exception;

/**
 * Class GetAccessCodeTest
 */
class GetAccessCodeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Mock data order id
     */
    const ORDER_ID = '1';

    /**
     * Mock data total due
     */
    const TOTAL_DUE = 10.02;

    /**
     * Mock data access code
     */
    const ACCESS_CODE = 'access_code';

    /**
     * @var GetAccessCode
     */
    protected $controller;

    /**
     * @var \Magento\Payment\Gateway\Command\CommandPoolInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $commandPool;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderRepository;

    /**
     * @var \Magento\Payment\Gateway\Data\PaymentDataObjectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentDataObjectFactory;

    /**
     * @var \Magento\Checkout\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Framework\Session\SessionManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sessionManager;

    /**
     * @var \Magento\Framework\Controller\ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultFactory;

    /**
     *
     */
    protected function setUp()
    {
        /** @var \Magento\Framework\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject $context */
        $context = $this->getMockBuilder('Magento\Framework\App\Action\Context')
            ->disableOriginalConstructor()
            ->getMock();

        $this->commandPool = $this->getMockBuilder('Magento\Payment\Gateway\Command\CommandPoolInterface')
            ->getMockForAbstractClass();

        $this->logger = $this->getMockBuilder('Psr\Log\LoggerInterface')
            ->getMockForAbstractClass();

        $this->orderRepository = $this->getMockBuilder('Magento\Sales\Api\OrderRepositoryInterface')
            ->getMockForAbstractClass();

        $this->paymentDataObjectFactory = $this->getMockBuilder('Magento\Payment\Gateway\Data\PaymentDataObjectFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $this->checkoutSession = $this->getMockBuilder('Magento\Checkout\Model\Session')
            ->disableOriginalConstructor()
            ->getMock();

        $this->sessionManager = $this->getMockBuilder('Magento\Framework\Session\SessionManager')
            ->setMethods(['setAccessCode'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultFactory = $this->getMockBuilder('Magento\Framework\Controller\ResultFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $context->expects($this->once())
            ->method('getResultFactory')
            ->willReturn($this->resultFactory);

        $this->controller = new GetAccessCode(
            $context,
            $this->commandPool,
            $this->logger,
            $this->orderRepository,
            $this->paymentDataObjectFactory,
            $this->checkoutSession,
            $this->sessionManager
        );
    }

    /**
     *
     */
    public function testExecuteInvalidOrderIdError()
    {
        $controllerResult = $this->getMockBuilder('Magento\Framework\Controller\ResultInterface')
            ->setMethods(['setData'])
            ->getMockForAbstractClass();

        $this->resultFactory->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_JSON)
            ->willReturn($controllerResult);
        $this->checkoutSession->expects($this->once())
            ->method('getData')
            ->with('last_order_id')
            ->willReturn('first');
        $controllerResult->expects($this->once())
            ->method('setHttpResponseCode')
            ->with(Exception::HTTP_BAD_REQUEST);
        $controllerResult->expects($this->once())
            ->method('setData')
            ->with(['message' => __('Sorry, but something went wrong')]);

        $this->controller->execute();
    }

    /**
     *
     */
    public function testExecuteAssertOrderPaymentError()
    {
        $order = $this->getMockBuilder('Magento\Sales\Api\Data\OrderInterface')
            ->getMockForAbstractClass();
        $wrongPayment = $this->getMockBuilder('Magento\Quote\Model\Quote\Payment')
            ->disableOriginalConstructor()
            ->getMock();
        $controllerResult = $this->getMockBuilder('Magento\Framework\Controller\ResultInterface')
            ->setMethods(['setData'])
            ->getMockForAbstractClass();

        $this->resultFactory->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_JSON)
            ->willReturn($controllerResult);
        $this->checkoutSession->expects($this->once())
            ->method('getData')
            ->with('last_order_id')
            ->willReturn(self::ORDER_ID);
        $this->orderRepository->expects($this->once())
            ->method('get')
            ->with((int) self::ORDER_ID)
            ->willReturn($order);
        $order->expects($this->once())
            ->method('getPayment')
            ->willReturn($wrongPayment);
        $controllerResult->expects($this->once())
            ->method('setHttpResponseCode')
            ->with(Exception::HTTP_BAD_REQUEST);
        $controllerResult->expects($this->once())
            ->method('setData')
            ->with(['message' => __('Sorry, but something went wrong')]);
        $this->logger->expects($this->once())
            ->method('critical')
            ->with($this->isInstanceOf('\Exception'));

        $this->controller->execute();
    }

    /**
     *
     */
    public function testExecuteReadAccessCodeError()
    {
        $order = $this->getMockBuilder('Magento\Sales\Api\Data\OrderInterface')
            ->getMockForAbstractClass();
        $payment = $this->getMockBuilder('Magento\Sales\Model\Order\Payment')
            ->disableOriginalConstructor()
            ->getMock();
        $paymentDO = $this->getMockBuilder('Magento\Payment\Gateway\Data\PaymentDataObjectInterface')
            ->getMockForAbstractClass();
        $commandMock = $this->getMockBuilder('Magento\Payment\Gateway\CommandInterface')
            ->getMockForAbstractClass();
        $commandResult = $this->getMockBuilder('Magento\Payment\Gateway\Command\ResultInterface')
            ->getMockForAbstractClass();
        $controllerResult = $this->getMockBuilder('Magento\Framework\Controller\ResultInterface')
            ->setMethods(['setData'])
            ->getMockForAbstractClass();

        $this->resultFactory->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_JSON)
            ->willReturn($controllerResult);
        $this->checkoutSession->expects($this->once())
            ->method('getData')
            ->with('last_order_id')
            ->willReturn(self::ORDER_ID);
        $this->orderRepository->expects($this->once())
            ->method('get')
            ->with(self::ORDER_ID)
            ->willReturn($order);
        $order->expects($this->once())
            ->method('getPayment')
            ->willReturn($payment);
        $this->paymentDataObjectFactory->expects($this->once())
            ->method('create')
            ->with($payment)
            ->willReturn($paymentDO);
        $order->expects($this->once())
            ->method('getTotalDue')
            ->willReturn(self::TOTAL_DUE);
        $this->commandPool->expects($this->once())
            ->method('get')
            ->with('get_access_code')
            ->willReturn($commandMock);
        $commandMock->expects($this->once())
            ->method('execute')
            ->with(
                [
                    'payment' => $paymentDO,
                    'amount' => self::TOTAL_DUE
                ]
            )
            ->willReturn($commandResult);
        $commandResult->expects($this->once())
            ->method('get')
            ->willReturn(['AccessCode' => null]);
        $controllerResult->expects($this->once())
            ->method('setHttpResponseCode')
            ->with(Exception::HTTP_BAD_REQUEST);
        $controllerResult->expects($this->once())
            ->method('setData')
            ->with(['message' => __('Sorry, but something went wrong')]);
        $this->logger->expects($this->once())
            ->method('critical')
            ->with($this->isInstanceOf('\Exception'));

        $this->controller->execute();
    }

    /**
     *
     */
    public function testExecute()
    {
        $order = $this->getMockBuilder('Magento\Sales\Api\Data\OrderInterface')
            ->getMockForAbstractClass();
        $payment = $this->getMockBuilder('Magento\Sales\Model\Order\Payment')
            ->disableOriginalConstructor()
            ->getMock();
        $paymentDO = $this->getMockBuilder('Magento\Payment\Gateway\Data\PaymentDataObjectInterface')
            ->getMockForAbstractClass();
        $commandMock = $this->getMockBuilder('Magento\Payment\Gateway\CommandInterface')
            ->getMockForAbstractClass();
        $commandResult = $this->getMockBuilder('Magento\Payment\Gateway\Command\ResultInterface')
            ->getMockForAbstractClass();
        $controllerResult = $this->getMockBuilder('Magento\Framework\Controller\ResultInterface')
            ->setMethods(['setData'])
            ->getMockForAbstractClass();

        $this->checkoutSession->expects($this->once())
            ->method('getData')
            ->with('last_order_id')
            ->willReturn(self::ORDER_ID);
        $this->orderRepository->expects($this->once())
            ->method('get')
            ->with(self::ORDER_ID)
            ->willReturn($order);
        $order->expects($this->once())
            ->method('getPayment')
            ->willReturn($payment);
        $this->paymentDataObjectFactory->expects($this->once())
            ->method('create')
            ->with($payment)
            ->willReturn($paymentDO);
        $order->expects($this->once())
            ->method('getTotalDue')
            ->willReturn(self::TOTAL_DUE);
        $this->commandPool->expects($this->once())
            ->method('get')
            ->with('get_access_code')
            ->willReturn($commandMock);
        $commandMock->expects($this->once())
            ->method('execute')
            ->with(
                [
                    'payment' => $paymentDO,
                    'amount' => self::TOTAL_DUE
                ]
            )
            ->willReturn($commandResult);
        $commandResult->expects($this->once())
            ->method('get')
            ->willReturn([AbstractResponseValidator::ACCESS_CODE => self::ACCESS_CODE]);
        $this->sessionManager->expects($this->once())
            ->method('setAccessCode')
            ->with(self::ACCESS_CODE);
        $this->resultFactory->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_JSON)
            ->willReturn($controllerResult);
        $controllerResult->expects($this->once())
            ->method('setData')
            ->with(['access_code' => self::ACCESS_CODE]);

        $this->assertSame($controllerResult, $this->controller->execute());
    }
}
