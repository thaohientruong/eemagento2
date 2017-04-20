/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true expr:true*/
define([
    'jquery',
    'uiRegistry',
    'Magento_Ui/js/lib/core/storage',
    'mage/backend/tabs'
], function ($, registry) {
    'use strict';

    $.widget('mage.visualMerchandiserTabs', {
        options: {
        },

        storage: null,
        activeTabName: 'merchandiser_tab_active',
        storageName: 'localStorage',

        /**
         * @private
         */
        _create: function () {
            var tabs = null,
                active = null;

            this.storage = registry.get(this.storageName);

            tabs = this.element.tabs({
                activate: $.proxy(this._activateTab, this),
                create: $.proxy(this._createTabs, this)
            });

            active = this.storage.get(this.activeTabName);

            if (active === undefined) {
                active = 0;
            }
            tabs.tabs('option', 'active', active);
        },

        /**
         * @param {jQuery.Event} event
         * @param {Object} ui
         * @private
         */
        _activateTab: function (event, ui) {
            this._trigger('viewDidChange', event, ui.newPanel);
            this.storage.set(this.activeTabName, ui.newTab.index());
        },

        /**
         * @param {jQuery.Event} event
         * @param {Object} ui
         * @private
         */
        _createTabs: function (event, ui) {
            this._trigger('viewDidChange', event, ui.panel);
        }
    });

    return $.mage.visualMerchandiserTabs;
});
