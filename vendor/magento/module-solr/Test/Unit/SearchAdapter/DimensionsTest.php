<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Solr\Test\Unit\SearchAdapter;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class DimensionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\ScopeResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scope;

    /**
     * @var \Magento\Framework\App\ScopeInterface
     */
    private $dimensions;

    /**
     * @var \Magento\Solr\SearchAdapter\FieldMapperInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fieldMapper;

    protected function setUp()
    {
        $helper = new ObjectManager($this);

        $this->scope = $this->getMockBuilder('Magento\Framework\App\ScopeInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->scope->expects($this->any())
            ->method('getId')
            ->willReturn(1);

        $scopeResolver = $this->getMockBuilder('Magento\Framework\App\ScopeResolverInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $scopeResolver->expects($this->any())
            ->method('getScope')
            ->willReturn($this->scope);

        $this->fieldMapper = $this->getMockBuilder('Magento\Solr\SearchAdapter\FieldMapperInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->fieldMapper->expects($this->any())->method('getFieldName')->willReturnArgument(0);

        $this->dimensions = $helper->getObject(
            'Magento\Solr\SearchAdapter\Dimensions',
            ['scopeResolver' => $scopeResolver, 'fieldMapper' => $this->fieldMapper]
        );
    }

    /**
     * @param string $dimensionName
     * @param string|int $dimensionValue
     * @param string $filterName
     * @param string $expectedQuery
     * @param string|int $expectedValue
     * @dataProvider buildDataProvider
     */
    public function testBuild($dimensionName, $dimensionValue, $filterName, $expectedQuery, $expectedValue)
    {
        /** @var \Solarium\QueryType\Select\Query\FilterQuery|\PHPUnit_Framework_MockObject_MockObject $query */
        $filterQuery = $this->getMockBuilder('Solarium\QueryType\Select\Query\FilterQuery')
            ->disableOriginalConstructor()
            ->getMock();
        $filterQuery->expects($this->any())
            ->method('setQuery')
            ->withConsecutive([$expectedQuery, [$expectedValue]]);

        /** @var \Solarium\QueryType\Select\Query\Query|\PHPUnit_Framework_MockObject_MockObject $query */
        $query = $this->getMockBuilder('Solarium\QueryType\Select\Query\Query')
            ->disableOriginalConstructor()
            ->getMock();
        $query->expects($this->any())
            ->method('createFilterQuery')
            ->withConsecutive([$filterName])
            ->willReturn($filterQuery);

        /** @var \Magento\Framework\Search\Request\Dimension|\PHPUnit_Framework_MockObject_MockObject $query */
        $dimension = $this->getMockBuilder('Magento\Framework\Search\Request\Dimension')
            ->disableOriginalConstructor()
            ->getMock();
        $dimension->expects($this->any())
            ->method('getName')
            ->willReturn($dimensionName);
        $dimension->expects($this->any())
            ->method('getValue')
            ->willReturn($dimensionValue);

        $this->dimensions->build([$dimension], $query);
    }

    public function buildDataProvider()
    {
        return [
            ['scope', 'default', 'store_id', 'store_id:%1%', 1],
            ['not_scope', 'default', 'not_scope', 'not_scope:%1%', 'default']
        ];
    }
}
