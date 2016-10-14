
fineuploader Contao extension
=============================

Provides the Fine Uploader to the Contao. The uploader initially uploads the files to ```system/tmp``` and moves them to the destination after the form is being submitted.

Includes the [Fine Uploader](http://fineuploader.com/) by Widen.

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
        'storeFile'         => true,                // Mandatory to store the file on the server
        'multiple'          => true,                // Allow multiple files to be uploaded
        'uploadFolder'      => 'files/uploads',     // Upload target directory (can also be a Contao file system UUID)
        'useHomeDir'        => true,                // Upload to the FE member home directory (overrides "uploadFolder", can also be a Contao file system UUID)
        'uploaderConfig'    => "['debug': true]",   // Custom uploader configuration that gets merged with the other params
        'uploaderLimit'     => 4,                   // Maximum files that can be uploaded
        'addToDbafs'        => true,                // Add files to the database assisted file system
        'extensions'        => 'pdf,zip',           // Allowed extension types
        'minlength'         => 1048000,             // Minimum file size
        'maxlength'         => 2048000,             // Maximum file size (is ignored if you use chunking!)
        'doNotOverwrite'    => true,                // Do not overwrite files in destination folder

        'chunking'          => true,                // Enable chunking
        'chunkSize'         => 2000000,             // Chunk size in bytes
        'concurrent'        => true                 // Allow multiple chunks to be uploaded simultaneously per file
        'maxConnections'    => 3                    // Maximum allowable concurrent requests

        // Upload the files directly to the destination folder. If not set, then the files are first uploaded
        // to the temporary folder and moved to the destination folder only when the form is submitted
        'directUpload' => true,
        
        // Set a custom thumbnail image size that is generated upon image upload  
        'imageSize'         => [160, 120, 'center_center'],

        // You can also use the default features of fileTree widget such as:
        // isGallery, isDownloads

        // The "orderField" attribute is not valid (see #9)
    ),
    'sql'                     => "blob NULL"
);
```