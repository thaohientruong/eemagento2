<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\VisualMerchandiser\Model\Sorting\Sku;

use \Magento\VisualMerchandiser\Model\Sorting\AttributeAbstract;

class Descending extends AttributeAbstract
{
    /**
     * @return string
     */
    protected function getSortField()
    {
        return 'sku';
    }

    /**
     * @return string
     */
    protected function getSortDirection()
    {
        return $this->descOrder();
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return __("SKU: Descending");
    }
}
