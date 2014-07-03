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
 * Add palettes to tl_form_field
 */
$GLOBALS['TL_DCA']['tl_form_field']['palettes']['fineUploader'] = '{type_legend},type,name,label;{fconfig_legend},mandatory,extensions,maxlength,multiple;{store_legend:hide},storeFile;{expert_legend:hide},class,accesskey,tabindex,fSize';
