<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Rma\Controller\Adminhtml\Rma;

class RmaCustomer extends \Magento\Rma\Controller\Adminhtml\Rma
{
    /**
     * Generate RMA grid for ajax request from customer page
     *
     * @return void
     */
    public function execute()
    {
        $customerId = intval($this->getRequest()->getParam('id'));
        if ($customerId) {
            $this->getResponse()->setBody(
                $this->_view->getLayout()->createBlock(
                    'Magento\Rma\Block\Adminhtml\Customer\Edit\Tab\Rma'
                )->setCustomerId(
                    $customerId
                )->toHtml()
            );
        }
    }
}
