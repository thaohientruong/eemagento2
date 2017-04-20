<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\AdvancedCheckout\Test\Unit\Model\ResourceModel\Sku\Errors\Grid;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testLoadData()
    {
        $productId = '3';
        $websiteId = '1';
        $sku = 'my sku';
        $typeId = 'giftcard';

        $cart = $this->getCartMock($productId, $websiteId, $sku);
        $product = $this->getProductMock($typeId);
        $priceCurrencyMock = $this->getPriceCurrencyMock();
        $entity = $this->getEntityFactoryMock();
        $stockStatusMock = $this->getMockBuilder('Magento\CatalogInventory\Api\Data\StockStatusInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $registryMock = $this->getMockBuilder('Magento\CatalogInventory\Api\StockRegistryInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $registryMock->expects($this->any())
            ->method('getStockStatus')
            ->withAnyParameters()
            ->willReturn($stockStatusMock);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $collection = $objectManager->getObject(
            'Magento\AdvancedCheckout\Model\ResourceModel\Sku\Errors\Grid\Collection',
            [
                'entityFactory' => $entity,
                'cart' => $cart,
                'productModel' => $product,
                'priceCurrency' => $priceCurrencyMock,
                'stockRegistry' => $registryMock
            ]
        );
        $collection->loadData();

        foreach ($collection->getItems() as $item) {
            $product = $item->getProduct();
            if ($item->getCode() != 'failed_sku') {
                $this->assertEquals($typeId, $product->getTypeId());
                $this->assertEquals('10.00', $item->getPrice());
            }
        }
    }

    /**
     * Return cart mock instance
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\AdvancedCheckout\Model\Cart
     */
    protected function getCartMock($productId, $storeId, $sku)
    {
        $cartMock = $this->getMockBuilder(
            'Magento\AdvancedCheckout\Model\Cart'
        )->disableOriginalConstructor()->setMethods(
            ['getFailedItems', 'getStore']
        )->getMock();
        $cartMock->expects(
            $this->any()
        )->method(
            'getFailedItems'
        )->will(
            $this->returnValue(
                [
                    [
                        "item" => ["id" => $productId, "is_qty_disabled" => "false", "sku" => $sku, "qty" => "1"],
                        "code" => "failed_configure",
                        "orig_qty" => "7",
                    ],
                    [
                        "item" => ["sku" => 'invalid', "qty" => "1"],
                        "code" => "failed_sku",
                        "orig_qty" => "1"
                    ],
                ]
            )
        );
        $storeMock = $this->getStoreMock($storeId);
        $cartMock->expects($this->any())->method('getStore')->will($this->returnValue($storeMock));

        return $cartMock;
    }

    /**
     * Return store mock instance
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\Store\Model\Store
     */
    protected function getStoreMock($websiteId)
    {
        $storeMock = $this->getMock('\Magento\Store\Model\Store', [], [], '', false);
        $storeMock->expects($this->any())->method('getWebsiteId')->will($this->returnValue($websiteId));

        return $storeMock;
    }

    /**
     * Return product mock instance
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\Product
     */
    protected function getProductMock($typeId)
    {
        $productMock = $this->getMock(
            'Magento\Catalog\Model\Product',
            ['__wakeup', '_beforeLoad', '_afterLoad', '_getResource', 'load', 'getPriceModel', 'getPrice', 'getTypeId'],
            [],
            '',
            false
        );
        $productMock->expects($this->once())->method('getTypeId')->will($this->returnValue($typeId));
        $productMock->expects($this->once())->method('getPrice')->will($this->returnValue('10.00'));

        return $productMock;
    }

    /**
     * Return PriceCurrencyInterface mock instance
     *
     * @return \PHPUnit_Framework_MockObject_MockObject| \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected function getPriceCurrencyMock()
    {
        $priceCurrencyMock = $this->getMockBuilder('Magento\Framework\Pricing\PriceCurrencyInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $priceCurrencyMock->expects($this->any())->method('format')->will($this->returnArgument(0));

        return $priceCurrencyMock;
    }

    /**
     * Return entityFactory mock instance
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Data\Collection\EntityFactory
     */
    protected function getEntityFactoryMock()
    {
        $entityFactoryMock = $this->getMock('Magento\Framework\Data\Collection\EntityFactory', [], [], '', false);

        return $entityFactoryMock;
    }
}
