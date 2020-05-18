<?php

declare(strict_types=1);

/*
 * FineUploader Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2020, terminal42 gmbh
 * @author     terminal42 <https://terminal42.ch>
 * @license    MIT
 */

$GLOBALS['BE_FFL']['fineUploader'] = 'Terminal42\FineUploaderBundle\Widget\BackendWidget';

/*
 * Front end form fields
 */
$GLOBALS['TL_FFL']['fineUploader'] = 'Terminal42\FineUploaderBundle\Widget\FrontendWidget';

/*
 * Hooks
 */
$GLOBALS['TL_HOOKS']['executePostActions'][] = ['terminal42_fineuploader.listener.backend', 'onExecutePostActions'];
$GLOBALS['TL_HOOKS']['loadDataContainer'][] = ['terminal42_fineuploader.listener.backend', 'onLoadDataContainer'];
