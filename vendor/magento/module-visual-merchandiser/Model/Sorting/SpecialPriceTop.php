<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\VisualMerchandiser\Model\Sorting;

use \Magento\Framework\DB\Select;

class SpecialPriceTop extends SortAbstract implements SortInterface
{
    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function sort(
        \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
    ) {
        $this->addPriceData($collection);
        $collection->getSelect()
            ->group('entity_id')
            ->reset(Select::ORDER)
            ->order('(price_index.price <> price_index.final_price) ' . $collection::SORT_ORDER_DESC);

        return $collection;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return __("Special price to top");
    }
}
