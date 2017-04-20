<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CustomerFinance\Test\Unit\Model\Import\Eav\Customer;

use Magento\CustomerFinance\Model\ResourceModel\Customer\Attribute\Finance\Collection;
use Magento\CustomerImportExport\Model\Import\Address;
use Magento\ImportExport\Model\Import\AbstractEntity;
use \Magento\CustomerFinance\Model\Import\Eav\Customer\Finance;

/**
 * Test class for \Magento\CustomerFinance\Model\Import\Eav\Customer\Finance
 */
class FinanceTest extends \Magento\ImportExport\Test\Unit\Model\Import\AbstractImportTestCase
{
    /**
     * Customer financial data export model
     *
     * @var \Magento\CustomerFinance\Model\Import\Eav\Customer\Finance
     */
    protected $_model;

    /**
     * Bunch counter for getNextBunch() stub method
     *
     * @var int
     */
    protected $_bunchNumber;

    /**
     * @var \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface
     */
    protected $errorAggregator;

    /**
     * Websites array (website id => code)
     *
     * @var array
     */
    protected $_websites = [
        \Magento\Store\Model\Store::DEFAULT_STORE_ID => 'admin',
        1 => 'website1',
        2 => 'website2',
    ];

    /**
     * Customers array
     *
     * @var array
     */
    protected $_customers = [
        ['entity_id' => 1, 'email' => 'test1@email.com', 'website_id' => 1],
        ['entity_id' => 2, 'email' => 'test2@email.com', 'website_id' => 2],
    ];

    /**
     * Attributes array
     *
     * @var array
     */
    protected $_attributes = [
        [
            'id' => 1,
            'attribute_code' => Collection::COLUMN_CUSTOMER_BALANCE,
            'frontend_label' => 'Store Credit',
            'backend_type' => 'decimal',
            'is_required' => true,
        ],
        [
            'id' => 2,
            'attribute_code' => Collection::COLUMN_REWARD_POINTS,
            'frontend_label' => 'Reward Points',
            'backend_type' => 'int',
            'is_required' => false
        ],
    ];

    /**
     * Input data
     *
     * @var array
     */
    protected $_inputData = [
        [
            Finance::COLUMN_EMAIL => 'test1@email.com',
            Finance::COLUMN_WEBSITE => 'website1',
            Finance::COLUMN_FINANCE_WEBSITE => 'website1',
            AbstractEntity::COLUMN_ACTION => null,
            Address::COLUMN_ADDRESS_ID => 1,
            Collection::COLUMN_CUSTOMER_BALANCE => 100,
            Collection::COLUMN_REWARD_POINTS => 200,
        ],
        [
            Finance::COLUMN_EMAIL => 'test2@email.com',
            Finance::COLUMN_WEBSITE => 'website2',
            Finance::COLUMN_FINANCE_WEBSITE => 'website1',
            AbstractEntity::COLUMN_ACTION => AbstractEntity::COLUMN_ACTION_VALUE_DELETE,
            Address::COLUMN_ADDRESS_ID => 2
        ],
        [
            Finance::COLUMN_EMAIL => 'test2@email.com',
            Finance::COLUMN_WEBSITE => 'website2',
            Finance::COLUMN_FINANCE_WEBSITE => 'website1',
            AbstractEntity::COLUMN_ACTION => 'update',
            Address::COLUMN_ADDRESS_ID => 2,
            Collection::COLUMN_CUSTOMER_BALANCE => 100,
            Collection::COLUMN_REWARD_POINTS => 200
        ],
    ];

