<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogPermissions\Test\Unit\Model\Adminhtml\System\Config\Source\Grant;

use Magento\CatalogPermissions\Model\Adminhtml\System\Config\Source\Grant\Landing;
use Magento\Framework\Phrase;

/**
 * Unit test for Magento\CatalogPermissions\Model\Adminhtml\System\Config\Source\Grant\Landing
 */
class LandingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogPermissions\Model\Adminhtml\System\Config\Source\Grant\Landing
     */
    protected $landing;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->landing = new Landing();
    }

    /**
     * @return void
     */
    public function testToOptionArray()
    {
        foreach ($this->landing->toOptionArray() as $item) {
            $this->assertTrue($item instanceof Phrase || is_string($item));
        }
    }
}
