<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ForeignKey\Test\Unit\Strategy;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class DbIgnoreTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $connectionMock;

    /**
     * @var \Magento\Framework\ForeignKey\Strategy\Cascade
     */
    protected $strategy;

    protected function setUp()
    {
        $this->connectionMock = $this->getMock('\Magento\Framework\DB\Adapter\AdapterInterface');
        $objectManager = new ObjectManager($this);
        $this->strategy = $objectManager->getObject('Magento\Framework\ForeignKey\Strategy\DbIgnore');
    }

    public function testProcess()
    {
        $constraintMock = $this->getMock('\Magento\Framework\ForeignKey\ConstraintInterface');
        $this->connectionMock->expects($this->never())->method('delete');
        $this->strategy->process($this->connectionMock, $constraintMock, 'cond1');
    }

    public function testLockAffectedData()
    {
        $table = 'sampleTable';
        $condition = 'sampleCondition';
        $fields = [3, 75, 56, 67];

        $this->connectionMock->expects($this->never())->method('select');
        $this->connectionMock->expects($this->never())->method('fetchAssoc');
        $result = $this->strategy->lockAffectedData($this->connectionMock, $table, $condition, $fields);
        $this->assertEquals([], $result);
    }
}
