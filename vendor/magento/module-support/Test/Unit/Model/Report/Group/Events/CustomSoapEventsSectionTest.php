<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Support\Test\Unit\Model\Report\Group\Events;

use Magento\Framework\App\Area;
use Magento\Framework\Event\ConfigInterface;

class CustomSoapEventsSectionTest extends AbstractEventsSectionTest
{
    /**
     * {@inheritdoc}
     */
    protected function getExpectedTitle()
    {
        return (string)__('Custom SOAP Events');
    }

    /**
     * {@inheritdoc}
     */
    protected function getExpectedType()
    {
        return ConfigInterface::TYPE_CUSTOM;
    }

    /**
     * {@inheritdoc}
     */
    protected function getExpectedAreaCode()
    {
        return Area::AREA_WEBAPI_SOAP;
    }

    /**
     * {@inheritdoc}
     */
    protected function getSectionName()
    {
        return 'Magento\Support\Model\Report\Group\Events\CustomSoapEventsSection';
    }
}
