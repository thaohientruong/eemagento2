<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cybersource\Test\Unit\Gateway\Validator\Soap;

use Magento\Cybersource\Gateway\Validator\Soap\SubscriptionIdValidator;
use Magento\Payment\Gateway\Validator\Result;
use Magento\Cybersource\Gateway\Request\Soap\AuthorizeDataBuilder;

class SubscriptionIdValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactory;

    /**
     * @var \Magento\Cybersource\Gateway\Validator\Soap\SubscriptionIdValidator
     */
    private $validator;

    public function setUp()
    {
        $this->resultFactory = $this->getMockBuilder(
            'Magento\Payment\Gateway\Validator\ResultInterfaceFactory'
        )
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->validator = new SubscriptionIdValidator($this->resultFactory);
    }

    public function testValidateNoSubscriptionId()
    {
        $response = [];

        $this->resultFactory->expects(static::once())
            ->method('create')
            ->with([
                'isValid' => false,
                'failsDescription' => [__('Your payment has been declined. Please try again.')]
            ])
            ->willReturn(new Result(false, [__('Your payment has been declined. Please try again.')]));

        $result = $this->validator->validate(['response' => $response]);

        static::assertFalse($result->isValid());
    }

    public function testValidate()
    {
        $response = [
            'paySubscriptionCreateReply' => [
                AuthorizeDataBuilder::SUBSCRIPTION_ID => '1111'
            ]
        ];

        $this->resultFactory->expects(static::once())
            ->method('create')
            ->with([
                'isValid' => true,
                'failsDescription' => [__('Your payment has been declined. Please try again.')]
            ])
            ->willReturn(new Result(false, [__('Your payment has been declined. Please try again.')]));

        $result = $this->validator->validate(['response' => $response]);

        static::assertFalse($result->isValid());
    }
}
