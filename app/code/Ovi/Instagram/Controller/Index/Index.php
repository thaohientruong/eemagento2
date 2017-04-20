<?php
namespace Ovi\Instagram\Controller\Index;
use \Magento\Framework\App\Action\Action;
/**
 * Class Index
 *
 * @package Ovi\Instagram\Controller\Index
 */
class Index extends Action
{
    public function execute() {
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }

}