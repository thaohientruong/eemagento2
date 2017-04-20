<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Invitation\Test\Unit\Model\Invitation;

class StatusTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param bool $isAdmin
     * @param string[] $statuses
     * @return void
     * @dataProvider dataProviderGetCanBeSentStatuses
     */
    public function testGetCanBeSentStatuses($isAdmin, $statuses)
    {
        $model = new \Magento\Invitation\Model\Invitation\Status($isAdmin);
        $this->assertEquals($statuses, $model->getCanBeSentStatuses());
    }

    /**
     * @return array
     */
    public function dataProviderGetCanBeSentStatuses()
    {
        return [
            [
                false,
                [
                    \Magento\Invitation\Model\Invitation\Status::STATUS_NEW,
                ],
            ],
            [
                true,
                [
                    \Magento\Invitation\Model\Invitation\Status::STATUS_NEW,
                    \Magento\Invitation\Model\Invitation\Status::STATUS_CANCELED,
                    \Magento\Invitation\Model\Invitation\Status::STATUS_SENT,
                ],
            ],
        ];
    }
}
