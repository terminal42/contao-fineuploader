<?php

declare(strict_types=1);

/*
 * FineUploader Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2020, terminal42 gmbh
 * @author     terminal42 <https://terminal42.ch>
 * @license    MIT
 */

$GLOBALS['TL_LANG']['FFL']['fineUploader'] = [
    'Fine uploader',
    'Trascina e carica file basato su FineUploader di Widen.',
];

/*
 * Fields
 */
$GLOBALS['TL_LANG']['tl_form_field']['maxConnections'] = [
    'Richieste simultanee massime consentite',
    'Qui è possibile impostare le richieste simultanee massime consentite per client.',
];
$GLOBALS['TL_LANG']['tl_form_field']['chunking'] = [
    'Abilita suddivisione',
    'Abilita la suddivisione dei file. È utile caricare file di grandi dimensioni.',
];
$GLOBALS['TL_LANG']['tl_form_field']['addToDbafs'] = [
    'Aggiungi a DBAFS',
    'Aggiungi il file al file system assistito dal database. Nota: il widget restituirà UUID anziché un percorso.',
];
$GLOBALS['TL_LANG']['tl_form_field']['chunkSize'] = [
    'Dimensione del blocco in byte',
    'Inserisci la dimensione del blocco in byte (1MB = 1000000 bytes).',
];
$GLOBALS['TL_LANG']['tl_form_field']['concurrent'] = [
    'Abilita blocci simultanei',
    'Attiva questa casella di controllo per abilitare la suddivisione simultanea. Verificare anche l\'impostazione "Numero massimo di connessioni".',
];
$GLOBALS['TL_LANG']['tl_form_field']['uploadButtonLabel'] = [
    'Etichetta del pulsante di caricamento',
    'Qui puoi personalizzare un\'etichetta del pulsante di caricamento.',
];
$GLOBALS['TL_LANG']['tl_form_field']['maxWidth'] = [
    'Larghezza massima (in pixels)',
    'Qui puoi inserire una larghezza massima di un\'immagine in pixel. Immettere 0 per utilizzare i valori predefiniti del sistema.',
];
$GLOBALS['TL_LANG']['tl_form_field']['maxHeight'] = [
    'Altezza massima (in pixels)',
    'Qui puoi inserire l\'altezza massima di un\'immagine in pixel. Immettere 0 per utilizzare i valori predefiniti del sistema.',
];
