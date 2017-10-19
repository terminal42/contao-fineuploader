<?php

/**
 * Register the templates
 */
\Contao\TemplateLoader::addFiles(
    [
        'fineuploader_backend'         => 'vendor/terminal42/contao-fineuploader/src/Resources/contao/templates',
        'fineuploader_frontend'        => 'vendor/terminal42/contao-fineuploader/src/Resources/contao/templates',
        'fineuploader_item_backend'    => 'vendor/terminal42/contao-fineuploader/src/Resources/contao/templates',
        'fineuploader_item_frontend'   => 'vendor/terminal42/contao-fineuploader/src/Resources/contao/templates',
        'fineuploader_uploader'        => 'vendor/terminal42/contao-fineuploader/src/Resources/contao/templates',
        'fineuploader_values_backend'  => 'vendor/terminal42/contao-fineuploader/src/Resources/contao/templates',
        'fineuploader_values_frontend' => 'vendor/terminal42/contao-fineuploader/src/Resources/contao/templates',
    ]
);
