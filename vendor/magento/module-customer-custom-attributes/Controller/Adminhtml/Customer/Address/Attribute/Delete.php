<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CustomerCustomAttributes\Controller\Adminhtml\Customer\Address\Attribute;

class Delete extends \Magento\CustomerCustomAttributes\Controller\Adminhtml\Customer\Address\Attribute
{
    /**
     * Delete attribute action
     *
     * @return void
     */
    public function execute()
    {
        $attributeId = $this->getRequest()->getParam('attribute_id');
        if ($attributeId) {
            $attributeObject = $this->_initAttribute()->load($attributeId);
            if ($attributeObject->getEntityTypeId() != $this->_getEntityType()->getId() ||
                !$attributeObject->getIsUserDefined()
            ) {
                $this->messageManager->addError(__('You cannot delete this attribute.'));
                $this->_redirect('adminhtml/*/');
                return;
            }
            try {
                $attributeObject->delete();
                $this->messageManager->addSuccess(__('You deleted the customer address attribute.'));
                $this->_redirect('adminhtml/*/');
                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_redirect('adminhtml/*/edit', ['attribute_id' => $attributeId, '_current' => true]);
                return;
            } catch (\Exception $e) {
                $this->messageManager->addException(
                    $e,
                    __('We can\'t delete the customer address attribute right now.')
                );
                $this->_redirect('adminhtml/*/edit', ['attribute_id' => $attributeId, '_current' => true]);
                return;
            }
        }

        $this->_redirect('adminhtml/*/');
        return;
    }
}
