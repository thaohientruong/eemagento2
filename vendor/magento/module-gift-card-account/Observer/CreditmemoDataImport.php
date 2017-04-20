<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftCardAccount\Observer;

use Magento\Framework\Event\ObserverInterface;

class CreditmemoDataImport implements ObserverInterface
{
    /**
     * Set refund flag to creditmemo based on user input
     * used for event: adminhtml_sales_order_creditmemo_register_before
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $creditmemo = $observer->getEvent()->getCreditmemo();

        $input = $observer->getEvent()->getInput();

        if (isset($input['refund_giftcardaccount']) && $input['refund_giftcardaccount']) {
            $creditmemo->setRefundGiftCards(true);
        }

        return $this;
    }
}
