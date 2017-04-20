<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cybersource\Gateway\Validator\Soap;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use Magento\Cybersource\Gateway\Request\Soap\AuthorizeDataBuilder;

class SubscriptionIdValidator extends AbstractValidator
{
    /**
     * Performs domain-related validation for business object
     *
     * @param array $validationSubject
     * @return null|ResultInterface
     */
    public function validate(array $validationSubject)
    {
        $response = SubjectReader::readResponse($validationSubject);

        $result = $this->createResult(
            isset($response['paySubscriptionCreateReply'][AuthorizeDataBuilder::SUBSCRIPTION_ID]
            ),
            [__('Your payment has been declined. Please try again.')]
        );

        return $result;
    }
}
