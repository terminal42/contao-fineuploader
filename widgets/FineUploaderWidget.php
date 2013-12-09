<?php

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *
 * PHP version 5
 * @copyright  terminal42 gmbh 2009-2013
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Kamil Kuźmiński <kamil.kuzminski@codefog.pl>
 * @license    LGPL
 */

namespace Contao;


/**
 * Class FineUploaderWidget
 *
 * Provide methods to handle input field "fine uploader".
 */
class FineUploaderWidget extends \Widget
{

    /**
     * Submit user input
     * @var boolean
     */
    protected $blnSubmitInput = true;

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'be_widget';

    /**
     * Order ID
     * @var string
     */
    protected $strOrderId;

    /**
     * Order name
     * @var string
     */
    protected $strOrderName;

    /**
     * Order field
     * @var string
     */
    protected $strOrderField;

    /**
     * Show files
     * @var boolean
     */
    protected $blnIsDownloads = false;

    /**
     * Gallery flag
     * @var boolean
     */
    protected $blnIsGallery = false;

    /**
     * Multiple flag
     * @var boolean
     */
    protected $blnIsMultiple = false;


    /**
     * Load the database object
     * @param array
     */
    public function __construct($arrAttributes=null)
    {
        if (TL_MODE == 'FE')
        {
            $this->strTemplate = 'form_widget';

            // Execute the AJAX actions in front end
            if (\Environment::get('isAjaxRequest') && \Input::get('no_ajax') != 1)
            {
                $objHandler = new \FineUploader();
                $objHandler->executeAjaxActions($this->arrConfiguration);
                return;
            }
        }

        parent::__construct($arrAttributes);
        $this->strOrderField = $this->arrConfiguration['orderField'];
        $this->blnIsMultiple = $this->arrConfiguration['multiple'];

        // Prepare the order field
        if (TL_MODE == 'BE' && $this->strOrderField != '')
        {
            $this->strOrderId = $this->strOrderField . str_replace($this->strField, '', $this->strId);
            $this->strOrderName = $this->strOrderField . str_replace($this->strField, '', $this->strName);

            // Retrieve the order value
            $objRow = \Database::getInstance()->prepare("SELECT {$this->strOrderField} FROM {$this->strTable} WHERE id=?")
                                              ->limit(1)
                                              ->execute($this->activeRecord->id);

            $tmp = deserialize($objRow->{$this->strOrderField});
            $this->{$this->strOrderField} = (!empty($tmp) && is_array($tmp)) ? array_filter($tmp) : array();
        }

        $this->blnIsGallery = $this->arrConfiguration['isGallery'];
        $this->blnIsDownloads = $this->arrConfiguration['isDownloads'];

        // Include the assets
        $GLOBALS['TL_JAVASCRIPT']['fineuploader'] = 'system/modules/fineuploader/assets/fineuploader/fineuploader-4.0.1.min.js';
        $GLOBALS['TL_JAVASCRIPT']['fineuploader_handler'] = 'system/modules/fineuploader/assets/handler.min.js';

        if (TL_MODE == 'FE')
        {
            $GLOBALS['TL_JAVASCRIPT']['mootao'] = 'assets/mootools/mootao/Mootao.js';
        }

        if (TL_MODE == 'BE')
        {
            $GLOBALS['TL_CSS']['fineuploader_handler'] = 'system/modules/fineuploader/assets/handler.min.css';
        }
    }


