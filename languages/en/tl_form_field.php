<?php

/**
 * fineuploader extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2014, terminal42 gmbh
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
$GLOBALS['TL_LANG']['tl_form_field']['chunking']  = array('Enable chunking', 'Enable the file chunking. It is useful to upload big files.');
$GLOBALS['TL_LANG']['tl_form_field']['chunkSize'] = array('Chunk size in bytes', 'Please enter the chunk size in bytes (1MB = 1000000 bytes).');
$GLOBALS['TL_LANG']['tl_form_field']['prefix']  = array('File prefix', 'If you want the uploaded files to be prefixed, fill in here. To use the value of another field write ###field_POST_name## and replace the middle with the value of the name attribute from the field you want to use. ');