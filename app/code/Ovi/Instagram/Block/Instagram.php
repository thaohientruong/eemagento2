<?php

namespace Ovi\Instagram\Block;

use Magento\Framework\View\Element\Template;

/**
 * Class Instagram
 *
 * @package Ovi\Instagram\Block
 */
class Instagram extends Template
{
    /**
     *
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->pageConfig->getTitle()->set(__('Instagram'));
    }
    

}