<?php

declare(strict_types=1);

/*
 * FineUploader Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2020, terminal42 gmbh
 * @author     terminal42 <https://terminal42.ch>
 * @license    MIT
 */

\Contao\TemplateLoader::addFiles(
    [
        'fineuploader_backend' => 'vendor/terminal42/contao-fineuploader/src/Resources/contao/templates',
        'fineuploader_frontend' => 'vendor/terminal42/contao-fineuploader/src/Resources/contao/templates',
        'fineuploader_item_backend' => 'vendor/terminal42/contao-fineuploader/src/Resources/contao/templates',
        'fineuploader_item_frontend' => 'vendor/terminal42/contao-fineuploader/src/Resources/contao/templates',
        'fineuploader_uploader' => 'vendor/terminal42/contao-fineuploader/src/Resources/contao/templates',
        'fineuploader_values_backend' => 'vendor/terminal42/contao-fineuploader/src/Resources/contao/templates',
        'fineuploader_values_frontend' => 'vendor/terminal42/contao-fineuploader/src/Resources/contao/templates',
    ]
);
