<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TargetRule\Block\Adminhtml\Rule;

use Magento\Framework\Data\Form\Element\AbstractElement;

class Conditions implements \Magento\Framework\Data\Form\Element\Renderer\RendererInterface
{
    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        if ($element->getRule() && $element->getRule()->getConditions()) {
            return $element->getRule()->getConditions()->asHtmlRecursive();
        }
        return '';
    }
}
