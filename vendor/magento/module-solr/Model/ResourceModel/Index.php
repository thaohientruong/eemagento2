<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Solr\Model\ResourceModel;

use Magento\Store\Model\StoreManagerInterface;

/**
 * Solr index resource model
 */
class Index extends \Magento\CatalogSearch\Model\ResourceModel\Fulltext
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        StoreManagerInterface $storeManager,
        $connectionName = null
    ) {
        $this->storeManager = $storeManager;
        parent::__construct($context, $eventManager, $connectionName);
    }

    /**
     * Return array of price data per customer and website by products
     *
     * @param null|array $productIds
     * @return array
     */
    protected function _getCatalogProductPriceData($productIds = null)
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from(
            $this->getTable('catalog_product_index_price'),
            ['entity_id', 'customer_group_id', 'website_id', 'min_price']
        );

        if ($productIds) {
            $select->where('entity_id IN (?)', $productIds);
        }

        $result = [];
        foreach ($connection->fetchAll($select) as $row) {
            $result[$row['website_id']][$row['entity_id']][$row['customer_group_id']] = round($row['min_price'], 2);
        }

        return $result;
    }

    /**
     * Retrieve price data for product
     *
     * @param null|array $productIds
     * @param int $storeId
     * @return array
     */
    public function getPriceIndexData($productIds, $storeId)
    {
        $priceProductsIndexData = $this->_getCatalogProductPriceData($productIds);

        $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();
        if (!isset($priceProductsIndexData[$websiteId])) {
            return [];
        }

        return $priceProductsIndexData[$websiteId];
    }

    /**
     * Prepare system index data for products.
     *
     * @param int $storeId
     * @param null|array $productIds
     * @return array
     */
    public function getCategoryProductIndexData($storeId = null, $productIds = null)
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from(
            [$this->getTable('catalog_category_product_index')],
            ['category_id', 'product_id', 'position', 'store_id']
        )->where(
            'store_id = ?',
            $storeId
        );

        if ($productIds) {
            $select->where('product_id IN (?)', $productIds);
        }

        $result = [];
        foreach ($connection->fetchAll($select) as $row) {
            $result[$row['product_id']][$row['category_id']] = $row['position'];
        }

        return $result;
    }

    /**
     * Retrieve moved categories product ids
     *
     * @param int $categoryId
     * @return array
     */
    public function getMovedCategoryProductIds($categoryId)
    {
        $connection = $this->getConnection();

        $select = $connection->select()->distinct()->from(
            ['c_p' => $this->getTable('catalog_category_product')],
            ['product_id']
        )->join(
            ['c_e' => $this->getTable('catalog_category_entity')],
            'c_p.category_id = c_e.entity_id',
            []
        )->where(
            $connection->quoteInto('c_e.path LIKE ?', '%/' . $categoryId . '/%')
        )->orWhere(
            'c_p.category_id = ?',
            $categoryId
        );

        return $connection->fetchCol($select);
    }
}
