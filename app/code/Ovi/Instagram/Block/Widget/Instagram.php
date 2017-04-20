<?php
namespace Ovi\Instagram\Block\Widget;

use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;

/**
 * Class Instagram
 *
 * @package Ovi\Instagram\Block\Widget
 */
class Instagram extends Template implements BlockInterface
{
    protected $_template = 'widget/instagram.phtml';

    protected $_api = null;

    public function canShow()
    {
        
    }



}