/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
define([
    "jquery",
    'Magento_Ui/js/modal/alert',
    "jquery/ui"
], function($, alert){
    "use strict";

    $.widget('mage.testConnection', {
        options: {
            url: '',
            elementId: '',
            successText: '',
            failedText: ''
        },

        /**
         * Bind handlers to events
         */
        _create: function() {
            this._on({'click': $.proxy(this._connect, this)});
        },

        /**
         * Method triggers an AJAX request to check Solr connection
         * @private
         */
        _connect: function() {
            var result = this.options.failedText;
            var element =  $("#" + this.options.elementId)
            element.removeClass('success').addClass('fail')
            var self = this;
            $.ajax({
                url: this.options.url,
                showLoader: true,
                data : {
                    host: $('#catalog_search_solr_server_hostname').val(),
                    port: $('#catalog_search_solr_server_port').val(),
                    path: $('#catalog_search_solr_server_path').val(),
                    timeout: $('#catalog_search_solr_server_timeout').val()
                }
            }).done(function(response) {
                if (response.success) {
                    element.removeClass('fail').addClass('success')
                    result = self.options.successText;
                } else {
                    var msg = response.error_message;
                    if (msg) {
                        alert({
                            content: $.mage.__(msg)
                        });
                    }
                }
            }).always(function() {
                    $("#connection_test_result").text(result);
            });
        }
    });

    return $.mage.testConnection;
});