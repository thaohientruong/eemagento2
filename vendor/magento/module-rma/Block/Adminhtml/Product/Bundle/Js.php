<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Rma\Block\Adminhtml\Product\Bundle;

class Js extends \Magento\Backend\Block\Template
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Get url for Bundle AJAX Action
     *
     * @return string
     */
    public function getLoadBundleUrl()
    {
        return $this->getUrl('adminhtml/*/showBundleItems');
    }

    /**
     * Get url for Details AJAX Action
     *
     * @return string
     */
    public function getLoadAttributesUrl()
    {
        return $this->getUrl(
            'adminhtml/*/loadNewAttributes',
            ['order_id' => $this->_coreRegistry->registry('current_order')->getId()]
        );
    }

    /**
     * Get load order id
     *
     * @return int
     */
    public function getLoadOrderId()
    {
        return $this->getRequest()->getParam('order_id');
    }
}