    /**
     * Init entity adapter model
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        parent::setUp();

        $this->_bunchNumber = 0;
        if ($this->getName() == 'testImportDataCustomBehavior') {
            $dependencies = $this->_getModelDependencies(true);
        } else {
            $dependencies = $this->_getModelDependencies();
        }

        $moduleHelper = $this->getMock(
            'Magento\CustomerFinance\Helper\Data',
            ['isRewardPointsEnabled', 'isCustomerBalanceEnabled'],
            [],
            '',
            false
        );
        $moduleHelper->expects($this->any())
            ->method('__')
            ->will($this->returnArgument(0));
        $moduleHelper->expects($this->any())
            ->method('isRewardPointsEnabled')
            ->will($this->returnValue(true));
        $moduleHelper->expects($this->any())
            ->method('isCustomerBalanceEnabled')
            ->will($this->returnValue(true));

        $customerFactory = $this->getMock(
            'Magento\Customer\Model\CustomerFactory',
            ['create'],
            [],
            '',
            false
        );
        $balanceFactory = $this->getMock(
            'Magento\CustomerBalance\Model\BalanceFactory',
            ['create'],
            [],
            '',
            false
        );
        $rewardFactory = $this->getMock('Magento\Reward\Model\RewardFactory', ['create'], [], '', false);

        $customerFactory->expects(
            $this->any()
        )
            ->method(
                'create'
            )
            ->will(
                $this->returnValue($this->getModelInstance('Magento\Customer\Model\Customer'))
            );
        $balanceFactory->expects(
            $this->any()
        )
            ->method(
                'create'
            )
            ->will(
                $this->returnValue($this->getModelInstance('Magento\CustomerBalance\Model\Balance'))
            );
        $rewardFactory->expects(
            $this->any()
        )
            ->method(
                'create'
            )
            ->will(
                $this->returnValue($this->getModelInstance('Magento\Reward\Model\Reward'))
            );

        $scopeConfig = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');

        $adminUser = $this->getMock('stdClass', ['getUsername']);
        $adminUser->expects($this->any())
            ->method('getUsername')
            ->will($this->returnValue('admin'));
        $authSession = $this->getMock('Magento\Backend\Model\Auth\Session', ['getUser'], [], '', false);
        $authSession->expects($this->once())
            ->method('getUser')
            ->will($this->returnValue($adminUser));

        $storeManager = $this->getMock('\Magento\Store\Model\StoreManager', ['getWebsites'], [], '', false);
        $storeManager->expects(
            $this->once()
        )
            ->method(
                'getWebsites'
            )
            ->will(
                $this->returnCallback([$this, 'getWebsites'])
            );

        $this->errorAggregator = $this->getErrorAggregatorObject();

        $this->_model = new \Magento\CustomerFinance\Model\Import\Eav\Customer\Finance(
            new \Magento\Framework\Stdlib\StringUtils(),
            $scopeConfig,
            $this->getMock('Magento\ImportExport\Model\ImportFactory', ['create'], [], '', false),
            $this->getMock('Magento\ImportExport\Model\ResourceModel\Helper', [], [], '', false),
            $this->getMock('Magento\Framework\App\ResourceConnection', [], [], '', false),
            $this->errorAggregator,
            $storeManager,
            $this->getMock('Magento\ImportExport\Model\Export\Factory', ['create'], [], '', false),
            $this->getMock('Magento\Eav\Model\Config', [], [], '', false),
            $this->getMock(
                'Magento\CustomerImportExport\Model\ResourceModel\Import\Customer\StorageFactory',
                ['create'],
                [],
                '',
                false
            ),
            $authSession,
            $moduleHelper,
            $customerFactory,
            $balanceFactory,
            $rewardFactory,
            $dependencies
        );
    }

    /**
     * Unset entity adapter model
     */
    protected function tearDown()
    {
        unset($this->_model);
        unset($this->_bunchNumber);
    }

    /**
     * Create mocks for all $this->_model dependencies
     *
     * @param bool $addData
     * @return array
     */
    protected function _getModelDependencies($addData = false)
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $dataSourceModel = $this->getMock('stdClass', ['getNextBunch']);
        if ($addData) {
            $dataSourceModel->expects(
                $this->exactly(2)
            )
                ->method(
                    'getNextBunch'
                )
                ->will(
                    $this->returnCallback([$this, 'getNextBunch'])
                );
        }

        $connection = $this->getMock('stdClass');

