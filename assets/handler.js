import "./handler.css"
import { FineUploader } from 'fine-uploader';

(function (root, factory) {
    if (typeof define === 'function' && define.amd) {
        define([], factory(root));
    } else if (typeof exports === 'object') {
        module.exports = factory(root);
    } else {
        root.ContaoFineUploader = factory(root);
    }
})(typeof global !== "undefined" ? global : this.window || this.global, function (root) {
    'use strict';

    /**
     * Default settings
     */
    var defaults = {
        // AJAX
        ajaxActionName: 'fineuploader_upload',
        ajaxUrl: null,

        // Selectors
        ajaxContainerSelector: '[data-fineuploader="ajax-container"]',
        deleteButtonsSelector: '[data-fineuploader="delete"]',
        fieldSelector: '[data-fineuploader="field"]',
        itemSelector: '[data-fineuploader="item"]',
        uploaderSelector: '[data-fineuploader="uploader"]',

        // Callbacks
        configCallback: null
    };

    /**
     * Plugin constructor
     * @param {object} container
     * @param {object} options
     * @constructor
     */
    function Plugin(container, options) {
        this.container = container;
        options = options || {};

        // Clone the "defaults" object
        this.settings = JSON.parse(JSON.stringify(defaults));

        // Extend default with options
        for (var item in options) {
            if (options.hasOwnProperty(item)) {
                this.settings[item] = options[item];
            }
        }

        this.initEnvironment();
        this.bindFormEventListeners();

        this.currentValue = this.field.value;
        this.submitForm = true;
    }

    Plugin.prototype = {
        /**
         * Initialize the environment
         */
        initEnvironment: function () {
            this.ajaxContainer = this.container.querySelector(this.settings.ajaxContainerSelector);
            this.deleteButtons = this.container.querySelectorAll(this.settings.deleteButtonsSelector);
            this.field = this.container.querySelector(this.settings.fieldSelector);
            this.items = this.container.querySelectorAll(this.settings.itemSelector);
            this.uploader = this.container.querySelector(this.settings.uploaderSelector);

            this.bindDeleteEventListeners.apply(this);
        },

        /**
         * Initialize the FineUploader
         */
        initFineUploader: function () {
            var templateElement = this.container.querySelector('script[type="text/template"][id^="qq-template"]');

            // Remove all HTML comments, especially the <!-- TEMPLATE START: … --> Contao debug template comment,
            // which would cause the FineUploader to not initialize due to an error.
            templateElement.innerHTML = templateElement.innerHTML.replace(/<!--[\s\S]*?-->/g, '');

            var config = {
                element: this.uploader,
                debug: !!this.settings.debug,
                template: templateElement,
                request: {
                    endpoint: this.settings.ajaxUrl || root.location.href,
                    inputName: this.field.name + '_fineuploader',
                    params: {
                        action: this.settings.ajaxActionName,
                        name: this.field.name,
                        REQUEST_TOKEN: this.field.form.querySelector('input[name="REQUEST_TOKEN"]').value
                    }
                },
                failedUploadTextDisplay: {
                    mode: 'custom',
                    maxChars: 1000,
                    responseProperty: 'error'
                },
                callbacks: {
                    onValidateBatch: this.onValidateBatch.bind(this),
                    onStatusChange: this.onStatusChange.bind(this),
                    onComplete: this.onComplete.bind(this)
                }
            };

            // Set the maximum number of connections
            if (this.settings.maxConnections) {
                config.maxConnections = this.settings.maxConnections;
            }

            // Set the chunking
            if (this.settings.chunking) {
                config.chunking = {
                    enabled: true,
                    partSize: this.settings.chunkSize,
                    concurrent: this.settings.concurrent ? true : false
                };
            }

            // Set the validation
            if (this.settings.extensions || this.settings.minSizeLimit || this.settings.sizeLimit) {
                config.validation = {};

                if (this.settings.extensions) {
                    config.validation.allowedExtensions = this.settings.extensions;
                }
                if (this.settings.minSizeLimit) {
                    config.validation.minSizeLimit = this.settings.minSizeLimit;
                }
                if (this.settings.sizeLimit) {
                    config.validation.sizeLimit = this.settings.sizeLimit
                }
                if (this.settings.minWidth || this.settings.maxWidth || this.settings.minHeight || this.settings.maxHeight) {
                    config.validation.image = {};
                }
                if (this.settings.minWidth) {
                    config.validation.image.minWidth = this.settings.minWidth
                }
                if (this.settings.maxWidth) {
                    config.validation.image.maxWidth = this.settings.maxWidth
                }
                if (this.settings.minHeight) {
                    config.validation.image.minHeight = this.settings.minHeight
                }
                if (this.settings.maxHeight) {
                    config.validation.image.maxHeight = this.settings.maxHeight
                }
            }

            // Set the upload button title
            if (this.settings.uploadButtonTitle) {
                config.text = {
                    fileInputTitle: this.settings.uploadButtonTitle
                };
            }

            // Set the messages
            if (this.settings.messages && !Array.isArray(this.settings.messages)) {
                config.messages = this.settings.messages;
            }

            // Call the config callback
            if (typeof this.settings.configCallback === 'function') {
                this.settings.configCallback(config);
            }

            this.fineUploader = new FineUploader(config);
        },

        /**
         * Set the AJAX container content
         *
         * @param {string} buffer
         */
        setAjaxContainerContent: function (buffer) {
            this.ajaxContainer.innerHTML = buffer;
            this.initEnvironment();
        },

        /**
         * Bind the event listeners to delete buttons
         */
        bindDeleteEventListeners: function () {
            var self = this;

            // Handle the delete buttons
            Array.from(self.deleteButtons).forEach(function (el) {
                el.addEventListener('click', function (e) {
                    e.preventDefault();
                    self.deleteFile(this.dataset.deleteId);
                });
            });
        },

        /**
         * Bind the event listeners to the form
         */
        bindFormEventListeners: function () {
            // Prevent the form submit if e.g. the file upload is in progress
            this.field.form.addEventListener('submit', function (e) {
                if (!this.submitForm) {
                    e.preventDefault();
                }
            }.bind(this));
        },

        /**
         * Trigger the validate batch callback
         *
         * @param {array} files
         * @returns {boolean}
         */
        onValidateBatch: function (files) {
            var count = (this.currentValue === '') ? 0 : this.currentValue.split(',').length;

            // If the limit is set to 1 file and user attempts to upload 1 file
            // then it should replace the current value instead of throwing an error
            if (this.settings.limit == 1 && files.length == 1 && count == 1) {
                count = 0;
                this.currentValue = '';
                this.fineUploader.clearStoredFiles();
            }

            // Trigger an error if there are too many items
            if (this.settings.limit > 0 && this.settings.limit < (count + files.length)) {
                this.fineUploader._batchError(
                    this.fineUploader._options.messages.tooManyItemsError
                        .replace(/\{netItems\}/g, count + files.length)
                        .replace(/\{itemLimit\}/g, this.settings.limit)
                );

                return false;
            }

            return true;
        },

        /**
         * Trigger the status change callback
         */
        onStatusChange: function () {
            this.submitForm = this.fineUploader.getInProgress() === 0;

            // Disable the form buttons alongside the form
            Array.from(document.querySelectorAll('[type="submit"]')).forEach(function (button) {
                if (button.form === this.field.form) {
                    button.disabled = !this.submitForm;
                }
            }.bind(this));
        },

        /**
         * Trigger the complete callback
         *
         * @param {string} id
         * @param {string} name
         * @param {object} result
         */
        onComplete: function (id, name, result) {
            if (!result.success) {
                return;
            }

            // Add the uploaded file to value
            if (result.file) {
                this.currentValue = (this.currentValue.length ? (this.currentValue + ',') : '') + result.file;
            }

            // Update the value if there are no files in progress
            if (this.fineUploader.getInProgress() === 0) {
                this.field.value = this.currentValue;
            }
        },

        /**
         * Delete the file
         *
         * @param {string} file The file to delete
         */
        deleteFile: function (file) {
            var current = this.currentValue.split(',');
            var i;

            // Remove the file from the current value
            for (i = 0; i < current.length; i++) {
                if (current[i] === file) {
                    current.splice(i, 1);
                    break;
                }
            }

            // Update the current value
            this.currentValue = current.join(',');
            this.field.value = this.currentValue;

            // Remove the DOM element
            for (i = 0; i < this.items.length; i++) {
                var el = this.items[i];

                if (el.dataset.itemId === file) {
                    el.parentElement.removeChild(el);
                    break;
                }
            }
        }
    };

    return Plugin;
});
