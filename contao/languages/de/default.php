<?php

/*
 * FineUploader Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2020, terminal42 gmbh
 * @author     terminal42 <https://terminal42.ch>
 * @license    MIT
 */

$GLOBALS['TL_LANG']['ERR']['fileminwidth'] = 'Datei %s benötigt eine Mindestbreite von %d Pixel!';
$GLOBALS['TL_LANG']['ERR']['fileminheight'] = 'Datei %s benötigt eine Mindesthöhe von %d Pixel!';
$GLOBALS['TL_LANG']['MSC']['fineuploader.error'] = 'Ein unbekannter Fehler ist aufgetreten.';
$GLOBALS['TL_LANG']['MSC']['fineuploader.drop'] = 'Datei zum Hochladen hierhin ziehen';
$GLOBALS['TL_LANG']['MSC']['fineuploader.upload'] = 'Datei hochladen';
$GLOBALS['TL_LANG']['MSC']['fineuploader.processing'] = 'Dateien werden hochgeladen…';
$GLOBALS['TL_LANG']['MSC']['fineuploader.cancel'] = 'Abbrechen';
$GLOBALS['TL_LANG']['MSC']['fineuploader.retry'] = 'Wiederholen';
$GLOBALS['TL_LANG']['MSC']['fineuploader.delete'] = 'Löschen';
$GLOBALS['TL_LANG']['MSC']['fineuploader.close'] = 'Schliessen';
$GLOBALS['TL_LANG']['MSC']['fineuploader.yes'] = 'Ja';
$GLOBALS['TL_LANG']['MSC']['fineuploader.no'] = 'Nein';

/*
 * FineUploader translations
 */
$GLOBALS['TL_LANG']['MSC']['fineuploader.trans.text'] = [
    // {percent}% of {total_size}
    'formatProgress' => '{percent}% von {total_size}',

    // Upload failed
    'failUpload' => 'Hochladen fehlgeschlagen',

    // Processing...
    'waitingForResponse' => 'Wird bearbeitet…',

    // Paused...
    'paused' => 'Pausiert…',
];

$GLOBALS['TL_LANG']['MSC']['fineuploader.trans.messages'] = [
    // {file} has an invalid extension. Valid extension(s): {extensions}.
    'typeError' => '{file} hat eine invalide Dateiendung. Valide Dateiendung(en): {extensions}',

    // {file} is too large, maximum file size is {sizeLimit}.
    'sizeError' => '{file} ist zu gross. Maximale Dateigrösse ist {sizeLimit}',

    // {file} is too small, minimum file size is {minSizeLimit}.
    'minSizeError' => '{file} ist zu klein. Minimale Dateigrösse ist {minSizeLimit}',

    // {file} is empty, please select files again without it.
    'emptyError' => '{file} ist leer, bitte wählen Sie die Dateien erneut ohne diese Datei.',

    // No files to upload.
    'noFilesError' => 'Keine Dateien zum Hochladen.',

    // Too many items ({netItems}) would be uploaded. Item limit is {itemLimit}.
    'tooManyItemsError' => 'Zu viele Dateien. {netItems} würden hochgeladen, das Limit ist bei {itemLimit}.',

    // Image is too tall.
    'maxHeightImageError' => 'Das Bild ist zu hoch.',

    // Image is too wide.
    'maxWidthImageError' => 'Das Bild ist zu breit.',

    // Image is not tall enough.
    'minHeightImageError' => 'Das Bild ist nicht hoch genug.',

    // Image is not wide enough.
    'minWidthImageError' => 'Das Bild ist nicht breit genug.',

    // Retry failed - you have reached your file limit.
    'retryFailTooManyItems' => 'Wiederholen fehlgeschlagen, Sie haben das Dateilimit erreicht.',

    // The files are being uploaded, if you leave now the upload will be canceled.
    'onLeave' => 'Dateien werden hochgeladen, wenn Sie die Seite jetzt verlassen, wird der Upload abgebrochen.',

    // Unrecoverable error - this browser does not permit file uploading of any kind due to serious bugs in iOS8 Safari.  Please use iOS8 Chrome until Apple fixes these issues.
    'unsupportedBrowserIos8Safari' => 'Nicht zu behebender Fehler: Dieser Browser erlaubt keinen Dateiupload bedingt durch schwere FEhler in iOS8 Safari. Bitte nutzen Sie Chrome bis Apple diese Fehler behebt.',
];

$GLOBALS['TL_LANG']['MSC']['fineuploader.trans.retry'] = [
    // Retrying {retryNum}/{maxAuto}...
    'autoRetryNote' => 'Wiederhole {retryNum}/{maxAuto}…',
];

$GLOBALS['TL_LANG']['MSC']['fineuploader.trans.deleteFile'] = [
    // Are you sure you want to delete {filename}?
    'confirmMessage' => 'Sind Sie sicher, dass Sie die Datei {filename} löschen wollen?',

    // Deleting...
    'deletingStatusText' => 'Löschen…',

    // Delete failed
    'deletingFailedText' => 'Löschen fehlgeschlagen',
];

$GLOBALS['TL_LANG']['MSC']['fineuploader.trans.paste'] = [
    // Please name this image
    'namePromptMessage' => 'Bitte benennen Sie dieses Bild',
];
