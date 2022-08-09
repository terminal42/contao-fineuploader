<?php

declare(strict_types=1);

$GLOBALS['TL_DCA']['tl_form_field']['palettes']['fineUploader'] = '{type_legend},type,name,label;{fconfig_legend},mandatory,maxConnections,extensions,minlength,maxlength,maxWidth,maxHeight,uploadButtonLabel,chunking,multiple;{store_legend:hide},storeFile,addToDbafs;{expert_legend:hide},class,fSize';
$GLOBALS['TL_DCA']['tl_form_field']['palettes']['__selector__'][] = 'chunking';
$GLOBALS['TL_DCA']['tl_form_field']['subpalettes']['chunking'] = 'chunkSize,concurrent';

/*
 * Add fields
 */
$GLOBALS['TL_DCA']['tl_form_field']['fields']['maxConnections'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_form_field']['maxConnections'],
    'default' => 3,
    'exclude' => true,
    'inputType' => 'text',
    'eval' => ['rgxp' => 'natural', 'tl_class' => 'w50'],
    'sql' => "int(10) NOT NULL default '3'",
];

$GLOBALS['TL_DCA']['tl_form_field']['fields']['chunking'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_form_field']['chunking'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'clr m12', 'submitOnChange' => true],
    'sql' => "char(1) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_form_field']['fields']['chunkSize'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_form_field']['chunkSize'],
    'default' => 2000000,
    'exclude' => true,
    'inputType' => 'text',
    'eval' => ['rgxp' => 'digit', 'tl_class' => 'w50'],
    'sql' => "varchar(16) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_form_field']['fields']['concurrent'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_form_field']['concurrent'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'w50 m12'],
    'sql' => "char(1) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_form_field']['fields']['addToDbafs'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_form_field']['addToDbafs'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'clr'],
    'sql' => "char(1) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_form_field']['fields']['uploadButtonLabel'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_form_field']['uploadButtonLabel'],
    'exclude' => true,
    'inputType' => 'text',
    'eval' => ['maxlength' => 255, 'tl_class' => 'clr'],
    'sql' => "varchar(255) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_form_field']['fields']['maxWidth'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_form_field']['maxWidth'],
    'exclude' => true,
    'inputType' => 'text',
    'eval' => ['rgxp' => 'digit', 'tl_class' => 'w50'],
    'sql' => "smallint(5) unsigned NOT NULL default '0'",
];

$GLOBALS['TL_DCA']['tl_form_field']['fields']['maxHeight'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_form_field']['maxHeight'],
    'exclude' => true,
    'inputType' => 'text',
    'eval' => ['rgxp' => 'digit', 'tl_class' => 'w50'],
    'sql' => "smallint(5) unsigned NOT NULL default '0'",
];
