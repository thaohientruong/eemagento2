<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftCardAccount\Model\Total\Creditmemo;

class Giftcardaccount extends \Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal
{
    /**
     * Collect gift card account totals for credit memo
     *
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @return $this
     */
    public function collect(\Magento\Sales\Model\Order\Creditmemo $creditmemo)
    {
        $order = $creditmemo->getOrder();
        if ($order->getBaseGiftCardsAmount() && $order->getBaseGiftCardsInvoiced() != 0) {
            $gcaLeft = $order->getBaseGiftCardsInvoiced() - $order->getBaseGiftCardsRefunded();

            $used = 0;
            $baseUsed = 0;

            if ($gcaLeft >= $creditmemo->getBaseGrandTotal()) {
                $baseUsed = $creditmemo->getBaseGrandTotal();
                $used = $creditmemo->getGrandTotal();

                $creditmemo->setBaseGrandTotal(0);
                $creditmemo->setGrandTotal(0);

                $creditmemo->setAllowZeroGrandTotal(true);
            } else {
                $baseUsed = $order->getBaseGiftCardsInvoiced() - $order->getBaseGiftCardsRefunded();
                $used = $order->getGiftCardsInvoiced() - $order->getGiftCardsRefunded();

                $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() - $baseUsed);
                $creditmemo->setGrandTotal($creditmemo->getGrandTotal() - $used);
            }

            $creditmemo->setBaseGiftCardsAmount($baseUsed);
            $creditmemo->setGiftCardsAmount($used);
        }

        $creditmemo->setBaseCustomerBalanceReturnMax(
            $creditmemo->getBaseCustomerBalanceReturnMax() + $creditmemo->getBaseGiftCardsAmount()
        );

        $creditmemo->setCustomerBalanceReturnMax(
            $creditmemo->getCustomerBalanceReturnMax() + $creditmemo->getGiftCardsAmount()
        );
        $creditmemo->setCustomerBalanceReturnMax(
            $creditmemo->getCustomerBalanceReturnMax() + $creditmemo->getGrandTotal()
        );

        return $this;
    }
}
