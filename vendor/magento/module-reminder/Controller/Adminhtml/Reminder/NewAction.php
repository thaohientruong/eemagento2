<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reminder\Controller\Adminhtml\Reminder;

class NewAction extends \Magento\Reminder\Controller\Adminhtml\Reminder
{
    /**
     * Create new rule
     *
     * @return void
     */
    public function execute()
    {
        // the same form is used to create and edit
        $this->_forward('edit');
    }
}
