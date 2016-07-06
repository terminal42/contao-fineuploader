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
 * Miscellaneous
 */
$GLOBALS['TL_LANG']['MSC']['fineuploader_error']      = 'Ein unbekannter Fehler ist aufgetreten.';
$GLOBALS['TL_LANG']['MSC']['fineuploader_drop']       = 'Datei zum Hochladen hierhin ziehen';
$GLOBALS['TL_LANG']['MSC']['fineuploader_upload']     = 'Datei hochladen';
$GLOBALS['TL_LANG']['MSC']['fineuploader_processing'] = 'Dateien werden hochgeladen…';
$GLOBALS['TL_LANG']['MSC']['fineuploader_cancel']     = 'Abbrechen';
$GLOBALS['TL_LANG']['MSC']['fineuploader_retry']      = 'Wiederholen';
$GLOBALS['TL_LANG']['MSC']['fineuploader_delete']     = 'Löschen';
$GLOBALS['TL_LANG']['MSC']['fineuploader_close']      = 'Schliessen';
$GLOBALS['TL_LANG']['MSC']['fineuploader_yes']        = 'Ja';
$GLOBALS['TL_LANG']['MSC']['fineuploader_no']         = 'Nein';

/**
 * Fineuploader translations
 */
$GLOBALS['TL_LANG']['MSC']['fineuploader_trans']['text']['formatProgress']                    = '{percent}% von {total_size}';            // {percent}% of {total_size}
$GLOBALS['TL_LANG']['MSC']['fineuploader_trans']['text']['failUpload']                        = 'Hochladen fehlgeschlagen';               // Upload failed
$GLOBALS['TL_LANG']['MSC']['fineuploader_trans']['text']['waitingForResponse']                = 'Wird bearbeitet…';                       // Processing...
$GLOBALS['TL_LANG']['MSC']['fineuploader_trans']['text']['paused']                            = 'Pausiert…';                              // Paused...

$GLOBALS['TL_LANG']['MSC']['fineuploader_trans']['messages']['typeError']                     = '{file} hat eine invalide Dateiendung. Valide Dateiendung(en): {extensions}';            // {file} has an invalid extension. Valid extension(s): {extensions}.
$GLOBALS['TL_LANG']['MSC']['fineuploader_trans']['messages']['sizeError']                     = '{file} ist zu gross. Maximale Dateigrösse ist {sizeLimit}';                             // {file} is too large, maximum file size is {sizeLimit}.
$GLOBALS['TL_LANG']['MSC']['fineuploader_trans']['messages']['minSizeError']                  = '{file} ist zu klein. Minimale Dateigrösse ist {minSizeLimit}';                          // {file} is too small, minimum file size is {minSizeLimit}.
$GLOBALS['TL_LANG']['MSC']['fineuploader_trans']['messages']['emptyError']                    = '{file} ist leer, bitte wählen Sie die Dateien erneut ohne diese Datei.';                // {file} is empty, please select files again without it.
$GLOBALS['TL_LANG']['MSC']['fineuploader_trans']['messages']['noFilesError']                  = 'Keine Dateien zum Hochladen.';                                                          // No files to upload.
$GLOBALS['TL_LANG']['MSC']['fineuploader_trans']['messages']['tooManyItemsError']             = 'Zu viele Dateien. {netItems} würden hochgeladen, das Limit ist bei {itemLimit}.';       // Too many items ({netItems}) would be uploaded.  Item limit is {itemLimit}.
$GLOBALS['TL_LANG']['MSC']['fineuploader_trans']['messages']['maxHeightImageError']           = 'Das Bild ist zu hoch.';                                                                 // Image is too tall.
$GLOBALS['TL_LANG']['MSC']['fineuploader_trans']['messages']['maxWidthImageError']            = 'Das Bild ist zu breit.';                                                                // Image is too wide.
$GLOBALS['TL_LANG']['MSC']['fineuploader_trans']['messages']['minHeightImageError']           = 'Das Bild ist nicht hoch genug.';                                                        // Image is not tall enough.
$GLOBALS['TL_LANG']['MSC']['fineuploader_trans']['messages']['minWidthImageError']            = 'Das Bild ist nicht breit genug.';                                                       // Image is not wide enough.
$GLOBALS['TL_LANG']['MSC']['fineuploader_trans']['messages']['retryFailTooManyItems']         = 'Wiederholen fehlgeschlagen, Sie haben das Dateilimit erreicht.';                        // Retry failed - you have reached your file limit.
$GLOBALS['TL_LANG']['MSC']['fineuploader_trans']['messages']['onLeave']                       = 'Dateien werden hochgeladen, wenn Sie die Seite jetzt verlassen, wird der Upload abgebrochen.';               // The files are being uploaded, if you leave now the upload will be canceled.
$GLOBALS['TL_LANG']['MSC']['fineuploader_trans']['messages']['unsupportedBrowserIos8Safari']  = 'Nicht zu behebender Fehler: Dieser Browser erlaubt keinen Dateiupload bedingt durch schwere FEhler in iOS8 Safari. Bitte nutzen Sie Chrome bis Apple diese Fehler behebt.';               // Unrecoverable error - this browser does not permit file uploading of any kind due to serious bugs in iOS8 Safari.  Please use iOS8 Chrome until Apple fixes these issues.

$GLOBALS['TL_LANG']['MSC']['fineuploader_trans']['retry']['autoRetryNote']                    = 'Wiederhole {retryNum}/{maxAuto}…';                                                      // Retrying {retryNum}/{maxAuto}...

$GLOBALS['TL_LANG']['MSC']['fineuploader_trans']['deleteFile']['confirmMessage']              = 'Sind Sie sicher, dass Sie die Datei {filename} löschen wollen?';                        // Are you sure you want to delete {filename}?
$GLOBALS['TL_LANG']['MSC']['fineuploader_trans']['deleteFile']['deletingStatusText']          = 'Löschen…';                                                                              // Deleting...
$GLOBALS['TL_LANG']['MSC']['fineuploader_trans']['deleteFile']['deletingFailedText']          = 'Löschen fehlgeschlagen';                                                                // Delete failed

$GLOBALS['TL_LANG']['MSC']['fineuploader_trans']['paste']['namePromptMessage']                = 'Bitte benennen Sie dieses Bild';                                                        // Please name this image
