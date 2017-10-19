<?php

/**
 * Back end form fields
 */
$GLOBALS['BE_FFL']['fineUploader'] = 'Terminal42\FineUploaderBundle\Widget\BackendWidget';

/**
 * Front end form fields
 */
$GLOBALS['TL_FFL']['fineUploader'] = 'Terminal42\FineUploaderBundle\Widget\FrontendWidget';

/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['executePostActions'][] = ['terminal42_fineuploader.listener.backend', 'onExecutePostActions'];
$GLOBALS['TL_HOOKS']['loadDataContainer'][]  = ['terminal42_fineuploader.listener.backend', 'onLoadDataContainer'];