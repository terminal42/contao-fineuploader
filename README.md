fineuploader Contao extension
=============================

Provides the Fine Uploader to the Contao. The uploader initially uploads the files to ```system/tmp``` and moves them to the destination after the form is being submitted. The extension works also in the front end, but only with MooTools!

Includes the [Fine Uplodaer](http://fineuploader.com/) by Widen.

Usage
-------------------
```php
$GLOBALS['TL_DCA']['tl_table']['fields']['myupload'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_table']['myupload'],
    'exclude'                 => true,
    'inputType'               => 'fineUploader',
    'eval'                    => array
    (
        'storeFile' => true, // Mandatory to store the file on the server
        'multiple' => true, // Allow multiple files to be uploaded
        'uploadFolder' => 'files/uploads', // Upload path (destination folder)
        'uploaderConfig' => 'debug: true', // Custom uploader configuration (JSON)
        'uploaderLimit' => 4, // Maximum files that can be uploaded
        'addToDbafs' => true, // Add files to the database assisted file system
        'extensions' => $GLOBALS['TL_CONFIG']['validImageTypes'], // Allowed extension types
        'maxlength' => 2048000, // Maximum file size
        'doNotOvewrite' => true, // Do not overwrite files in destination folder

        'chunking' => true, // Enable chunking
        'chunkSize' => 2000000, // Chunk size in bytes

        // You can also use the default features of fileTree widget such as:
        // isGallery, isDownloads

        // The "orderField" attribute is not valid (see #9)
    ),
    'sql'                     => "blob NULL"
);
```

Contributors
-------------------

* Andreas Schempp <andreas.schempp@terminal42.ch>
* Kamil Kuzminski <kamil.kuzminski@codefog.pl>
