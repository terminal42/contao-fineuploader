FineUploader Bundle for Contao Open Source CMS
==============================================

This extension integrates the [Fine Uploader](http://fineuploader.com/) by Widen with Contao Open Source CMS.

> [!IMPORTANT]
> Please be aware that the FineUploader script [is not being developed anymore](https://github.com/FineUploader/fine-uploader/issues/2073),
we are therefore not planning to add new features or continue to support this extension.


Installation
------------

Choose the installation method that matches your workflow!

### Installation via Contao Manager

Search for `terminal42/contao-fineuploader` in the Contao Manager and add it
to your installation. Apply changes to update the packages.

### Manual installation

Add a composer dependency for this bundle. Therefore, change in the project root and run the following:

```bash
composer require terminal42/fineuploader
```

Depending on your environment, the command can differ, i.e. starting with `php composer.phar …` if you do not have
composer installed globally.



Usage
-----

Define the form field as follows:

```php
$GLOBALS['TL_DCA']['tl_table']['fields']['myupload'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_table']['myupload'],
    'exclude'   => true,
    'inputType' => 'fineUploader',
    'eval'      => [
        'multiple'          => true,                        // Allow multiple files to be uploaded
        'storeFile'         => true,                        // Store the files on the server
        'uploadFolder'      => 'files/uploads',             // Upload target directory (can also be a Contao file system UUID)
        'useHomeDir'        => true,                        // Upload to the current logged in member home directory (overrides "uploadFolder", can also be a Contao file system UUID)
        'uploaderLimit'     => 4,                           // Maximum files that can be uploaded
        'maxConnections'    => 3                            // Maximum allowable concurrent requests
        'addToDbafs'        => true,                        // Add files to the database assisted file system
        'doNotOverwrite'    => true,                        // Do not overwrite files in destination folder
        'debug'             => true                         // Enable the debug mode (always true in development environment)

        // Validation
        'extensions'        => 'pdf,zip',                   // Allowed extension types
        'minlength'         => 1048000,                     // Minimum file size
        'maxlength'         => 2048000,                     // Maximum file size (ignored if you use chunking!)
        'minWidth'          => 400,                         // Minimum image width
        'maxWidth'          => 800,                         // Maximum image width
        'minHeight'         => 300,                         // Minimum image height
        'maxHeight'         => 600,                         // Maximum image height

        // Chunking
        'chunking'          => true,                        // Enable chunking
        'chunkSize'         => 2000000,                     // Chunk size in bytes
        'concurrent'        => true                         // Allow multiple chunks to be uploaded simultaneously per file

        // Rendering
        'imageSize'         => [160, 120, 'center_center'], // Thumbnail image size that is generated upon image upload
        'isGallery'         => true,                        // Display the widget as image gallery
        'isDownloads'       => true,                        // Display the widget as file list
        'sortable'          => true,                        // Make the uploaded files sortable
        'uploadButtonTitle' => 'Upload',                    // Custom upload button title

        // Upload the files directly to the destination folder. If not set, then the files are first uploaded
        // to the temporary folder and moved to the destination folder only when the form is submitted
        'directUpload'      => true,
    ],
    'sql' => "blob NULL"
];
```

## License

This bundle is released under the [MIT license](LICENSE)
