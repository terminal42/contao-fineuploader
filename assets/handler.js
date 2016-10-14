/**
 * fineuploader extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2015, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-fineuploader
 */

;var ContaoFineUploader = {};

(function() {
    "use strict";

    /**
     * Current values
     */
    var current_values = {};

    /**
     * Initialize the uploader.
     *
     * @param {object} el
     * @param {object} config
     * @param {object} options
     *
     * @returns {object}
     */
    ContaoFineUploader.init = function(el, config, options) {
        current_values[config.field] = document.getElementById('ctrl_' + config.field).value;

        var params = {
            element: el,
            debug: false,
            request: {
                endpoint: window.location.href,
                inputName: config.field + '_fineuploader',
                params: {
                    action: 'fineuploader_upload',
                    name: config.field,
                    REQUEST_TOKEN: config.request_token
                }
            },
            maxConnections: config.maxConnections,
            chunking: {
                enabled: config.chunking ? true : false,
                partSize: config.chunkSize,
                concurrent: config.concurrent ? true : false,
            },
            failedUploadTextDisplay: {
                mode: 'custom',
                maxChars: 1000,
                responseProperty: 'error'
            },
            text: {
                fileInputTitle: config.uploadButtonTitle
            },
            validation: {
                allowedExtensions: config.extensions,
                minSizeLimit: config.minSizeLimit,
                sizeLimit: config.sizeLimit
            },
            callbacks: {
                onValidateBatch: function(files) {
                    var count = (current_values[config.field] === '') ? 0 : current_values[config.field].split(',').length;

                    // If the limit is set to 1 file and user attempts to upload 1 file
                    // then it should replace the current value instead of throwing an error
                    if (config.limit == 1 && files.length == 1 && count == 1) {
                        count = 0;
                        current_values[config.field] = '';
                        this.clearStoredFiles();
                    }

                    if (config.limit > 0 && config.limit < (count + files.length)) {
                        this._batchError(this._options.messages.tooManyItemsError.replace(/\{netItems\}/g, count + files.length).replace(/\{itemLimit\}/g, config.limit));
                        return false;
                    }

                    // Call the custom callback
                    if (config.onValidateBatchCallback) {
                        config.onValidateBatchCallback.apply(this, arguments);
                    }
                },
                onUpload: function() {
                    if (config.backend) {
                        AjaxRequest.displayBox(Contao.lang.loading + ' …');
                    }

                    // Call the custom callback
                    if (config.onUploadCallback) {
                        config.onUploadCallback.apply(this, arguments);
                    }
                },
                onComplete: function(id, name, result) {
                    if (!result.success) {
                        if (config.backend) {
                            AjaxRequest.hideBox();
                        }

                        return;
                    }

                    // Add the uploaded file to value
                    if (result.file) {
                        current_values[config.field] = (current_values[config.field].length ? (current_values[config.field] + ',') : '') + result.file;
                    }

                    if (this.getInProgress() > 0) {
                        return;
                    }

                    if (config.backend) {
                        new Request.Contao({
                            field: document.getElementById('ctrl_' + config.field),
                            evalScripts: false,
                            onSuccess: function(txt, json) {
                                document.getElementById('ctrl_' + config.field).getParent('div').set('html', json.content);
                                json.javascript && Browser.exec(json.javascript);
                                AjaxRequest.hideBox();
                                window.fireEvent('ajax_change');
                            }
                        }).post({'action':'fineuploader_reload', 'name':config.field, 'value':current_values[config.field], 'REQUEST_TOKEN':config.request_token});
                    } else {
                        document.getElementById('ctrl_' + config.field).value = current_values[config.field];
                    }

                    // Call the custom callback
                    if (config.onCompleteCallback) {
                        config.onCompleteCallback.apply(this, arguments);
                    }
                }
            }
        };

        // Merge the params
        for (var i in options) {
            params[i] = options[i];
        }

        return new qq.FineUploader(params);
    };

    /**
     * Delete the item.
     *
     * @param {object} el
     * @param {string} field
     */
    ContaoFineUploader.deleteItem = function(el, field) {
        var item = el.parentNode;
        var value = item.getAttribute('data-id');
        removeValueFromField(document.getElementById('ctrl_' + field), value);
        item.parentNode.removeChild(item);
    };

    /**
     * Make items sortable in the back end.
     *
     * @param {string} id
     * @param {string} oid
     */
    ContaoFineUploader.makeSortable = function(id, oid) {
        var i;
        var list = new Sortables(document.getElementById(id), {
            contstrain: true,
            opacity: 0.6
        }).addEvent('complete', function() {
            var els = [],
                lis = document.getElementById(id).getChildren('li');
            for (i=0; i<lis.length; i++) {
                els.push(lis[i].get('data-id'));
            }
            document.getElementById(oid).value = els.join(',');
        });

        list.fireEvent("complete"); // Initial sorting
    };

    /**
     * Remove the value from field.
     *
     * @param {object} el
     * @param {string} value
     */
    var removeValueFromField = function(el, value) {
        var current = el.value.split(',');
        var i;

        for (i=0; i<current.length; i++) {
            if (current[i] == value) {
                current.splice(i, 1);
                break;
            }
        }

        current_values[el.id] = current.join(',');
        el.value = current_values[el.id];
    };
})();