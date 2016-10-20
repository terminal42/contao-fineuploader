/**
 * fineuploader extension for Contao Open Source CMS
 *
 * @copyright Copyright (c) 2008-2015, terminal42 gmbh
 * @author    terminal42 gmbh <info@terminal42.ch>
 * @license   http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link      http://github.com/terminal42/contao-fineuploader
 */
(function (root, factory) {
    if (typeof define === 'function' && define.amd) {
        define([], factory(root));
    } else if (typeof exports === 'object') {
        module.exports = factory(root);
    } else {
        root.ContaoFineUploaderFrontend = factory(root);
    }
})(typeof global !== "undefined" ? global : this.window || this.global, function (root) {
    'use strict';

    /**
     * Default settings
     */
    var defaults = {
        ajaxActionName: 'fineuploader_reload',
        errorMessage: ''
    };

    /**
     * Plugin constructor
     * @param {object} container
     * @param {object} config
     * @param {object} options
     * @constructor
     */
    function Plugin(container, config, options) {
        this.container = container;
        this.config = config;
        options = options || {};

        // Clone the "defaults" object
        this.settings = JSON.parse(JSON.stringify(defaults));

        // Extend default with options
        for (var item in options) {
            if (options.hasOwnProperty(item)) {
                this.settings[item] = options[item];
            }
        }

        this.initUploader();
        this.makeSortable();
    }

    Plugin.prototype = {
        /**
         * Initialize the uploader
         */
        initUploader: function () {
            this.config.configCallback = this.configCallback.bind(this);
            this.uploader = new ContaoFineUploader(this.container, this.config);
            this.uploader.initFineUploader();
        },

        /**
         * Config callback
         *
         * @param {object} config
         */
        configCallback: function (config) {
            config.callbacks.onComplete = this.onCompleteCallback.bind(this);
        },

        /**
         * Make items sortable
         */
        makeSortable: function () {
            var sortable = this.uploader.ajaxContainer.querySelector('.sortable');

            if (sortable === null) {
                return;
            }

            new Sortable(sortable, {
                dataIdAttr: 'data-item-id',
                store: {
                    get: function () {
                        return this.uploader.field.value.split(',');
                    }.bind(this),
                    set: function (sortable) {
                        this.uploader.field.value = sortable.toArray().join(',');
                    }.bind(this)
                }
            });
        },

        /**
         * Handle the onComplete callback
         *
         * @param {string} id
         * @param {string} name
         * @param {object} result
         */
        onCompleteCallback: function (id, name, result) {
            if (!result.success) {
                return;
            }

            // Trigger the default callback
            this.uploader.onComplete.apply(this.uploader, arguments);

            // Return if there are still files in progress
            if (this.uploader.fineUploader.getInProgress() > 0) {
                return;
            }

            $.ajax({
                data: {
                    'action': this.settings.ajaxActionName,
                    'name': this.uploader.field.name,
                    'value': this.uploader.currentValue,
                    'REQUEST_TOKEN': this.uploader.field.form.querySelector('input[name="REQUEST_TOKEN"]').value
                },
                dataType: 'html',
                method: 'POST',
                beforeSend: function () {
                    $(this.uploader.ajaxContainer).addClass('ajax-loading');
                }.bind(this),
                complete: function () {
                    $(this.uploader.ajaxContainer).removeClass('ajax-loading');
                }.bind(this),
                error: function () {
                    alert(this.settings.errorMessage);
                }.bind(this),
                success: function (buffer) {
                    this.uploader.setAjaxContainerContent(buffer);

                    // Make the elements sortable
                    this.makeSortable();
                }.bind(this)
            });
        }
    };

    return Plugin;
});
