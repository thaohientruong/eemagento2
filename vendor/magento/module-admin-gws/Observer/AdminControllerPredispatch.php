<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdminGws\Observer;

use Magento\Framework\Event\ObserverInterface;

class AdminControllerPredispatch implements ObserverInterface
{
    const ACL_WEBSITE_LEVEL = 'website';

    const ACL_STORE_LEVEL = 'store';

    /**
     * @var \Magento\AdminGws\Observer\RolePermissionAssigner
     */
    protected $rolePermissionAssigner;

    /**
     * @var array|null
     */
    protected $_controllersMap = null;

    /**
     * @var \Magento\AdminGws\Model\CallbackInvoker
     */
    protected $callbackInvoker;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $backendAuthSession;

    /**
     * @var \Magento\AdminGws\Model\Role
     */
    protected $role;

    /**
     * @var \Magento\AdminGws\Model\ConfigInterface
     */
    protected $config;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param \Magento\AdminGws\Model\Role $role
     * @param \Magento\AdminGws\Observer\RolePermissionAssigner $rolePermissionAssigner
     * @param \Magento\AdminGws\Model\CallbackInvoker $callbackInvoker
     * @param \Magento\Backend\Model\Auth\Session $backendAuthSession
     * @param \Magento\AdminGws\Model\ConfigInterface $config
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Store\Model\System\Store $systemStore
     */

    public function __construct(
        \Magento\AdminGws\Model\Role $role,
        \Magento\AdminGws\Observer\RolePermissionAssigner $rolePermissionAssigner,
        \Magento\AdminGws\Model\CallbackInvoker $callbackInvoker,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Magento\AdminGws\Model\ConfigInterface $config,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Store\Model\System\Store $systemStore
    ) {
        $this->rolePermissionAssigner = $rolePermissionAssigner;
        $this->callbackInvoker = $callbackInvoker;
        $this->backendAuthSession = $backendAuthSession;
        $this->role = $role;
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->systemStore = $systemStore;
    }

    /**
     * Reinit stores only with allowed scopes
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->backendAuthSession->isLoggedIn()) {
            // load role with true websites and store groups
            $this->role->setAdminRole($this->backendAuthSession->getUser()->getRole());

            if (!$this->role->getIsAll()) {
                // disable single store mode
                $this->storeManager->setIsSingleStoreModeAllowed(false);

                // cleanup from disallowed stores
                $this->storeManager->reinitStores();

                // completely block some admin menu items
                $this->rolePermissionAssigner->denyAclLevelRules(self::ACL_WEBSITE_LEVEL);
                if (count($this->role->getWebsiteIds()) === 0) {
                    $this->rolePermissionAssigner->denyAclLevelRules(self::ACL_STORE_LEVEL);
                }
                // cleanup dropdowns for forms/grids that are supposed to be built in future
                $this->systemStore->setIsAdminScopeAllowed(false)->reload();
            }

            // inject into request predispatch to block disallowed actions
            $this->validateControllerPredispatch($observer);
        }
    }

    /**
     * Validate page by current request (module, controller, action)
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function validateControllerPredispatch($observer)
    {
        if ($this->role->getIsAll()) {
            return;
        }

        /** @var \Magento\Framework\App\RequestInterface $request */
        $request = $observer->getEvent()->getRequest();
        // initialize controllers map
        if (null === $this->_controllersMap) {
            $this->_controllersMap = ['full' => [], 'partial' => []];
            foreach ($this->config->getCallbacks('controller_predispatch') as $actionName => $method) {
                list($module, $controller, $action) = explode('__', $actionName);
                if ($action) {
                    $this->_controllersMap['full'][$module][$controller][$action] = $method;
                } else {
                    $this->_controllersMap['partial'][$module][$controller] = $method;
                }
            }
        }

        // map request to validator callback
        $routeName = $request->getRouteName();
        $controllerName = $request->getControllerName();
        $actionName = $request->getActionName();
        $callback = false;
        if (isset(
                $this->_controllersMap['full'][$routeName]
            ) && isset(
                $this->_controllersMap['full'][$routeName][$controllerName]
            ) && isset(
                $this->_controllersMap['full'][$routeName][$controllerName][$actionName]
            )
        ) {
            $callback = $this->_controllersMap['full'][$routeName][$controllerName][$actionName];
        } elseif (isset(
                $this->_controllersMap['partial'][$routeName]
            ) && isset(
                $this->_controllersMap['partial'][$routeName][$controllerName]
            )
        ) {
            $callback = $this->_controllersMap['partial'][$routeName][$controllerName];
        }

        if ($callback) {
            $this->callbackInvoker->invoke(
                $callback,
                $this->config->getGroupProcessor('controller_predispatch'),
                $observer->getEvent()->getControllerAction()
            );
        }
    }
}