    /**
     * Validate the upload
     * @return string
     */
    public function validateUpload()
    {
        $objUploader = new \FileUpload();
        $objUploader->setName($this->strName);

        // Convert the $_FILES array to Contao format
        if (!empty($_FILES[$this->strName]))
        {
            $arrFile = array
            (
                'name' => array($_FILES[$this->strName]['name']),
                'type' => array($_FILES[$this->strName]['type']),
                'tmp_name' => array($_FILES[$this->strName]['tmp_name']),
                'error' => array($_FILES[$this->strName]['error']),
                'size' => array($_FILES[$this->strName]['size']),
            );

            // Check if the file exists
            if (file_exists(TL_ROOT . '/system/tmp/' . $arrFile['name'][0]))
            {
                $arrFile['name'][0] = $this->getFileName($arrFile['name'][0], 'system/tmp');
            }

            $_FILES[$this->strName] = $arrFile;
        }

        $varInput = '';
        $maxlength = null;

        // Override the default maxlength value
        if (isset($this->arrConfiguration['maxlength']))
        {
            $maxlength = $GLOBALS['TL_CONFIG']['maxFileSize'];
            $GLOBALS['TL_CONFIG']['maxFileSize'] = $this->arrConfiguration['maxlength'];
        }

        try
        {
            $varInput = $objUploader->uploadTo('system/tmp');
            \Message::reset();
        }
        catch (\Exception $e)
        {
            $this->addError($e->getMessage());
        }

        // Restore the default maxlength value
        if ($maxlength !== null)
        {
            $GLOBALS['TL_CONFIG']['maxFileSize'] = $maxlength;
        }

        if (!is_array($varInput) || empty($varInput))
        {
            $this->addError('Unknown error occured.');
        }

        return $varInput[0];
    }


    /**
     * Return an array if the "multiple" attribute is set
     * @param mixed
     * @return mixed
     */
    protected function validator($varInput)
    {
        $arrMapper = array();
        $varReturn = $this->blnIsMultiple ? '' : 0;
        $strDestination = $GLOBALS['TL_CONFIG']['uploadPath'];

        // Specify the target folder in the DCA (eval)
        if (isset($this->arrConfiguration['uploadFolder']))
        {
            $strDestination = $this->arrConfiguration['uploadFolder'];
        }

        // Return the value as usual
        if ($varInput == '' && $this->mandatory)
        {
            $this->addError(sprintf($GLOBALS['TL_LANG']['ERR']['mandatory'], $this->strLabel));
        }
        elseif (strpos($varInput, ',') === false)
        {
            if (!\Validator::isUuid($varInput))
            {
                $strUuid = $this->moveTemporaryFile($varInput, $strDestination);
                $arrMapper[$varInput] = $strUuid;
                $varInput = $strUuid;
            }

            $varInput = \String::uuidToBin($varInput);
            $varReturn = $this->blnIsMultiple ? array($varInput) : $varInput;
        }
        else
        {
            $arrValue = array_filter(explode(',', $varInput));

            // Limit the number of uploads
            if ($this->arrConfiguration['uploaderLimit'] > 0)
            {
                $arrValue = array_slice($arrValue, 0, $this->arrConfiguration['uploaderLimit']);
            }

            foreach ($arrValue as $k => $v)
            {
                if (!\Validator::isUuid($v))
                {
                    $arrValue[$k] = $this->moveTemporaryFile($v, $strDestination);
                    $arrMapper[$v] = $arrValue[$k];
                }
            }

            $varReturn = $this->blnIsMultiple ? array_map('String::uuidToBin', $arrValue) : \String::uuidToBin($arrValue[0]);
        }

        // Store the order value
        if (TL_MODE == 'BE' && $this->strOrderField != '')
        {
            $arrNew = explode(',', \Input::post($this->strOrderName));

            foreach ($arrNew as $k => $v)
            {
                if (!\Validator::isUuid($v))
                {
                    $arrNew[$k] = $arrMapper[$v];
                }
            }

            $arrNew = array_map('String::uuidToBin', $arrNew);

            // Only proceed if the value has changed
            if ($arrNew !== $this->{$this->strOrderField})
            {
                $objVersions = new Versions($this->strTable, \Input::get('id'));
                $objVersions->initialize();

                \Database::getInstance()->prepare("UPDATE {$this->strTable} SET tstamp=?, {$this->strOrderField}=? WHERE id=?")
                                        ->execute(time(), serialize($arrNew), \Input::get('id'));

                $objVersions->create(); // see #6285
            }
        }

        return $varReturn;
    }


    /**
     * Move the temporary file to its destination and return the UUID
     * @param string
     * @param string
     * @return string
     */
    protected function moveTemporaryFile($strFile, $strDestination)
    {
        if (!is_file(TL_ROOT . '/' . $strFile))
        {
            return '';
        }

        $strNew = $strDestination . '/' . basename($strFile);

        // Do not overwrite existing files
        if ($this->arrConfiguration['doNotOverwrite'])
        {
            $strNew = $strDestination . '/' . $this->getFileName(basename($strFile), $strDestination);
        }

        if (\Files::getInstance()->rename($strFile, $strNew))
        {
            $objModel = \Dbafs::addResource($strNew);

            if (!$objModel !== null)
            {
                return \String::binToUuid($objModel->uuid);
            }
        }

        return '';
    }


