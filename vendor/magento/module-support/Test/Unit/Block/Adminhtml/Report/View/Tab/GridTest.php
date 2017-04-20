<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Support\Test\Unit\Block\Adminhtml\Report\View\Tab;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\DataObject;

class GridTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Support\Block\Adminhtml\Report\View\Tab\Grid
     */
    protected $reportGridBlock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManagerHelper;

    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->reportGridBlock = $this->objectManagerHelper->getObject(
            'Magento\Support\Block\Adminhtml\Report\View\Tab\Grid'
        );
    }

    public function testCanDisplayContainer()
    {
        $this->assertFalse($this->reportGridBlock->canDisplayContainer());
    }

    public function testGetRowUrl()
    {
        $item = new DataObject();

        $this->assertEquals('', $this->reportGridBlock->getRowUrl($item));
    }
}
