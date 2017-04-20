<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesArchive\Controller\Adminhtml\Archive;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

/**
 * Class MassAdd
 */
class MassAdd extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
{
    /**
     * @var \Magento\SalesArchive\Model\Archive
     */
    protected $_archiveModel;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param Filter $filter
     * @param \Magento\SalesArchive\Model\Archive $archiveModel
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        Filter $filter,
        \Magento\SalesArchive\Model\Archive $archiveModel,
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->_archiveModel = $archiveModel;
        parent::__construct($context, $filter);
    }

    /**
     * Add selected orders to archive
     *
     * @param AbstractCollection $collection
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function massAction(AbstractCollection $collection)
    {
        $archivedIds = $this->_archiveModel->archiveOrdersById($collection->getAllIds());
        $archivedCount = count($archivedIds);

        if ($archivedCount > 0) {
            $this->messageManager->addSuccess(__('We archived %1 order(s).', $archivedCount));
        } else {
            $this->messageManager->addWarning(__("We can't archive the selected order(s)."));
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('sales/order/');

        return $resultRedirect;
    }

    /**
     * {@inheritDoc}
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_SalesArchive::add');
    }
}
