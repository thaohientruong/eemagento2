<?php
/**
 * AdminGws configuration model
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdminGws\Model;

class Config extends \Magento\Framework\Config\Data\Scoped implements \Magento\AdminGws\Model\ConfigInterface
{
    /**
     * @param \Magento\AdminGws\Model\Config\Reader $reader
     * @param \Magento\Framework\Config\ScopeInterface $configScope
     * @param \Magento\Framework\Config\CacheInterface $cache
     * @param string $cacheId
     */
    public function __construct(
        \Magento\AdminGws\Model\Config\Reader $reader,
        \Magento\Framework\Config\ScopeInterface $configScope,
        \Magento\Framework\Config\CacheInterface $cache,
        $cacheId = 'admingws_config'
    ) {
        parent::__construct($reader, $configScope, $cache, $cacheId);
    }

    /**
     * Get callback list by group name
     *
     * @param string $groupName
     * @return array
     */
    public function getCallbacks($groupName)
    {
        return $this->get('callbacks/' . $groupName, []);
    }

    /**
     * Get deny acl level rules
     *
     * @param string $level
     * @return array
     */
    public function getDeniedAclResources($level)
    {
        return $this->get('acl/' . $level, []);
    }

    /**
     * Get group processor
     *
     * @param string $groupName
     * @return string|null
     */
    public function getGroupProcessor($groupName)
    {
        return $this->get('processors/' . $groupName);
    }

    /**
     * Get callback for the object
     *
     * @param object $object
     * @param string $callbackGroup
     * @return string|null
     */
    public function getCallbackForObject($object, $callbackGroup)
    {
        $output = null;
        $objectClass = get_class($object);

        if (!$objectClass) {
            return $output;
        }

        /**
         * Determine callback for current instance
         * Explicit class name has priority before inherited classes
         */
        $output = $this->get('callbacks/' . $callbackGroup . '/' . $objectClass, null);
        if (!$output) {
            foreach ($this->get('callbacks/' . $callbackGroup, []) as $className => $callback) {
                if ($object instanceof $className) {
                    $output = $callback;
                    break;
                }
            }
        }
        return $output;
    }
}
