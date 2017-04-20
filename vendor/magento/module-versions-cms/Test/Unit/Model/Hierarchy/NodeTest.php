<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\VersionsCms\Test\Unit\Model\Hierarchy;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\VersionsCms\Model\Hierarchy\Node;

class NodeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\VersionsCms\Model\Hierarchy\Node
     */
    protected $node;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\VersionsCms\Model\ResourceModel\Hierarchy\Node|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $nodeResourceMock;

    /**
     * @var \Magento\VersionsCms\Helper\Hierarchy|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $hierarchyHelperMock;

    public function setUp()
    {
        $this->nodeResourceMock = $this->getMockBuilder('Magento\VersionsCms\Model\ResourceModel\Hierarchy\Node')
            ->disableOriginalConstructor()
            ->getMock();
        $this->hierarchyHelperMock = $this->getMockBuilder('Magento\VersionsCms\Helper\Hierarchy')
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->node = $this->objectManagerHelper->getObject(
            'Magento\VersionsCms\Model\Hierarchy\Node',
            [
                'resource' => $this->nodeResourceMock,
                'cmsHierarchy' => $this->hierarchyHelperMock
            ]
        );
    }

    /**
     * @param array $nodeData
     * @param array $preparedNodeData
     * @param array|null $remove
     *
     * @dataProvider collectTreeDataProvider
     */
    public function testCollectTreeSuccess(
        array $nodeData,
        array $preparedNodeData,
        array $remove = null
    ) {
        $id = 111;
        $requestUrl = 'request/url';

        $this->prepareCollectTree($nodeData, $preparedNodeData);
        $this->persistTreeSuccess($id, $requestUrl, $remove);

        $this->assertSame(
            $this->node,
            $this->node->collectTree([$nodeData], $remove)
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Please correct the node data.
     */
    public function testCollectTreeValidationFailure()
    {
        $data = [[]];
        $this->node->collectTree($data, null);
    }

    /**
     * @param array $nodeData
     * @param array $preparedNodeData
     * @param array|null $remove
     *
     * @dataProvider collectTreeDataProvider
     * @expectedException \Exception
     * @expectedExceptionMessage bad result
     */
    public function testCollectTreeDatabaseFailure(
        array $nodeData,
        array $preparedNodeData,
        array $remove = null
    ) {
        $id = 111;
        $requestUrl = 'request/url';

        $this->prepareCollectTree($nodeData, $preparedNodeData);
        $this->persistTreeFailure($id, $requestUrl, $remove);

        $this->node->collectTree([$nodeData], $remove);
    }

    /**
     * @return array
     */
    public function collectTreeDataProvider()
    {
        return [
            'data set #1' => [
                'nodeData' => [
                    'node_id' => '111',
                    'page_id' => '222',
                    'label' => 'some label',
                    'identifier' => 'identifier',
                    'level' => '8',
                    'sort_order' => '13',
                    'parent_node_id' => '333'
                ],
                'preparedNodeData' => [
                    'node_id' => 111,
                    'page_id' => 222,
                    'label' => null,
                    'identifier' => null,
                    'level' => 8,
                    'sort_order' => 13,
                    'request_url' => 'identifier',
                    'scope' => Node::NODE_SCOPE_DEFAULT,
                    'scope_id' => Node::NODE_SCOPE_DEFAULT_ID
                ],
                'remove' => null
            ],
            'data set #2' => [
                'nodeData' => [
                    'node_id' => '_111',
                    'page_id' => '',
                    'label' => 'some label',
                    'identifier' => 'identifier',
                    'level' => '8',
                    'sort_order' => '13',
                    'parent_node_id' => null
                ],
                'preparedNodeData' => [
                    'node_id' => null,
                    'page_id' => null,
                    'label' => 'some label',
                    'identifier' => 'identifier',
                    'level' => 8,
                    'sort_order' => 13,
                    'request_url' => 'identifier',
                    'scope' => Node::NODE_SCOPE_DEFAULT,
                    'scope_id' => Node::NODE_SCOPE_DEFAULT_ID
                ],
                'remove' => ['444', '555']
            ]
        ];
    }

    /**
     * @param array $nodeData
     * @param array $preparedNodeData
     */
    protected function prepareCollectTree(
        array $nodeData,
        array $preparedNodeData
    ) {
        $this->hierarchyHelperMock->expects($this->any())
            ->method('copyMetaData')
            ->with($nodeData, $preparedNodeData)
            ->willReturn($preparedNodeData);
    }

    /**
     * @param int $id
     * @param string $requestUrl
     * @param array|null $remove
     */
    protected function persistTreeSuccess(
        $id,
        $requestUrl,
        array $remove = null
    ) {
        $this->preparePersistTree($id, $requestUrl, $remove);
        $this->nodeResourceMock->expects($this->any())
            ->method('save')
            ->willReturnSelf();
        $this->nodeResourceMock->expects($this->once())
            ->method('addEmptyNode')
            ->with(Node::NODE_SCOPE_DEFAULT, Node::NODE_SCOPE_DEFAULT_ID)
            ->willReturnSelf();
        $this->nodeResourceMock->expects($this->once())
            ->method('commit')
            ->willReturnSelf();
    }

    /**
     * @param int $id
     * @param string $requestUrl
     * @param array|null $remove
     */
    protected function persistTreeFailure(
        $id,
        $requestUrl,
        array $remove = null
    ) {
        $this->preparePersistTree($id, $requestUrl, $remove);
        $this->nodeResourceMock->expects($this->any())
            ->method('save')
            ->willReturnSelf();
        $this->nodeResourceMock->expects($this->once())
            ->method('addEmptyNode')
            ->with(Node::NODE_SCOPE_DEFAULT, Node::NODE_SCOPE_DEFAULT_ID)
            ->willThrowException(new \Exception('bad result'));
        $this->nodeResourceMock->expects($this->never())
            ->method('commit')
            ->willReturnSelf();
        $this->nodeResourceMock->expects($this->once())
            ->method('rollback')
            ->willReturnSelf();
    }

    /**
     * @param int $id
     * @param string $requestUrl
     * @param array|null $remove
     */
    protected function preparePersistTree($id, $requestUrl, array $remove = null)
    {
        $this->node->setData(Node::NODE_ID, $id);
        $this->node->setData(Node::REQUEST_URL, $requestUrl);

        $this->nodeResourceMock->expects($this->once())
            ->method('beginTransaction')
            ->willReturnSelf();
        $this->nodeResourceMock->expects($this->any())
            ->method('dropNodes')
            ->with($remove)
            ->willReturnSelf();
    }
}
