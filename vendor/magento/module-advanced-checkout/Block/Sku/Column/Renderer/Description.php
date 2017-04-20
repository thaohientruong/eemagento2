<?php
/**
 * SKU failed description block renderer
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedCheckout\Block\Sku\Column\Renderer;

class Description extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $descriptionBlock = $this->getLayout()->createBlock(
            'Magento\AdvancedCheckout\Block\Adminhtml\Sku\Errors\Grid\Description',
            '',
            ['data' => ['product' => $row->getProduct(), 'item' => $row]]
        );

        return $descriptionBlock->toHtml();
    }
}
