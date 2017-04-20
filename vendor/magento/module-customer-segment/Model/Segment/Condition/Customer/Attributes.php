<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CustomerSegment\Model\Segment\Condition\Customer;

use Magento\Customer\Model\Customer;
use Magento\CustomerSegment\Model\Condition\AbstractCondition;

/**
 * Customer attributes condition
 */
class Attributes extends AbstractCondition
{
    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $_eavConfig;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer
     */
    protected $_resourceCustomer;

    /**
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param \Magento\CustomerSegment\Model\ResourceModel\Segment $resourceSegment
     * @param \Magento\Customer\Model\ResourceModel\Customer $resourceCustomer
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\CustomerSegment\Model\ResourceModel\Segment $resourceSegment,
        \Magento\Customer\Model\ResourceModel\Customer $resourceCustomer,
        \Magento\Eav\Model\Config $eavConfig,
        array $data = []
    ) {
        $this->_resourceCustomer = $resourceCustomer;
        $this->_eavConfig = $eavConfig;
        parent::__construct($context, $resourceSegment, $data);
        $this->setType('Magento\CustomerSegment\Model\Segment\Condition\Customer\Attributes');
        $this->setValue(null);
    }

    /**
     * Get array of event names where segment with such conditions combine can be matched
     *
     * @return string[]
     */
    public function getMatchedEvents()
    {
        return ['customer_save_commit_after'];
    }

    /**
     * Get inherited conditions selectors
     *
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $attributes = $this->loadAttributeOptions()->getAttributeOption();
        $conditions = [];
        foreach ($attributes as $code => $label) {
            $conditions[] = ['value' => $this->getType() . '|' . $code, 'label' => $label];
        }

        return $conditions;
    }

    /**
     * Retrieve attribute object
     *
     * @return \Magento\Eav\Model\Entity\Attribute
     */
    public function getAttributeObject()
    {
        return $this->_eavConfig->getAttribute('customer', $this->getAttribute());
    }

    /**
     * Load condition options for castomer attributes
     *
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $productAttributes = $this->_resourceCustomer->loadAllAttributes()->getAttributesByCode();

        $attributes = [];

        foreach ($productAttributes as $attribute) {
            $label = $attribute->getFrontendLabel();
            if (!$label) {
                continue;
            }
            // skip "binary" attributes
            if (in_array($attribute->getFrontendInput(), ['file', 'image'])) {
                continue;
            }
            if ($attribute->getIsUsedForCustomerSegment()) {
                $attributes[$attribute->getAttributeCode()] = $label;
            }
        }
        asort($attributes);
        $this->setAttributeOption($attributes);
        return $this;
    }

    /**
     * Retrieve select option values
     *
     * @return array
     */
    public function getValueSelectOptions()
    {
        if (!$this->getData('value_select_options') && is_object($this->getAttributeObject())) {
            if ($this->getAttributeObject()->usesSource()) {
                if ($this->getAttributeObject()->getFrontendInput() == 'multiselect') {
                    $addEmptyOption = false;
                } else {
                    $addEmptyOption = true;
                }
                $optionsArr = $this->getAttributeObject()->getSource()->getAllOptions($addEmptyOption);
                $this->setData('value_select_options', $optionsArr);
            }

            if ($this->_isCurrentAttributeDefaultAddress()) {
                $optionsArr = $this->_getOptionsForAttributeDefaultAddress();
                $this->setData('value_select_options', $optionsArr);
            }
        }

        return $this->getData('value_select_options');
    }

    /**
     * Get input type for attribute operators.
     *
     * @return string
     */
    public function getInputType()
    {
        if ($this->_isCurrentAttributeDefaultAddress()) {
            return 'select';
        }
        if (!is_object($this->getAttributeObject())) {
            return 'string';
        }
        $input = $this->getAttributeObject()->getFrontendInput();
        switch ($input) {
            case 'boolean':
                return 'select';
            case 'select':
            case 'multiselect':
            case 'date':
                return $input;
            default:
                return 'string';
        }
    }

    /**
     * Get attribute value input element type
     *
     * @return string
     */
    public function getValueElementType()
    {
        if ($this->_isCurrentAttributeDefaultAddress()) {
            return 'select';
        }
        if (!is_object($this->getAttributeObject())) {
            return 'text';
        }
        $input = $this->getAttributeObject()->getFrontendInput();
        switch ($input) {
            case 'boolean':
                return 'select';
            case 'select':
            case 'multiselect':
            case 'date':
                return $input;
            default:
                return 'text';
        }
    }

    /**
     * Check if attribute value should be explicit
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getExplicitApply()
    {
        if (is_object($this->getAttributeObject())) {
            switch ($this->getAttributeObject()->getFrontendInput()) {
                case 'date':
                    return true;
            }
        }
        return false;
    }

    /**
     * Retrieve attribute element
     *
     * @return \Magento\Framework\Data\Form\Element\AbstractElement
     */
    public function getAttributeElement()
    {
        $element = parent::getAttributeElement();
        $element->setShowAsText(true);
        return $element;
    }

