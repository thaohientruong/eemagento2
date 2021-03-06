<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\ForeignKey\ObjectRelationProcessor;

use Magento\Framework\DB\Adapter\AdapterInterface as Connection;
use Magento\Framework\Model\ResourceModel\Db\TransactionManagerInterface;
use Magento\Framework\ForeignKey\ConstraintProcessor;
use Magento\Framework\ForeignKey\ConfigInterface;
use Magento\Framework\Model\ResourceModel\Db\ObjectRelationProcessor;

class Plugin
{
    /**
     * @var \Magento\Framework\ForeignKey\ConfigInterface
     */
    protected $config;

    /**
     * @var ConstraintProcessor
     */
    protected $constraintProcessor;

    /**
     * @param ConfigInterface $config
     * @param ConstraintProcessor $constraintProcessor
     */
    public function __construct(
        \Magento\Framework\ForeignKey\ConfigInterface $config,
        ConstraintProcessor $constraintProcessor
    ) {
        $this->config = $config;
        $this->constraintProcessor = $constraintProcessor;
    }

    /**
     * Process object relations
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\ObjectRelationProcessor $subject
     * @param TransactionManagerInterface $transactionManager
     * @param Connection $connection
     * @param string $table
     * @param string $condition
     * @param array $involvedData
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeDelete(
        \Magento\Framework\Model\ResourceModel\Db\ObjectRelationProcessor $subject,
        TransactionManagerInterface $transactionManager,
        Connection $connection,
        $table,
        $condition,
        array $involvedData
    ) {
        /** Lock initial data */
        $select = $connection->select()
            ->forUpdate(true)
            ->from($table)
            ->where($condition);
        $connection->fetchAssoc($select);

        $constraints = $this->config->getConstraintsByReferenceTableName($table);
        foreach ($constraints as $constraint) {
            $this->constraintProcessor->resolve($transactionManager, $constraint, [$involvedData]);
        }
    }

    /**
     * Validate data integrity
     *
     * @param ObjectRelationProcessor $subject
     * @param string $table
     * @param array $involvedData
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeValidateDataIntegrity(
        ObjectRelationProcessor $subject,
        $table,
        array $involvedData
    ) {
        $constraints = $this->config->getConstraintsByTableName($table);
        foreach ($constraints as $constraint) {
            if (substr($constraint->getStrategy(), 0, 3) === 'DB ') {
                // skip validation of native database constraints
                continue;
            }
            $this->constraintProcessor->validate($constraint, $involvedData);
        }
    }
}
