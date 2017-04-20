<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogEvent\Test\Unit\Model\ResourceModel\Event\Grid;

use Magento\CatalogEvent\Model\ResourceModel\Event\Grid\Statuses;
use Magento\Framework\Phrase;

/**
 * Unit test for Magento\CatalogEvent\Model\ResourceModel\Event\Grid\Statuses
 */
class StatusesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogEvent\Model\ResourceModel\Event\Grid\Statuses
     */
    protected $statuses;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->statuses = new Statuses();
    }

    /**
     * @return void
     */
    public function testToOptionArray()
    {
        foreach ($this->statuses->toOptionArray() as $item) {
            $this->assertTrue($item instanceof Phrase || is_string($item));
        }
    }
}