    /**
     * Get attribute operator html
     *
     * @return string
     */
    public function getOperatorElementHtml()
    {
        if ($this->_isCurrentAttributeDefaultAddress()) {
            return '';
        }
        return parent::getOperatorElementHtml();
    }

    /**
     * Check if current condition attribute is default billing or shipping address
     *
     * @return bool
     */
    protected function _isCurrentAttributeDefaultAddress()
    {
        $code = $this->getAttributeObject()->getAttributeCode();
        return $code == 'default_billing' || $code == 'default_shipping';
    }

    /**
     * Get options for customer default address attributes value select
     *
     * @return array
     */
    protected function _getOptionsForAttributeDefaultAddress()
    {
        return [
            ['value' => 'is_exists', 'label' => __('exists')],
            ['value' => 'is_not_exists', 'label' => __('does not exist')]
        ];
    }

    /**
     * Customer attributes are standalone conditions, hence they must be self-sufficient
     *
     * @return \Magento\Framework\Phrase
     */
    public function asHtml()
    {
        return __('Customer %1', parent::asHtml());
    }

    /**
     * Return values of start and end datetime for date if operator is equal
     *
     * @return array
     */
    public function getDateValue()
    {
        if ($this->getOperator() == '==') {
            $dateObj = (new \DateTime($this->getValue()))->setTime(0, 0, 0);
            $value = [
                'start' => $dateObj->format('Y-m-d H:i:s'),
                'end' => $dateObj->modify('+1 day')->format('Y-m-d H:i:s'),
            ];
            return $value;
        }
        return $this->getValue();
    }

    /**
     * Return date operator if original operator is equal
     *
     * @return string
     */
    public function getDateOperator()
    {
        if ($this->getOperator() == '==') {
            return 'between';
        }
        return $this->getOperator();
    }

    /**
     * Create SQL condition select for customer attribute
     *
     * @param Customer|\Zend_Db_Expr $customer
     * @param int|\Zend_Db_Expr $website
     * @param bool $isFiltered
     * @return \Magento\Framework\DB\Select
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getConditionsSql($customer, $website, $isFiltered = true)
    {
        if ($this->isVisitor($customer, $isFiltered)) {
            return $this->getSqlForReturnZero();
        }
        return $this->getCustomerConditionalSql($customer, $website, $isFiltered);
    }

    /**
     * Create SQL condition select for customer attribute
     *
     * @param Customer|\Zend_Db_Expr $customer
     * @param int|\Zend_Db_Expr $website
     * @param bool $isFiltered
     * @return \Magento\Framework\DB\Select
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function getCustomerConditionalSql($customer, $website, $isFiltered)
    {
        $attribute = $this->getAttributeObject();
        $table = $attribute->getBackendTable();
        $select = $this->getResource()->createSelect();
        if ($isFiltered) {
            $select->from(['main' => $table], [new \Zend_Db_Expr(1)]);
            $select->where($this->_createCustomerFilter($customer, 'main.entity_id'));
            $select->limit(1);
        } else {
            $select->from(['main' => $table], ['entity_id']);
        }
        if (!in_array($attribute->getAttributeCode(), ['default_billing', 'default_shipping'])) {
            $value = $this->getValue();
            $operator = $this->getOperator();
            if ($attribute->isStatic()) {
                $field = "main.{$attribute->getAttributeCode()}";
            } else {
                $select->where('main.attribute_id = ?', $attribute->getId());
                $field = 'main.value';
            }
            $field = $select->getConnection()->quoteColumnAs($field, null);

            if ($attribute->getFrontendInput() == 'date') {
                $value = $this->getDateValue();
                $operator = $this->getDateOperator();
            }
            $condition = $this->getResource()->createConditionSql($field, $operator, $value);
            $select->where($condition);
        } else {
            if (!$isFiltered) {
                return $this->getCustomerSelect($website);
            }
            if ($this->getValue() == 'is_exists') {
                $ifCondition = 'COUNT(*) != 0';
            } else {
                $ifCondition = 'COUNT(*) = 0';
            }
            $select->reset(\Magento\Framework\DB\Select::COLUMNS);
            $condition = $this->getResource()->getConnection()->getCheckSql($ifCondition, '1', '0');
            $select->columns(new \Zend_Db_Expr($condition));

            if ($attribute->isStatic()) {
                $select->where("main.{$attribute->getAttributeCode()} > 0");
            } else {
                $select->where('main.attribute_id = ?', $attribute->getId());
            }
        }
        return $select;
    }

    /**
     * @param int $websiteId
     * @return \Magento\Framework\DB\Select
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getCustomerSelect($websiteId)
    {
        $select = $this->getResource()->createSelect();
        $table = $this->getResource()->getTable('customer_entity');
        $select->from(['root' => $table], ['entity_id']);
        $select->where($this->getCustomerConditionalSql(null, $websiteId, true));
        return $select;
    }
}
