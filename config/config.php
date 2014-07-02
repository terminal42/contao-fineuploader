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
 * Back end form fields
 */
$GLOBALS['BE_FFL']['fineUploader'] = 'FineUploader\FineUploaderWidget';


/**
 * Front end form fields
 */
$GLOBALS['TL_FFL']['fineUploader'] = 'FineUploader\FormFineUploader';


/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['executePostActions'][] = array('FineUploader\FineUploaderAjax', 'dispatchAjaxRequest');
