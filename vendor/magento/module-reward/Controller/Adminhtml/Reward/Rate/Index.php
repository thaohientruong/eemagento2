<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reward\Controller\Adminhtml\Reward\Rate;

/**
 * @codeCoverageIgnore
 */
class Index extends \Magento\Reward\Controller\Adminhtml\Reward\Rate
{
    /**
     * Index Action
     *
     * @return void
     */
    public function execute()
    {
        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Reward Exchange Rates'));
        $this->_view->renderLayout();
    }
}
