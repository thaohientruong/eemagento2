<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CustomerBalance\Test\Unit\Model\Adminhtml;

use Magento\CustomerBalance\Model\Adminhtml\Balance;

/**
 * Test \Magento\CustomerBalance\Model\Adminhtml\Balance
 */
class BalanceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Balance
     */
    protected $_model;

    protected function setUp()
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        /** @var Balance $model */
        $this->_model = $helper->getObject('Magento\CustomerBalance\Model\Adminhtml\Balance');
    }

    public function testGetWebsiteIdWithException()
    {
        $this->setExpectedException('Magento\Framework\Exception\LocalizedException', __('Please set a website ID.'));
        $this->_model->getWebsiteId();
    }

    public function testGetWebsiteId()
    {
        $this->_model->setWebsiteId('some id');
        $this->assertEquals('some id', $this->_model->getWebsiteId());
    }
}