        /** @var $customerStorage \Magento\CustomerImportExport\Model\ResourceModel\Import\Customer\Storage */
        $customerStorage = $this->getMock(
            'Magento\CustomerImportExport\Model\ResourceModel\Import\Customer\Storage',
            ['load'],
            [],
            '',
            false
        );
        $customerResource = $this->getMock(
            'Magento\Customer\Model\ResourceModel\Customer',
            ['getIdFieldName'],
            [],
            '',
            false
        );
        $customerResource->expects($this->any())
            ->method('getIdFieldName')
            ->will($this->returnValue('entity_id'));
        foreach ($this->_customers as $customerData) {
            /** @var $customer \Magento\Customer\Model\Customer */
            $arguments = $objectManagerHelper->getConstructArguments('Magento\Customer\Model\Customer');
            $arguments['resource'] = $customerResource;
            $arguments['data'] = $customerData;
            $customer = $this->getMock('Magento\Customer\Model\Customer', ['_construct'], $arguments);
            $customerStorage->addCustomer($customer);
        }

        $objectFactory = $this->getMock('stdClass', ['getModelInstance']);
        $objectFactory->expects(
            $this->any()
        )
            ->method(
                'getModelInstance'
            )
            ->will(
                $this->returnCallback([$this, 'getModelInstance'])
            );

        /** @var $attributeCollection \Magento\Framework\Data\Collection */
        $attributeCollection = $this->getMock(
            'Magento\Framework\Data\Collection',
            ['getEntityTypeCode'],
            [$this->getMock('Magento\Framework\Data\Collection\EntityFactory', [], [], '', false)]
        );
        foreach ($this->_attributes as $attributeData) {
            /** @var $attribute \Magento\Eav\Model\Entity\Attribute\AbstractAttribute */
            $arguments = $objectManagerHelper->getConstructArguments(
                'Magento\Eav\Model\Entity\Attribute\AbstractAttribute',
                ['eavTypeFactory' => $this->getMock('Magento\Eav\Model\Entity\TypeFactory', ['create'], [], '', false)]
            );
            $arguments['data'] = $attributeData;
            $attribute = $this->getMockForAbstractClass(
                'Magento\Eav\Model\Entity\Attribute\AbstractAttribute',
                $arguments,
                '',
                true,
                true,
                true,
                ['_construct']
            );
            $attributeCollection->addItem($attribute);
        }

        $data = [
            'data_source_model' => $dataSourceModel,
            'connection' => $connection,
            'json_helper' => 'not_used',
            'page_size' => 1,
            'max_data_size' => 1,
            'bunch_size' => 1,
            'entity_type_id' => 1,
            'customer_storage' => $customerStorage,
            'object_factory' => $objectFactory,
            'attribute_collection' => $attributeCollection,
        ];

