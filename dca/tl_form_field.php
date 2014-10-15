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
$GLOBALS['TL_DCA']['tl_form_field']['palettes']['fineUploader'] = '{type_legend},type,name,label;{fconfig_legend},mandatory,extensions,maxlength,chunking,chunkSize,multiple,prefix;{store_legend:hide},storeFile;{expert_legend:hide},class,accesskey,tabindex,fSize';

/**
 * Add fields to tl_form_field
 */
$GLOBALS['TL_DCA']['tl_form_field']['fields']['chunking'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_form_field']['chunking'],
    'exclude'                 => true,
    'inputType'               => 'checkbox',
    'eval'                    => array('tl_class'=>'w50 m12'),
    'sql'                     => "char(1) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_form_field']['fields']['chunkSize'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_form_field']['chunkSize'],
    'default'                 => 2000000,
    'exclude'                 => true,
    'inputType'               => 'text',
    'eval'                    => array('rgxp'=>'digit', 'tl_class'=>'w50'),
    'sql'                     => "varchar(16) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_form_field']['fields']['prefix'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_form_field']['prefix'],
    'default'                 => "",
    'exclude'                 => true,
    'inputType'               => 'text',
    'eval'                    => array('maxlength'=>200, 'tl_class'=>'w50'),
    'sql'                     => "varchar(255) NOT NULL default ''"
);