<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CustomAttributeManagement\Block\Form\Renderer;

use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Template;

/**
 * EAV Entity Attribute Form Renderer Block for File
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class File extends \Magento\CustomAttributeManagement\Block\Form\Renderer\AbstractRenderer
{
    /**
     * @var \Magento\Framework\Url\EncoderInterface
     */
    protected $urlEncoder;

    /**
     * @param \Magento\Framework\Url\EncoderInterface $urlEncoder
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        array $data = []
    ) {
        $this->urlEncoder = $urlEncoder;
        parent::__construct($context, $data);
    }

    /**
     * Return escaped value
     *
     * @return string
     */
    public function getEscapedValue()
    {
        if ($this->getValue()) {
            return $this->escapeHtml($this->urlEncoder->encode($this->getValue()));
        }
        return '';
    }
}
