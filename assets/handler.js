/**
 * fineuploader extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2014, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-fineuploader
 */

;var ContaoFineUploader = {};

(function() {
    "use strict";

    /**
     * Current value
     */
    var current_value = '';

    /**
     * Initialize the uploader
     * @param object
     * @param object
     * @param object
     * @return object
     */
    ContaoFineUploader.init = function(el, config, options) {
        current_value = document.getElementById('ctrl_' + config.field).value;

        var params = {
            element: el,
            debug: true,
            request: {
                endpoint: window.location.href,
                inputName: config.field + '_fineuploader',
                params: {
                    action: 'fineuploader_upload',
                    name: config.field,
                    REQUEST_TOKEN: config.request_token
                }
            },
            failedUploadTextDisplay: {
                mode: 'custom',
                maxChars: 1000,
                responseProperty: 'error'
            },
            validation: {
                allowedExtensions: config.extensions,
                sizeLimit: config.sizeLimit
            },
            callbacks: {
                onValidateBatch: function(files) {
                    var count = (current_value == '') ? 0 : current_value.split(',').length;

                    if (config.limit > 0 && config.limit < (count + files.length)) {
                        this._batchError(this._options.messages.tooManyItemsError.replace(/\{netItems\}/g, count + files.length).replace(/\{itemLimit\}/g, config.limit));
                        return false;
                    }
                },
                onUpload: function() {
                    if (config.backend) {
                        AjaxRequest.displayBox(Contao.lang.loading + ' …')
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
                        current_value = (current_value.length ? (current_value + ',') : '') + result.file;
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
                        }).post({'action':'fineuploader_reload', 'name':config.field, 'value':current_value, 'REQUEST_TOKEN':config.request_token});
                    } else {
                        document.getElementById('ctrl_' + config.field).value = current_value;
                    }
                }
            }
        };

        Object.append(params, options);
        return new qq.FineUploader(params);
    };

    /**
     * Delete the item
     * @param object
     * @param string
     */
    ContaoFineUploader.deleteItem = function(el, field) {
        var item = el.parentNode;
        var value = item.getAttribute('data-id');
        removeValueFromField(document.getElementById('ctrl_' + field), value);
        item.parentNode.removeChild(item);
    };

    /**
     * Make items sortable
     * @param string
     * @param string
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
     * Remove the value from field
     * @param object
     * @param string
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

        current_value = current.join(',');
        el.value = current_value;
    };
})();
