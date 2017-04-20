<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleTagManager\Model\Config\Source;

use \Magento\GoogleTagManager\Helper\Data as Helper;

/**
 * Class AccountType
 */
class AccountType
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => Helper::TYPE_UNIVERSAL,
                'label' => __('Universal Analytics')
            ],[
                'value' => Helper::TYPE_TAG_MANAGER,
                'label' => __('Google Tag Manager')
            ],
        ];
    }
}
