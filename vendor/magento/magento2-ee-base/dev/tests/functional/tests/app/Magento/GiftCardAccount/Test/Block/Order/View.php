<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GiftCardAccount\Test\Block\Order;

use Magento\Mtf\Client\Locator;

/**
 * Class View
 * View block on order's view page
 */
class View extends \Magento\Sales\Test\Block\Order\View
{
    /**
     * Get item block
     *
     * @param int $id [optional]
     * @return Items
     */
    public function getItemBlock($id = null)
    {
        $selector = ($id === null) ? $this->content : sprintf($this->itemBlock, $id) . $this->content;
        return $this->blockFactory->create(
            'Magento\GiftCardAccount\Test\Block\Order\Items',
            ['element' => $this->_rootElement->find($selector, Locator::SELECTOR_XPATH)]
        );
    }
}
