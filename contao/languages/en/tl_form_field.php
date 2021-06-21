<?php

/*
 * FineUploader Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2020, terminal42 gmbh
 * @author     terminal42 <https://terminal42.ch>
 * @license    MIT
 */

$GLOBALS['TL_LANG']['FFL']['fineUploader'] = [
    'Fine uploader',
    'Drag and drop file uploader based on FineUploader by Widen.',
];

/*
 * Fields
 */
$GLOBALS['TL_LANG']['tl_form_field']['maxConnections'] = [
    'Maximum allowable concurrent requests',
    'Here you can control the maximum allowable concurrent requests per client.',
];
$GLOBALS['TL_LANG']['tl_form_field']['chunking'] = [
    'Enable chunking',
    'Enable the file chunking. It is useful to upload big files.',
];
$GLOBALS['TL_LANG']['tl_form_field']['addToDbafs'] = [
    'Add to DBAFS',
    'Add the file to database assisted file system. Note: the widget will return UUID instead of a path.',
];
$GLOBALS['TL_LANG']['tl_form_field']['chunkSize'] = [
    'Chunk size in bytes',
    'Please enter the chunk size in bytes (1MB = 1000000 bytes).',
];
$GLOBALS['TL_LANG']['tl_form_field']['concurrent'] = [
    'Enable concurrent chunking',
    'Activate this checkbox to enable concurrent chunking. Please also note the "Maximum number of connections" setting.',
];
$GLOBALS['TL_LANG']['tl_form_field']['uploadButtonLabel'] = [
    'Upload button label',
    'Here you can enter a custom upload button label.',
];
$GLOBALS['TL_LANG']['tl_form_field']['maxWidth'] = [
    'Maximum width (in pixels)',
    'Here you can enter a maximum width of an image in pixels. Enter 0 to use system defaults.',
];
$GLOBALS['TL_LANG']['tl_form_field']['maxHeight'] = [
    'Maximum height (in pixels)',
    'Here you can enter a maximum height of an image in pixels. Enter 0 to use system defaults.',
];
