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
 * Class FineUploaderBackend
 *
 * Provide methods to handle fine uploader backend actions.
 */
class FineUploaderBackend
{
    /**
     * Load the widget assets if they are needed. Load them here so the widget in subpalette can work as well.
     *
     * @param string $table
     */
    public function loadAssets($table)
    {
        if (TL_MODE !== 'BE' || !is_array($GLOBALS['TL_DCA'][$table]['fields'])) {
            return;
        }

        foreach ($GLOBALS['TL_DCA'][$table]['fields'] as $field) {
            if ($field['inputType'] === 'fineUploader') {
                FineUploaderWidget::includeAssets();
                break;
            }
        }
    }
}
