<?php
/**
 * @category    Magento
 * @package     Magento_TargetRule
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TargetRule\Model\Indexer\TargetRule\Plugin;

class Category extends AbstractPlugin
{
    /**
     * Invalidate target rule indexer after deleting category
     *
     * @param \Magento\Catalog\Model\Category $category
     * @return \Magento\Catalog\Model\Category
     */
    public function afterDelete(\Magento\Catalog\Model\Category $category)
    {
        $this->invalidateIndexers();
        return $category;
    }

    /**
     * Invalidate target rule indexer after changing category products
     *
     * @param \Magento\Catalog\Model\Category $category
     * @return \Magento\Catalog\Model\Category
     */
    public function afterSave(\Magento\Catalog\Model\Category $category)
    {
        $isChangedProductList = $category->getData('is_changed_product_list');
        if ($isChangedProductList) {
            $this->invalidateIndexers();
        }
        return $category;
    }
}
