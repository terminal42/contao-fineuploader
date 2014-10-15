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
        var prefixField="";
        if (config.prefix) {
            prefixField=getPrefixField(config.prefix);
        }

        var params = {
            element: el,
            debug: config.debug ? true : false,
            multiple: config.multiple ? true : false,
            request: {
                endpoint: window.location.href,
                inputName: config.field + '_fineuploader',
                params: {
                    action: 'fineuploader_upload',
                    name: config.field,
                    REQUEST_TOKEN: config.request_token,
                    prefix: config.prefix
                    }
            },
            chunking: {
                enabled: config.chunking ? true : false,
                partSize: config.chunkSize
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
            text: {
                formatProgress: config.labels.text.formatProgress,
                failUpload: config.labels.text.failUpload,
                waitingForResponse: config.labels.text.waitingForResponse,
                paused: config.labels.text.paused
            },
            messages: {
                tooManyFilesError: config.labels.messages.tooManyFilesError,
                unsupportedBrowser: config.labels.messages.unsupportedBrowser,
                prefixFieldError: config.labels.messages.prefixFieldError
            },
            retry: {
                autoRetryNote: config.labels.retry.autoRetryNote
            },
            deleteFile: {
                confirmMessage: config.labels.deleteFile.confirmMessage,
                deletingStatusText: config.labels.deleteFile.deletingStatusText,
                deletingFailedText: config.labels.deleteFile.deletingFailedText
            },
            paste: {
                namePromptMessage: config.labels.paste.namePromptMessage
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
                onSubmit: function(id, name){
                    if(prefixField==undefined || prefixField.value==undefined || prefixField.value==""){
                        this._batchError(this._options.messages.prefixFieldError.replace(/\{prefixField\}/g, prefixField.name));
                        return false;
                    }
                else{
                        this._options.request.params.prefix=prefixField.value+getPrefixTail(config.prefix);
                        return true;
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

        // Merge the params
        for (var i in options) {
            params[i] = options[i];
        }

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
    var getPrefixField = function(prefix) {
        if(prefix.indexOf("###")!=-1)
            prefix=prefix.substring(
                prefix.indexOf("###")+3,
                (prefix.substring(prefix.indexOf("###")+3,prefix.length)).indexOf("###")+prefix.indexOf("###")+3);

        return document.getElementsByName(prefix)[0];
    };
    var getPrefixTail = function(prefix) {
        if(prefix.indexOf("###")!=-1)
            var prefixTail=prefix.substring(
                prefix.lastIndexOf("###")+3,prefix.length);
       return prefixTail;
    };

})();
