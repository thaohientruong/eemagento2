<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Rma\Block\Adminhtml\Rma\Item\Attribute\Edit;

/**
 * RMA Items Attributes Edit JavaScript Block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Js extends \Magento\Backend\Block\Template
{
    /**
     * Rma eav
     *
     * @var \Magento\CustomAttributeManagement\Helper\Data
     */
    protected $_attributeHelper = null;

    /**
     * Json encoder interface
     *
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\CustomAttributeManagement\Helper\Data $attributeHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\CustomAttributeManagement\Helper\Data $attributeHelper,
        array $data = []
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        $this->_attributeHelper = $attributeHelper;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve allowed Input Validate Filters in JSON format
     *
     * @return string
     */
    public function getValidateFiltersJson()
    {
        return $this->_jsonEncoder->encode($this->_attributeHelper->getAttributeValidateFilters());
    }

    /**
     * Retrieve allowed Input Filter Types in JSON format
     *
     * @return string
     */
    public function getFilteTypesJson()
    {
        return $this->_jsonEncoder->encode($this->_attributeHelper->getAttributeFilterTypes());
    }

    /**
     * Returns array of input types with type properties
     *
     * @return array
     */
    public function getAttributeInputTypes()
    {
        /**
         * Restriction! RMA doesn't support next types of attributes - multiline, multiselect & date
         * @see MAGETWO-45043: Customer/Guest doesn't have ability create Return if required and visible on
         * frontend RMA attribute was created
         */
        $supportedInputTypes = [];
        $restrictedInputTypes = ['multiline', 'multiselect', 'date'];
        foreach ($this->_attributeHelper->getAttributeInputTypes() as $code => $type) {
            if (!in_array($code, $restrictedInputTypes)) {
                $supportedInputTypes[$code] = $type;
            }
        }
        return $supportedInputTypes;
    }
}
