import "./backend.css"
import "./_handler.js"

(function (root, factory) {
    if (typeof define === 'function' && define.amd) {
        define([], factory(root));
    } else if (typeof exports === 'object') {
        module.exports = factory(root);
    } else {
        root.ContaoFineUploaderBackend = factory(root);
    }
})(typeof global !== "undefined" ? global : this.window || this.global, function (root) {
    'use strict';

    /**
     * Default settings
     */
    var defaults = {
        ajaxActionName: 'fineuploader_reload'
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
            config.callbacks.onUpload = this.onUploadCallback;
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

            var i;
            var list = new Sortables(sortable, {
                contstrain: true,
                opacity: 0.6
            }).addEvent('complete', function () {
                var els = [],
                    lis = sortable.getChildren('li');

                for (i = 0; i < lis.length; i++) {
                    els.push(lis[i].get('data-item-id'));
                }

                this.uploader.field.value = els.join(',');
            }.bind(this));

            list.fireEvent('complete'); // Initial sorting
        },

        /**
         * Handle the onUpload callback
         */
        onUploadCallback: function () {
            AjaxRequest.displayBox(Contao.lang.loading + ' …');
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
                AjaxRequest.hideBox();
                return;
            }

            // Trigger the default callback
            this.uploader.onComplete.apply(this.uploader, arguments);

            // Return if there are still files in progress
            if (this.uploader.fineUploader.getInProgress() > 0) {
                return;
            }

            new Request.Contao({
                field: this.uploader.field.name,
                evalScripts: false,
                onSuccess: function (txt, json) {
                    this.uploader.setAjaxContainerContent(json.content);

                    // Execute the scripts
                    json.javascript && Browser.exec(json.javascript);

                    // Hide the loading box
                    AjaxRequest.hideBox();

                    // Fire the ajax change event
                    window.fireEvent('ajax_change');

                    // Make the elements sortable
                    this.makeSortable();
                }.bind(this)
            }).post({
                'action': this.settings.ajaxActionName,
                'name': this.uploader.field.name,
                'value': this.uploader.currentValue,
                'REQUEST_TOKEN': this.uploader.field.form.querySelector('input[name="REQUEST_TOKEN"]').value
            });
        }
    };

    return Plugin;
});
