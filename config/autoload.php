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
 * Register the namespace
 */
ClassLoader::addNamespace('FineUploader');


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
    'FineUploader\FineUploaderAjax'   => 'system/modules/fineuploader/classes/FineUploaderAjax.php',
    'FineUploader\FineUploaderBase'   => 'system/modules/fineuploader/widgets/FineUploaderBase.php',
    'FineUploader\FineUploaderWidget' => 'system/modules/fineuploader/widgets/FineUploaderWidget.php',
    'FineUploader\FormFineUploader'   => 'system/modules/fineuploader/form/FormFineUploader.php',
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
    'fineuploader_default' => 'system/modules/fineuploader/templates/fineuploader'
));
