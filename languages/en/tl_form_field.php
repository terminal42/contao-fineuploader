<?php

/**
 * fineuploader extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2015, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-fineuploader
 */

/**
 * Form fields
 */
$GLOBALS['TL_LANG']['FFL']['fineUploader'] = array('Fine uploader', 'Drag and drop file uploader based on FineUploader by Widen.');

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_form_field']['maxConnections']  = array('Maximum number of connections', 'Here you can control the maxiumum of connections per client.');
$GLOBALS['TL_LANG']['tl_form_field']['chunking']        = array('Enable chunking', 'Enable the file chunking. It is useful to upload big files.');
$GLOBALS['TL_LANG']['tl_form_field']['addToDbafs']      = array('Add to DBAFS', 'Add the file to database assisted file system. Note: the widget will return UUID instead of a path.');
$GLOBALS['TL_LANG']['tl_form_field']['chunkSize']       = array('Chunk size in bytes', 'Please enter the chunk size in bytes (1MB = 1000000 bytes).');
$GLOBALS['TL_LANG']['tl_form_field']['concurrent']      = array('Enable concurrent chunking', 'Activate this checkbox to enable concurrent chunking. Please also note the "Maximum number of connections" setting.');
