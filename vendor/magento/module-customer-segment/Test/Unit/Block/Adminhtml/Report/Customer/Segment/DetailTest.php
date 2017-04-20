<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CustomerSegment\Test\Unit\Block\Adminhtml\Report\Customer\Segment;

use Magento\CustomerSegment\Block\Adminhtml\Report\Customer\Segment\Detail;

class DetailTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Detail
     */
    protected $model;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\CustomerSegment\Model\Segment
     */
    protected $segment;

    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;

    /**
     * @var \Magento\Backend\Block\Widget\Button\ButtonList
     */
    protected $buttonList;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Backend\Block\Widget\Context
     */
    protected $context;

    protected function setUp()
    {
        $this->segment = $this->getMock('Magento\CustomerSegment\Model\Segment', [], [], '', false);

        $this->registry = $this->getMock('Magento\Framework\Registry', [], [], '', false);
        $this->registry
            ->expects($this->any())
            ->method('registry')
            ->with($this->equalTo('current_customer_segment'))
            ->will($this->returnValue($this->segment));

        $this->urlBuilder = $this->getMockForAbstractClass('Magento\Framework\UrlInterface', [], '', false);
        $this->layout = $this->getMock('Magento\Framework\View\LayoutInterface');
        $this->storeManager = $this->getMock('Magento\Store\Model\StoreManagerInterface');

        $this->buttonList = $this->getMock('Magento\Backend\Block\Widget\Button\ButtonList', [], [], '', false);
        $this->buttonList
            ->expects($this->any())
            ->method('add')
            ->will($this->returnSelf());

        $this->context = $this->getMockBuilder('Magento\Backend\Block\Widget\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $this->context
            ->expects($this->once())
            ->method('getUrlBuilder')
            ->will($this->returnValue($this->urlBuilder));
        $this->context
            ->expects($this->once())
            ->method('getLayout')
            ->will($this->returnValue($this->layout));
        $this->context
            ->expects($this->once())
            ->method('getButtonList')
            ->will($this->returnValue($this->buttonList));
        $this->context
            ->expects($this->once())
            ->method('getStoreManager')
            ->will($this->returnValue($this->storeManager));

        $this->model = new \Magento\CustomerSegment\Block\Adminhtml\Report\Customer\Segment\Detail(
            $this->context,
            $this->registry
        );
    }

    protected function tearDown()
    {
        unset(
            $this->model,
            $this->segment,
            $this->registry,
            $this->urlBuilder,
            $this->layout,
            $this->storeManager,
            $this->buttonList,
            $this->context
        );
    }

    public function testGetRefreshUrl()
    {
        $this->urlBuilder
            ->expects($this->once())
            ->method('getUrl')
            ->with('customersegment/*/refresh', ['_current' => true])
            ->willReturn('http://some_url');

        $this->assertContains('http://some_url', (string)$this->model->getRefreshUrl());
    }

    public function testGetBackUrl()
    {
        $this->urlBuilder
            ->expects($this->once())
            ->method('getUrl')
            ->with('customersegment/*/segment')
            ->willReturn('http://some_url');

        $this->assertContains('http://some_url', (string)$this->model->getBackUrl());
    }

    public function testGetCustomerSegment()
    {
        $result = $this->model->getCustomerSegment();

        $this->assertInstanceOf('Magento\CustomerSegment\Model\Segment', $result);
        $this->assertEquals($this->segment, $result);
    }

    public function testGetWebsites()
    {
        $data = [
            1 => 'website_1',
            2 => 'website_2',
        ];

        $this->storeManager
            ->expects($this->once())
            ->method('getWebsites')
            ->willReturn($data);

        $result = $this->model->getWebsites();

        $this->assertTrue(is_array($result));
        $this->assertEquals($data, $result);
    }
}
