<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Rma\Test\Unit\Model;

class AttributeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Rma\Model\Attribute
     */
    protected $rmaAttribute;

    /**
     * @var \Magento\Store\Model\StoreManager|\PHPUnit_Framework_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \Magento\Rma\Model\ResourceModel\Item\Attribute|\PHPUnit_Framework_MockObject
     */
    protected $getResourceMock;

    /**
     * @var \Magento\Store\Model\Website|\PHPUnit_Framework_MockObject
     */
    protected $websiteMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Eav\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eavConfigMock;

    protected function setUp()
    {
        $this->objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->storeManagerMock = $this->getMock('Magento\Store\Model\StoreManager', ['getWebsite'], [], '', false);
        $this->getResourceMock = $this->getMock(
            'Magento\Rma\Model\ResourceModel\Item\Attribute',
            ['getUsedInForms', 'getIdFieldName', '__wakeup'],
            [],
            '',
            false
        );
        $this->eavConfigMock = $this->getMockBuilder('Magento\Eav\Model\Config')
            ->setMethods(['clear'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->rmaAttribute = $this->objectManagerHelper->getObject(
            'Magento\Rma\Model\Attribute',
            [
                'storeManager' => $this->storeManagerMock,
                'resource' => $this->getResourceMock,
                'eavConfig' => $this->eavConfigMock
            ]
        );
    }

    public function testSetWebsite()
    {
        $this->storeManagerMock->expects($this->once())->method('getWebsite')->with(12);
        $this->assertEquals($this->rmaAttribute, $this->rmaAttribute->setWebsite(12));
    }

    public function testGetWebsite()
    {
        $this->storeManagerMock->expects($this->once())
            ->method('getWebsite')
            ->will($this->returnValue($this->websiteMock));
        $this->assertEquals($this->websiteMock, $this->rmaAttribute->getWebsite());
    }

    public function testGetUsedInForms()
    {
        $this->getResourceMock->expects($this->once())
            ->method('getUsedInForms')
            ->with($this->rmaAttribute)
            ->will($this->returnValue('test_value'));
        $this->assertEquals('test_value', $this->rmaAttribute->getUsedInForms());
    }

    /**
     * @dataProvider getValidateRulesDataProvider
     * @param array $data
     */
    public function testGetValidateRules(array $data)
    {
        $rmaAttribute = $this->objectManagerHelper->getObject('Magento\Rma\Model\Attribute', ['data' => $data]);
        if (empty($data)) {
            $this->assertEmpty($rmaAttribute->getValidateRules());
        } else {
            $this->assertNotEmpty($rmaAttribute->getValidateRules());
        }
    }

    /**
     * @dataProvider setValidateRulesDataProvider
     * @param array|string $rules
     */
    public function testSetValidateRules($rules)
    {
        $this->assertEquals($this->rmaAttribute, $this->rmaAttribute->setValidateRules($rules));
    }

    /**
     * @dataProvider getIsRequiredDataProvider
     * @param array $data
     */
    public function testGetIsRequired($data)
    {
        $rmaAttribute = $this->objectManagerHelper->getObject('Magento\Rma\Model\Attribute', ['data' => $data]);
        $this->assertEquals(1, $rmaAttribute->getIsRequired());
    }

    /**
     * @dataProvider getIsVisibleDataProvider
     * @param array $data
     */
    public function testGetIsVisible($data)
    {
        $rmaAttribute = $this->objectManagerHelper->getObject('Magento\Rma\Model\Attribute', ['data' => $data]);
        $this->assertEquals(1, $rmaAttribute->getIsVisible());
    }

    /**
     * @dataProvider getMultilineCountDataProvider
     * @param array $data
     */
    public function testGetMultilineCount($data)
    {
        $rmaAttribute = $this->objectManagerHelper->getObject('Magento\Rma\Model\Attribute', ['data' => $data]);
        $this->assertEquals(1, $rmaAttribute->getMultilineCount());
    }

    public function getValidateRulesDataProvider()
    {
        $serialize = serialize(['test-key' => 'test-value']);
        return [
            [
                'data' => [
                    'validate_rules' => [
                        'key' => 'value',
                    ],
                ],
            ],
            [
                'data' => [
                    'validate_rules' => $serialize,
                ]
            ],
            [
                'data' => []
            ]
        ];
    }

    public function setValidateRulesDataProvider()
    {
        return [
            [
                'rules' => [
                    'validate_rules' => [
                        'key' => 'value',
                    ],
                ],
            ],
            [
                'rules' => ''
            ]
        ];
    }

    public function getIsRequiredDataProvider()
    {
        return [
            [
                'data' => [
                    'is_required' => 1,
                ],
            ],
            [
                'data' => [
                    'scope_is_required' => 1,
                ]
            ]
        ];
    }

    public function getIsVisibleDataProvider()
    {
        return [
            [
                'data' => [
                    'is_visible' => 1,
                ],
            ],
            [
                'data' => [
                    'scope_is_visible' => 1,
                ]
            ]
        ];
    }

    public function getDefaultValueDataProvider()
    {
        return [
            [
                'data' => [
                    'default_value' => 1,
                ],
            ],
            [
                'data' => [
                    'scope_default_value' => 1,
                ]
            ]
        ];
    }

    public function getMultilineCountDataProvider()
    {
        return [
            [
                'data' => [
                    'multiline_count' => 1,
                ],
            ],
            [
                'data' => [
                    'scope_multiline_count' => 1,
                ]
            ]
        ];
    }

    public function testAfterSaveEavCache()
    {
        $this->eavConfigMock
            ->expects($this->once())
            ->method('clear');
        $this->rmaAttribute->afterSave();
    }

    public function testAfterDeleteEavCache()
    {
        $this->eavConfigMock
            ->expects($this->once())
            ->method('clear');
        $this->rmaAttribute->afterDelete();
    }
}
