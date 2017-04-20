<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Rma\Controller\Guest;

use Magento\Rma\Model\Rma;

class AddLabel extends \Magento\Rma\Controller\Guest
{
    /**
     * Add Tracking Number action
     *
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        if ($this->_loadValidRma()) {
            try {
                $rma = $this->_coreRegistry->registry('current_rma');

                if (!$rma->isAvailableForPrintLabel()) {
                    throw new \Magento\Framework\Exception\LocalizedException(__('Shipping Labels are not allowed.'));
                }

                $response = false;
                $number = $this->getRequest()->getPost('number');
                $number = trim(strip_tags($number));
                $carrier = $this->getRequest()->getPost('carrier');
                $carriers = $this->_objectManager->get('Magento\Rma\Helper\Data')
                    ->getShippingCarriers($rma->getStoreId());

                if (!isset($carriers[$carrier])) {
                    throw new \Magento\Framework\Exception\LocalizedException(__('Please select a valid carrier.'));
                }

                if (empty($number)) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('Please enter a valid tracking number.')
                    );
                }
                /** @var $rmaShipping \Magento\Rma\Model\Shipping */
                $rmaShipping = $this->_objectManager->create('Magento\Rma\Model\Shipping');
                $rmaShipping->setRmaEntityId($rma->getEntityId())
                    ->setTrackNumber($number)
                    ->setCarrierCode($carrier)
                    ->setCarrierTitle($carriers[$carrier])
                    ->save();
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $response = ['error' => true, 'message' => $e->getMessage()];
            } catch (\Exception $e) {
                $response = ['error' => true, 'message' => __('We can\'t add a label right now.')];
            }
        } else {
            $response = ['error' => true, 'message' => __('You selected the wrong RMA.')];
        }
        if (is_array($response)) {
            $this->_objectManager->get('Magento\Framework\Session\Generic')->setErrorMessage($response['message']);
        }

        return $this->resultLayoutFactory->create();
    }
}
