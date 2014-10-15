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
 * Form fields
 */
$GLOBALS['TL_LANG']['FFL']['fineUploader'] = array('Fine uploader', 'Drag and drop Dateiuploader basierend auf dem FineUploader von Widen.');

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_form_field']['chunking']  = array('Chunking aktivieren', 'Aktivieren Sie Chunking, wenn Sie grosse Dateien hochladen möchten.');
$GLOBALS['TL_LANG']['tl_form_field']['chunkSize'] = array('Chunk-Grösse in Bytes', 'Bitte geben Sie die Chunk-Grösse in Bytes ein  (1MB = 1000000 Bytes).');
$GLOBALS['TL_LANG']['tl_form_field']['prefix']  = array('Datei-Prefix','Wenn sie etwas for den Dateinamen schreiben wollen, benutzten sie dieses Feld. Wenn sie ###feld-POST-name### benutzten und den Mittelteil durch den Wert des Names-Attribut eines Inputfeldes ihres Formular ersetzen, wird der Wert dieses Feldes genommen.');