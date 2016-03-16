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
 * Register the classes
 */
ClassLoader::addClasses(array
(
    'FineUploaderAjax'    => 'system/modules/fineuploader/classes/FineUploaderAjax.php',
    'FineUploaderBackend' => 'system/modules/fineuploader/classes/FineUploaderBackend.php',
    'FineUploaderBase'    => 'system/modules/fineuploader/widgets/FineUploaderBase.php',
    'FineUploaderWidget'  => 'system/modules/fineuploader/widgets/FineUploaderWidget.php',
    'FormFineUploader'    => 'system/modules/fineuploader/form/FormFineUploader.php',
));

/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
    'fineuploader_backend'  => 'system/modules/fineuploader/templates/fineuploader',
    'fineuploader_frontend' => 'system/modules/fineuploader/templates/fineuploader',
));
