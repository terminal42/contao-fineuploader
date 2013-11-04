<?php

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


/**
 * Back end form fields
 */
$GLOBALS['BE_FFL']['fineUploader'] = 'FineUploaderWidget';


/**
 * Front end form fields
 */
$GLOBALS['TL_FFL']['fineUploader'] = 'FineUploaderWidget';


/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['executePostActions'][] = array('FineUploader', 'dispatchAjaxRequest');
