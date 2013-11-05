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
		'uploadFolder' => 'files/uploads', // Upload path (destination folder)
		'uploaderConfig' => 'debug: true', // Custom uploader configuration (JSON)
		'uploaderTemplate' => 'fineuploader_custom', // Uploader template file
		'uploaderLimit' => 4, // Maximum files that can be uploaded
		'extensions' => $GLOBALS['TL_CONFIG']['validImageTypes'], // Allowed extension types
		'doNotOvewrite' => true // Do not overwrite files in destination folder

		// You can also use the default features of fileTree widget such as:
		// multiple, orderField, isGallery, isDownloads
	),
	'sql'                     => "blob NULL"
);
```

Contributors
-------------------

* Andreas Schempp <andreas.schempp@terminal42.ch>
* Kamil Kuzminski <kamil.kuzminski@codefog.pl>
