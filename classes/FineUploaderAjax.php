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
 * Class FineUploaderAjax
 *
 * Provide methods to handle fine uploader ajax actions.
 */
class FineUploaderAjax extends Controller
{

    /**
     * Dispatch an AJAX request
     * @param string
     * @param \DataContainer
     */
    public function dispatchAjaxRequest($strAction, \DataContainer $dc)
    {
        switch ($strAction) {
            // Upload the file
            case 'fineuploader_upload':
                $arrData['strTable'] = $dc->table;
                $arrData['id'] = $this->strAjaxName ?: $dc->id;
                $arrData['name'] = \Input::post('name');

                $objWidget = new $GLOBALS['BE_FFL']['fineUploader']($arrData, $dc);
                $strFile = $objWidget->validateUpload();

                if ($objWidget->hasErrors()) {
                    $arrResponse = array('success'=>false, 'error'=>$objWidget->getErrorAsString(), 'preventRetry'=>true);
                } else {
                    $arrResponse = array('success'=>true, 'file'=>$strFile);
                }

                echo json_encode($arrResponse);
                exit; break;

            // Reload the widget
            case 'fineuploader_reload':
                $intId = \Input::get('id');
                $strField = $dc->field = \Input::post('name');
                $this->import('Database');

                // Handle the keys in "edit multiple" mode
                if (\Input::get('act') == 'editAll') {
                    $intId = preg_replace('/.*_([0-9a-zA-Z]+)$/', '$1', $strField);
                    $strField = preg_replace('/(.*)_[0-9a-zA-Z]+$/', '$1', $strField);
                }

                // The field does not exist
                if (!isset($GLOBALS['TL_DCA'][$dc->table]['fields'][$strField])) {
                    $this->log('Field "' . $strField . '" does not exist in DCA "' . $dc->table . '"', __METHOD__, TL_ERROR);
                    header('HTTP/1.1 400 Bad Request');
                    die('Bad Request');
                }

                $objRow = null;
                $varValue = null;

                // Load the value
                if ($GLOBALS['TL_DCA'][$dc->table]['config']['dataContainer'] == 'File') {
                    $varValue = $GLOBALS['TL_CONFIG'][$strField];
                } elseif ($intId > 0 && $this->Database->tableExists($dc->table)) {
                    $objRow = $this->Database->prepare("SELECT * FROM " . $dc->table . " WHERE id=?")
                                             ->execute($intId);

                    // The record does not exist
                    if ($objRow->numRows < 1) {
                        $this->log('A record with the ID "' . $intId . '" does not exist in table "' . $dc->table . '"', __METHOD__, TL_ERROR);
                        header('HTTP/1.1 400 Bad Request');
                        die('Bad Request');
                    }

                    $varValue = $objRow->$strField;
                    $dc->activeRecord = $objRow;
                }

                // Call the load_callback
                if (is_array($GLOBALS['TL_DCA'][$dc->table]['fields'][$strField]['load_callback'])) {
                    foreach ($GLOBALS['TL_DCA'][$dc->table]['fields'][$strField]['load_callback'] as $callback) {
                        if (is_array($callback)) {
                            $this->import($callback[0]);
                            $varValue = $this->$callback[0]->$callback[1]($varValue, $dc);
                        } elseif (is_callable($callback)) {
                            $varValue = $callback($varValue, $dc);
                        }
                    }
                }

                $varValue = \Input::post('value', true);

                // Convert the selected values
                if ($varValue != '') {
                    $varValue = trimsplit(',', $varValue);

                    foreach ($varValue as $k => $v) {
                        if (\Validator::isUuid($v) && !is_file(TL_ROOT . '/' . $v)) {
                            $varValue[$k] = \String::uuidToBin($v);
                        }
                    }

                    $varValue = serialize($varValue);
                }

                // Build the attributes based on the "eval" array
                $arrAttribs = $GLOBALS['TL_DCA'][$dc->table]['fields'][$strField]['eval'];

                $arrAttribs['id'] = $dc->field;
                $arrAttribs['name'] = $dc->field;
                $arrAttribs['value'] = $varValue;
                $arrAttribs['strTable'] = $dc->table;
                $arrAttribs['strField'] = $strField;
                $arrAttribs['activeRecord'] = $dc->activeRecord;

                $objWidget = new $GLOBALS['BE_FFL']['fineUploader']($arrAttribs);
                echo $objWidget->parse();
                exit; break;
        }
    }

    /**
     * Execute AJAX actions in front end
     * @param array
     */
    public function executeAjaxActions($arrData)
    { \Input::setGet('no_ajax', 1); // Avoid circular reference

        switch (\Input::post('action')) {
            // Upload the file
            case 'fineuploader_upload':
                $arrData['name'] = \Input::post('name');
                if(isset($_POST['prefix']))
                    $arrData['prefix'] = \Input::post('prefix');

                $objWidget = new $GLOBALS['TL_FFL']['fineUploader']($arrData);
                $strFile = $objWidget->validateUpload();

                if ($objWidget->hasErrors()) {
                    $arrResponse = array('success'=>false, 'error'=>$objWidget->getErrorAsString(), 'preventRetry'=>true);
                } else {
                    $arrResponse = array('success'=>true, 'file'=>$strFile);
                }

                echo json_encode($arrResponse);
                exit; break;
        }
    }
}
