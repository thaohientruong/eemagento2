<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reminder\Test\Unit\Cron;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ScheduledNotificationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Reminder\Cron\ScheduledNotification
     */
    private $model;

    /**
     * @var \Magento\Reminder\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    private $reminderData;

    /**
     * @var \Magento\Reminder\Model\RuleFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $ruleFactory;

    /**
     * @var \Magento\Reminder\Model\Rule|\PHPUnit_Framework_MockObject_MockObject
     */
    private $rule;

    /**
     * @return void
     */
    protected function setUp()
    {
        $helper = new ObjectManager($this);

        $this->reminderData = $this->getMockBuilder('Magento\Reminder\Helper\Data')
            ->setMethods(['isEnabled'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->rule = $this->getMockBuilder('Magento\Reminder\Model\Rule')
            ->setMethods(['sendReminderEmails', '__wakeup', 'detachSalesRule'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->ruleFactory = $this->getMockBuilder('Magento\Reminder\Model\RuleFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->ruleFactory->expects($this->any())->method('create')->will($this->returnValue($this->rule));

        $this->model = $helper->getObject(
            'Magento\Reminder\Cron\ScheduledNotification',
            ['reminderData' => $this->reminderData, 'ruleFactory' => $this->ruleFactory]
        );
    }

    /**
     * @return void
     */
    public function testScheduledNotification()
    {
        $this->reminderData->expects($this->once())->method('isEnabled')->will($this->returnValue(true));

        $this->rule->expects($this->once())->method('sendReminderEmails');

        $this->model->execute();
    }

    /**
     * @return void
     */
    public function testScheduledNotificationDisabled()
    {
        $this->reminderData->expects($this->once())->method('isEnabled')->will($this->returnValue(false));

        $this->model->execute();
    }
}
