<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Solr\SearchAdapter\Aggregation\Builder;

use Magento\Framework\Search\Dynamic\DataProviderInterface;
use Magento\Framework\Search\Request\BucketInterface as RequestBucketInterface;
use Magento\Framework\Search\Request\Dimension;

interface BucketBuilderInterface
{
    /**
     * @param RequestBucketInterface $bucket
     * @param Dimension[] $dimensions
     * @param \Solarium\Core\Query\Result\ResultInterface $baseQueryResult
     * @param DataProviderInterface $dataProvider
     * @return array
     */
    public function build(
        RequestBucketInterface $bucket,
        array $dimensions,
        \Solarium\Core\Query\Result\ResultInterface $baseQueryResult,
        DataProviderInterface $dataProvider
    );
}
