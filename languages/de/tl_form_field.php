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
 * Form fields
 */
$GLOBALS['TL_LANG']['FFL']['fineUploader'] = array('Fine uploader', 'Drag and drop Dateiuploader basierend auf dem FineUploader von Widen.');

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_form_field']['maxConnections']  = array('Maximale Anzahl an Verbindungen', 'Geben Sie hier ein wie viele Verbindungen der gleiche Client maximal haben darf.');
$GLOBALS['TL_LANG']['tl_form_field']['chunking']        = array('Chunking aktivieren', 'Aktivieren Sie Chunking, wenn Sie grosse Dateien hochladen möchten.');
$GLOBALS['TL_LANG']['tl_form_field']['addToDbafs']      = array('Zum DBAFS hinzufügen', 'Die Datei zum datenbankunterstützten Dateisystem hinzufügen. Bitte beachten: In diesem Fall gibt das Formularfeld eine UUID statt dem Pfad zurück.');
$GLOBALS['TL_LANG']['tl_form_field']['chunkSize']       = array('Chunk-Grösse in Bytes', 'Bitte geben Sie die Chunk-Grösse in Bytes ein  (1MB = 1000000 Bytes).');
$GLOBALS['TL_LANG']['tl_form_field']['concurrent']      = array('Simultanes Hochladen aktivieren', 'Aktivieren Sie hier den simultanen Dateiupload. Beachten Sie auch die "Maximale Anzahl an Verbindungen" Einstellungsmöglichkeit.');
