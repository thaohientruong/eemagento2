<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ScheduledImportExport\Controller\Adminhtml\Scheduled\Operation;

use Magento\ScheduledImportExport\Controller\Adminhtml\Scheduled\Operation as OperationController;

class Index extends OperationController
{
    /**
     * Index action.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->createPage();
        return $resultPage;
    }
}
