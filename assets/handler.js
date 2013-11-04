/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *
 * PHP version 5
 * @copyright  terminal42 gmbh 2009-2013
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Kamil Kuźmiński <kamil.kuzminski@codefog.pl>
 * @license    LGPL
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
		current_value = document.id('ctrl_' + config.field).get('value');

		var params = {
			element: document.id(el),
			request: {
				endpoint: window.location.href,
				inputName: config.field,
				params: {
					action: 'fineuploader_upload',
					name: config.field,
					REQUEST_TOKEN: config.request_token
				}
			},
			validation: {
				allowedExtensions: config.extensions
			},
			callbacks: {
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

					new Request.Contao({
						field: document.id('ctrl_' + config.field),
						evalScripts: false,
						onSuccess: function(txt, json) {
							document.id('ctrl_' + config.field).getParent('div').set('html', json.content);
							json.javascript && Browser.exec(json.javascript);

							// Hide the loading box
							if (config.backend) {
								AjaxRequest.hideBox();
							}

							window.fireEvent('ajax_change');
						}
					}).post({'action':'fineuploader_reload', 'name':config.field, 'value':current_value, 'REQUEST_TOKEN':config.request_token});
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
		var item = document.id(el).getParent();
		var value = item.get('data-id');
		removeValueFromField(document.id('ctrl_' + field), value);
		item.dispose();
	};

	/**
     * Make items sortable
     * @param string
     * @param string
     */
    ContaoFineUploader.makeSortable = function(id, oid) {
    	var i;
        var list = new Sortables(document.id(id), {
            contstrain: true,
            opacity: 0.6
        }).addEvent('complete', function() {
            var els = [],
            	lis = document.id(id).getChildren('li');
            for (i=0; i<lis.length; i++) {
                els.push(lis[i].get('data-id'));
            }
            document.id(oid).value = els.join(',');
        });

        list.fireEvent("complete"); // Initial sorting
	};

	/**
	 * Remove the value from field
	 * @param object
	 * @param string
	 */
	var removeValueFromField = function(el, value) {
		var current = el.get('value').split(',');
		var i;

		for (i=0; i<current.length; i++) {
			if (current[i] == value) {
				current.splice(i, 1);
				break;
			}
		}

		current_value = current.join(',');
		el.set('value', current_value);
	};
})();
