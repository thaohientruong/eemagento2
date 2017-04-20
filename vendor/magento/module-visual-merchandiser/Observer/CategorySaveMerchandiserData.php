<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\VisualMerchandiser\Observer;

use Magento\Catalog\Model\Category;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\NoSuchEntityException;

class CategorySaveMerchandiserData implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\VisualMerchandiser\Model\Position\Cache
     */
    protected $_cache;

    /**
     * @var \Magento\VisualMerchandiser\Model\Rules
     */
    protected $_rules;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @param \Magento\VisualMerchandiser\Model\Position\Cache $cache
     * @param \Magento\VisualMerchandiser\Model\Rules $rules
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
        \Magento\VisualMerchandiser\Model\Position\Cache $cache,
        \Magento\VisualMerchandiser\Model\Rules $rules,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
    ) {
        $this->_cache = $cache;
        $this->_rules = $rules;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Execute observer
     *
     * @param Observer $observer
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var Category $category */
        $category = $observer->getEvent()->getCategory();
        // Assign cached positions
        $cacheKey = $observer->getEvent()->getRequest()->getPostValue(
            \Magento\VisualMerchandiser\Model\Position\Cache::POSITION_CACHE_KEY
        );
        $positions = $this->_cache->getPositions($cacheKey);
        if (is_array($positions)) {
            $category->setPostedProducts(
                $positions
            );
        }

        // Double check if this category actually exists in database
        // this is to prevent FK errors
        try {
            $this->categoryRepository->get($category->getId());
        } catch (NoSuchEntityException $e) {
            return;
        }

        // Can't save smart rules if it's a category without an ID
        if ($category->isObjectNew() || !$category->getId()) {
            return;
        }

        $general = $observer->getEvent()->getRequest()->getPostValue('general');

        // Save 'is smart category' state (or clear it)
        $isSmartCategory = isset($general['is_smart_category']) && $general['is_smart_category'] == 1;

        // Save smart category rules (or clear it)
        $rule = $this->_rules->loadByCategory($category);
        $rule->setData([
            'rule_id' => $rule->getId(),
            'category_id' => $category->getId(),
            'is_active' => $isSmartCategory,
            'conditions_serialized' => isset($general['smart_category_rules']) ? $general['smart_category_rules'] : ''
        ]);
        $rule->save();
    }
}
