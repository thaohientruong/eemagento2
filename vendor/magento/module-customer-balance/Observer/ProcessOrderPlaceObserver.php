<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CustomerBalance\Observer;

use Magento\Framework\Event\ObserverInterface;

class ProcessOrderPlaceObserver implements ObserverInterface
{
    /**
     * Customer balance data
     *
     * @var \Magento\CustomerBalance\Helper\Data
     */
    protected $_customerBalanceData;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\CustomerBalance\Model\BalanceFactory
     */
    protected $_balanceFactory;

    /**
     * @var CheckStoreCreditBalance
     */
    protected $checkStoreCreditBalance;

    /**
     * Constructor
     *
     * @param \Magento\CustomerBalance\Model\BalanceFactory $balanceFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\CustomerBalance\Helper\Data $customerBalanceData
     * @param CheckStoreCreditBalance $checkStoreCreditBalance
     */
    public function __construct(
        \Magento\CustomerBalance\Model\BalanceFactory $balanceFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\CustomerBalance\Helper\Data $customerBalanceData,
        CheckStoreCreditBalance $checkStoreCreditBalance
    ) {
        $this->_balanceFactory = $balanceFactory;
        $this->_storeManager = $storeManager;
        $this->_customerBalanceData = $customerBalanceData;
        $this->checkStoreCreditBalance = $checkStoreCreditBalance;
    }

    /**
     * Check if customer balance was used in quote and reduce balance if so
     *
     * @param   \Magento\Framework\Event\Observer $observer
     * @return  $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->_customerBalanceData->isEnabled()) {
            return $this;
        }

        $order = $observer->getEvent()->getOrder();
        $quote = $observer->getEvent()->getQuote();

        if ($quote && $quote->getUseCustomerBalance()) {
            $order->setBaseCustomerBalanceAmount($quote->getBaseCustomerBalAmountUsed());
            $order->setCustomerBalanceAmount($quote->getCustomerBalanceAmountUsed());
        }

        if ($order->getBaseCustomerBalanceAmount() > 0) {
            $this->checkStoreCreditBalance->execute($order);

            $websiteId = $this->_storeManager->getStore($order->getStoreId())->getWebsiteId();
            $this->_balanceFactory->create()->setCustomerId(
                $order->getCustomerId()
            )->setWebsiteId(
                $websiteId
            )->setAmountDelta(
                -$order->getBaseCustomerBalanceAmount()
            )->setHistoryAction(
                \Magento\CustomerBalance\Model\Balance\History::ACTION_USED
            )->setOrder(
                $order
            )->save();
        }

        return $this;
    }
}
