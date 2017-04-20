<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reward\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

class EarnForOrder implements ObserverInterface
{
    /**
     * Reward place order restriction interface
     *
     * @var \Magento\Reward\Observer\PlaceOrder\RestrictionInterface
     */
    protected $_restriction;

    /**
     * Reward model factory
     *
     * @var \Magento\Reward\Model\RewardFactory
     */
    protected $_modelFactory;

    /**
     * Reward resource model factory
     *
     * @var \Magento\Reward\Model\ResourceModel\RewardFactory
     */
    protected $_resourceFactory;

    /**
     * Core model store manager interface
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Reward helper.
     *
     * @var \Magento\Reward\Helper\Data
     */
    protected $rewardHelper;

    /**
     * @param \Magento\Reward\Observer\PlaceOrder\RestrictionInterface $restriction
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Reward\Model\RewardFactory $modelFactory
     * @param \Magento\Reward\Model\ResourceModel\RewardFactory $resourceFactory
     * @param \Magento\Reward\Helper\Data $rewardHelper
     */
    public function __construct(
        \Magento\Reward\Observer\PlaceOrder\RestrictionInterface $restriction,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Reward\Model\RewardFactory $modelFactory,
        \Magento\Reward\Model\ResourceModel\RewardFactory $resourceFactory,
        \Magento\Reward\Helper\Data $rewardHelper
    ) {
        $this->_restriction = $restriction;
        $this->_storeManager = $storeManager;
        $this->_modelFactory = $modelFactory;
        $this->_resourceFactory = $resourceFactory;
        $this->rewardHelper = $rewardHelper;
    }

    /**
     * Increase reward points balance for sales rules applied to order.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        if ($this->_restriction->isAllowed() === false) {
            return;
        }

        $pointsDelta = 0;
        /* @var $order \Magento\Sales\Model\Order */
        $order = $observer->getEvent()->getOrder();
        $appliedRuleIds = array_unique(explode(',', $order->getAppliedRuleIds()));
        /** @var $resource \Magento\Reward\Model\ResourceModel\Reward */
        $rewardRules = $this->_resourceFactory->create()->getRewardSalesrule($appliedRuleIds);
        foreach ($rewardRules as $rule) {
            $pointsDelta += (int)$rule['points_delta'];
        }

        if ($pointsDelta && !$order->getCustomerIsGuest()) {
            $reward = $this->_modelFactory->create();
            $reward->setCustomerId(
                $order->getCustomerId()
            )->setWebsiteId(
                $this->_storeManager->getStore($order->getStoreId())->getWebsiteId()
            )->setPointsDelta(
                $pointsDelta
            )->setAction(
                \Magento\Reward\Model\Reward::REWARD_ACTION_SALESRULE
            )->setActionEntity(
                $order
            )->updateRewardPoints();

            $order->addStatusHistoryComment(
                __(
                    'Customer earned promotion extra %1.',
                    $this->rewardHelper->formatReward($pointsDelta)
                )
            );
        }
    }
}