        return $data;
    }

    /**
     * Stub for next bunch of validated rows getter. It is callback function which is used to emulate work of data
     * source model. It should return data on first call and null on next call to emulate end of bunch.
     *
     * @return array|null
     */
    public function getNextBunch()
    {
        if ($this->_bunchNumber == 0) {
            $data = $this->_inputData;
        } else {
            $data = null;
        }
        $this->_bunchNumber++;

        return $data;
    }

    /**
     * Iterate stub
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param \Magento\Framework\Data\Collection $collection
     * @param int $pageSize
     * @param array $callbacks
     */
    public function iterate(\Magento\Framework\Data\Collection $collection, $pageSize, array $callbacks)
    {
        foreach ($collection as $customer) {
            foreach ($callbacks as $callback) {
                call_user_func($callback, $customer);
            }
        }
    }

    /**
     * Get websites stub
     *
     * @param bool $withDefault
     * @return array
     */
    public function getWebsites($withDefault = false)
    {
        $websites = [];
        if (!$withDefault) {
            unset($websites[0]);
        }
        foreach ($this->_websites as $id => $code) {
            if (!$withDefault && $id == \Magento\Store\Model\Store::DEFAULT_STORE_ID) {
                continue;
            }
            $websiteData = ['id' => $id, 'code' => $code];
            $websites[$id] = new \Magento\Framework\DataObject($websiteData);
        }

        return $websites;
    }

    /**
     * Callback method for mock object
     *
     * @param string $modelClass
     * @param array|object $constructArguments
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getModelInstance($modelClass = '', $constructArguments = [])
    {
        switch ($modelClass) {
            case 'Magento\CustomerBalance\Model\Balance':
                $instance = $this->getMock(
                    $modelClass,
                    [
                        'setCustomer',
                        'setWebsiteId',
                        'loadByCustomer',
                        'getAmount',
                        'setAmountDelta',
                        'setComment',
                        'save',
                        '__wakeup'
                    ],
                    $constructArguments,
                    '',
                    false
                );
                $instance->expects($this->any())
                    ->method('setCustomer')
                    ->will($this->returnSelf());
                $instance->expects($this->any())
                    ->method('setWebsiteId')
                    ->will($this->returnSelf());
                $instance->expects($this->any())
                    ->method('loadByCustomer')
                    ->will($this->returnSelf());
                $instance->expects($this->any())
                    ->method('getAmount')
                    ->will($this->returnValue(0));
                $instance->expects($this->any())
                    ->method('setAmountDelta')
                    ->will($this->returnSelf());
                $instance->expects($this->any())
                    ->method('setComment')
                    ->will($this->returnSelf());
                $instance->expects($this->any())
                    ->method('save')
                    ->will($this->returnSelf());
                break;
            case 'Magento\Reward\Model\Reward':
                $instance = $this->getMock(
                    $modelClass,
                    [
                        'setCustomer',
                        'setWebsiteId',
                        'loadByCustomer',
                        'getPointsBalance',
                        'setPointsDelta',
                        'setAction',
                        'setComment',
                        'updateRewardPoints',
                        '__wakeup'
                    ],
                    $constructArguments,
                    '',
                    false
                );
                $instance->expects($this->any())
                    ->method('setCustomer')
                    ->will($this->returnSelf());
                $instance->expects($this->any())
                    ->method('setWebsiteId')
                    ->will($this->returnSelf());
                $instance->expects($this->any())
                    ->method('loadByCustomer')
                    ->will($this->returnSelf());
                $instance->expects($this->any())
                    ->method('getPointsBalance')
                    ->will($this->returnValue(0));
                $instance->expects($this->any())
                    ->method('setPointsDelta')
                    ->will($this->returnSelf());
                $instance->expects($this->any())
                    ->method('setAction')
                    ->will($this->returnSelf());
                $instance->expects($this->any())
                    ->method('setComment')
                    ->will($this->returnSelf());
                $instance->expects($this->any())
                    ->method('updateRewardPoints')
                    ->will($this->returnSelf());
                break;
            default:
                $instance = $this->getMock($modelClass, [], $constructArguments, '', false);
                break;
        }
        return $instance;
    }

    /**
     * Data provider of row data and errors
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return array
     */
    public function validateRowDataProvider()
    {
        return [
            'valid' => [
                '$rowData' => include __DIR__ . '/_files/row_data_valid.php',
                '$behaviors' => [
                    \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE => true,
                    \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE => true,
                ],
            ],
            'no website' => [
                '$rowData' => include __DIR__ . '/_files/row_data_no_website.php',
                '$behaviors' => [
                    \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE => false,
                    \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE => false,
                ],
            ],
            'empty website' => [
                '$rowData' => include __DIR__ . '/_files/row_data_empty_website.php',
                '$behaviors' => [
                    \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE => false,
                    \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE => false,
                ],
            ],
            'no email' => [
                '$rowData' => include __DIR__ . '/_files/row_data_no_email.php',
                '$behaviors' => [
                    \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE => false,
                    \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE => false,
                ],
            ],
            'empty email' => [
                '$rowData' => include __DIR__ . '/_files/row_data_empty_email.php',
                '$behaviors' => [
                    \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE => false,
                    \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE => false,
                ],
            ],
            'empty finance website' => [
                '$rowData' => include __DIR__ . '/_files/row_data_empty_finance_website.php',
                '$behaviors' => [
                    \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE => false,
                    \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE => false,
                ],
            ],
            'invalid email' => [
                '$rowData' => include __DIR__ . '/_files/row_data_invalid_email.php',
                '$behaviors' => [
                    \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE => false,
                    \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE => false,
                ],
            ],
            'invalid website' => [
                '$rowData' => include __DIR__ . '/_files/row_data_invalid_website.php',
                '$behaviors' => [
                    \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE => false,
                    \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE => false,
                ],
            ],
            'invalid finance website' => [
                '$rowData' => include __DIR__ . '/_files/row_data_invalid_finance_website.php',
                '$behaviors' => [
                    \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE => false,
                    \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE => false,
                ],
            ],
            'invalid finance website (admin)' => [
                '$rowData' => include __DIR__ . '/_files/row_data_invalid_finance_website_admin.php',
                '$behaviors' => [
                    \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE => false,
                    \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE => false,
                ],
            ],
            'no customer' => [
                '$rowData' => include __DIR__ . '/_files/row_data_no_customer.php',
                '$behaviors' => [
                    \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE => false,
                    \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE => false,
                ],
            ],
            'invalid_attribute_value' => [
                '$rowData' => include __DIR__ . '/_files/row_data_invalid_attribute_value.php',
                '$behaviors' => [
                    \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE => false,
                    \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE => true,
                ],
            ],
            'empty_optional_attribute_value' => [
                '$rowData' => include __DIR__ . '/_files/row_data_empty_optional_attribute_value.php',
                '$behaviors' => [
                    \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE => true,
                    \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE => true,
                ],
            ],
            'empty_required_attribute_value' => [
                '$rowData' => include __DIR__ . '/_files/row_data_empty_required_attribute_value.php',
                '$behaviors' => [
                    \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE => false,
                    \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE => true,
                ],
            ]
        ];
    }

    /**
     * Test Finance::validateRow()
     * with different values in case when add/update behavior is performed
     *
     * @covers \Magento\CustomerFinance\Model\Import\Eav\Customer\Finance::_validateRowForUpdate
     * @dataProvider validateRowDataProvider
     *
     * @param array $rowData
     * @param array $behaviors
     */
    public function testValidateRowForUpdate(array $rowData, array $behaviors)
    {
        $behavior = \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE;

        $this->_model->setParameters(['behavior' => $behavior]);

        $this->assertEquals($behaviors[$behavior], $this->_model->validateRow($rowData, 0));
    }

    /**
     * Test Finance::validateRow()
     * with 2 rows with identical PKs in case when add/update behavior is performed
     *
     * @covers \Magento\CustomerFinance\Model\Import\Eav\Customer\Finance::_validateRowForUpdate
     */
    public function testValidateRowForUpdateDuplicateRows()
    {
        $behavior = \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE;

        $this->_model->setParameters(['behavior' => $behavior]);

        $secondRow = $firstRow = [
            '_website' => 'website1',
            '_email' => 'test1@email.com',
            '_finance_website' => 'website2',
            'store_credit' => 10.5,
            'reward_points' => 5,
        ];
        $secondRow['store_credit'] = 20;
        $secondRow['reward_points'] = 30;

        $this->assertTrue($this->_model->validateRow($firstRow, 0));
        $this->assertFalse($this->_model->validateRow($secondRow, 1));
    }

    /**
     * Test Finance::validateRow()
     * with different values in case when delete behavior is performed
     *
     * @covers \Magento\CustomerFinance\Model\Import\Eav\Customer\Finance::_validateRowForDelete
     * @dataProvider validateRowDataProvider
     *
     * @param array $rowData
     * @param array $behaviors
     */
    public function testValidateRowForDelete(array $rowData, array $behaviors)
    {
        $behavior = \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE;

        $this->_model->setParameters(['behavior' => $behavior]);

        $this->assertEquals($behaviors[$behavior], $this->_model->validateRow($rowData, 0));
    }

    /**
     * Test entity type code getter
     *
     * @covers \Magento\CustomerFinance\Model\Import\Eav\Customer\Finance::getEntityTypeCode
     */
    public function testGetEntityTypeCode()
    {
        $this->assertEquals('customer_finance', $this->_model->getEntityTypeCode());
    }

    /**
     * Test data import
     *
     * @covers \Magento\CustomerFinance\Model\Import\Eav\Customer\Finance::importData
     */
    public function testImportDataCustomBehavior()
    {
        $this->assertTrue($this->_model->importData());
    }
}