    /**
     * Get the new file name if it already exists in the folder
     * @param string
     * @param string
     * @return string
     */
    protected function getFileName($strFile, $strFolder)
    {
        if (!file_exists(TL_ROOT . '/' . $strFolder . '/' . $strFile))
        {
            return $strFile;
        }

        $offset = 1;
        $pathinfo = pathinfo($strFile);
        $name = $pathinfo['filename'];

        $arrAll = scan(TL_ROOT . '/' . $strFolder);
        $arrFiles = preg_grep('/^' . preg_quote($name, '/') . '.*\.' . preg_quote($pathinfo['extension'], '/') . '/', $arrAll);

        foreach ($arrFiles as $file)
        {
            if (preg_match('/__[0-9]+\.' . preg_quote($pathinfo['extension'], '/') . '$/', $file))
            {
                $file = str_replace('.' . $pathinfo['extension'], '', $file);
                $intValue = intval(substr($file, (strrpos($file, '_') + 1)));

                $offset = max($offset, $intValue);
            }
        }

        return str_replace($name, $name . '__' . ++$offset, $strFile);
    }


    /**
     * Generate the widget and return it as string
     * @return string
     */
    public function generate()
    {
        $arrSet = array();
        $arrValues = array();
        $blnHasOrder = ($this->strOrderField != '' && is_array($this->{$this->strOrderField}) && TL_MODE == 'BE');

        if (!empty($this->varValue)) // Can be an array
        {
            $arrUuids = array();
            $arrTemp = array();
            $this->varValue = (array) $this->varValue;

            foreach ($this->varValue as $varFile)
            {
                if (\Validator::isUuid($varFile))
                {
                    $arrUuids[] = $varFile;
                }
                else
                {
                    $arrTemp[] = $varFile;
                }
            }

            $objFiles = \FilesModel::findMultipleByUuids($arrUuids);

            // Get the database files
            if ($objFiles !== null)
            {
                while ($objFiles->next())
                {
                    $chunk = $this->generateFileItem($objFiles->path);

                    if (strlen($chunk))
                    {
                        $arrValues[$objFiles->uuid] = $chunk;
                        $arrSet[] = $objFiles->uuid;
                    }
                }
            }

            // Get the temporary files
            foreach ($arrTemp as $varFile)
            {
                $chunk = $this->generateFileItem($varFile);

                if (strlen($chunk))
                {
                    $arrValues[$varFile] = $chunk;
                    $arrSet[] = $varFile;
                }
            }

            // Apply a custom sort order
            if ($blnHasOrder)
            {
                $arrNew = array();

                foreach ($this->{$this->strOrderField} as $i)
                {
                    if (isset($arrValues[$i]))
                    {
                        $arrNew[$i] = $arrValues[$i];
                        unset($arrValues[$i]);
                    }
                }

                if (!empty($arrValues))
                {
                    foreach ($arrValues as $k=>$v)
                    {
                        $arrNew[$k] = $v;
                    }
                }

                $arrValues = $arrNew;
                unset($arrNew);
            }
        }

        // Load the fonts for the drag hint (see #4838)
        $GLOBALS['TL_CONFIG']['loadGoogleFonts'] = true;

        // Parse the set array
        foreach ($arrSet as $k=>$v)
        {
            if (in_array($v, $arrTemp))
            {
                $strSet[$k] = $v;
            }
            else
            {
                $arrSet[$k] = \String::binToUuid($v);
            }
        }

        // Convert the binary UUIDs
        $strSet = implode(',', $arrSet);
        $strOrder = $blnHasOrder ? implode(',', array_map('String::binToUuid', $this->{$this->strOrderField})) : '';

        $return = '<input type="hidden" name="'.$this->strName.'" id="ctrl_'.$this->strId.'" value="'.$strSet.'">' . ($blnHasOrder ? '
  <input type="hidden" name="'.$this->strOrderName.'" id="ctrl_'.$this->strOrderId.'" value="'.$strOrder.'">' : '') . '
  <div class="selector_container">' . (($blnHasOrder && count($arrValues)) ? '
    <p class="sort_hint">' . $GLOBALS['TL_LANG']['MSC']['dragItemsHint'] . '</p>' : '') . '
    <ul id="sort_'.$this->strId.'" class="'.trim(($blnHasOrder ? 'sortable ' : '').($this->blnIsGallery ? 'sgallery' : '')).'">';

        foreach ($arrValues as $k=>$v)
        {
            $return .= '<li data-id="'.(in_array($k, $arrTemp) ? $k : \String::binToUuid($k)).'">
<a href="#" class="delete" onclick="ContaoFineUploader.deleteItem(this, \''.$this->strId.'\');return false;"></a>
'.$v.'
</li>';
        }

        $return .= '</ul>' . ($blnHasOrder ? '
    <script>ContaoFineUploader.makeSortable("sort_'.$this->strId.'", "ctrl_'.$this->strOrderId.'")</script>' : '') . '
  </div>';

        if (!\Environment::get('isAjaxRequest'))
        {
            $return = '<div><div>' . $return . '</div>';
            $extensions = trimsplit(',', $this->arrConfiguration['extensions']);
            $limit = $this->arrConfiguration['uploaderLimit'] ? $this->arrConfiguration['uploaderLimit'] : 0;

            $return .= '<div id="'.$this->strId.'_fineuploader" class="upload_container"></div>
  <script>
    window.addEvent("domready", function() {
      ContaoFineUploader.init($("'.$this->strId.'_fineuploader"), {
          field: "'.$this->strId.'",
          request_token: "'.REQUEST_TOKEN.'",
          backend: '.((TL_MODE == 'FE') ? 'false' : 'true').',
          extensions: '.json_encode($extensions).',
          limit: '.(int) $this->arrConfiguration['uploaderLimit'].'
        },
        {'.($this->arrConfiguration['uploaderConfig'] ? $this->arrConfiguration['uploaderConfig'] : "").'});
    });
  </script>';

              $objTemplate = new \BackendTemplate($this->arrConfiguration['uploaderTemplate'] ? $this->arrConfiguration['uploaderTemplate'] : 'fineuploader_default');
              $return .= $objTemplate->parse() . '</div>';
        }

        return $return;
    }


    /**
     * Generate a file item and return it as HTML string
     * @param string
     * @return string
     */
    protected function generateFileItem($strPath)
    {
        if (!is_file(TL_ROOT . '/' . $strPath))
        {
            return '';
        }

        $objFile = new \File($strPath, true);
        $strInfo = $strPath . ' <span class="tl_gray">(' . $this->getReadableSize($objFile->size) . ($objFile->isGdImage ? ', ' . $objFile->width . 'x' . $objFile->height . ' px' : '') . ')</span>';
        $allowedDownload = trimsplit(',', strtolower($GLOBALS['TL_CONFIG']['allowedDownload']));
        $strReturn = '';

        // Show files and folders
        if (!$this->blnIsGallery && !$this->blnIsDownloads)
        {
            if ($objFile->isGdImage)
            {
                $strReturn = \Image::getHtml(\Image::get($strPath, 80, 60, 'center_center'), '', 'class="gimage" title="' . specialchars($strInfo) . '"');
            }
            else
            {
                $strReturn = \Image::getHtml($objFile->icon) . ' ' . $strInfo;
            }
        }

        // Show a sortable list of files only
        else
        {
            if ($this->blnIsGallery)
            {
                // Only show images
                if ($objFile->isGdImage)
                {
                    $strReturn = \Image::getHtml(\Image::get($strPath, 80, 60, 'center_center'), '', 'class="gimage" title="' . specialchars($strInfo) . '"');
                }
            }
            else
            {
                // Only show allowed download types
                if (in_array($objFile->extension, $allowedDownload) && !preg_match('/^meta(_[a-z]{2})?\.txt$/', $objFile->basename))
                {
                    $strReturn = \Image::getHtml($objFile->icon) . ' ' . $strPath;
                }
            }
        }

        return $strReturn;
    }
}
