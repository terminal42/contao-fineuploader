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
 * Class FormFineUploader
 *
 * Provide methods to handle input field "fine uploader".
 */
class FormFineUploader extends FineUploaderBase
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
    protected $strTemplate = 'form_widget';

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
        // Execute the AJAX actions in front end
        if (\Environment::get('isAjaxRequest') && \Input::get('no_ajax') != 1)
        {
            $objHandler = new FineUploaderAjax();
            $objHandler->executeAjaxActions($this->arrConfiguration);
            return;
        }

        parent::__construct($arrAttributes);
        $this->blnIsMultiple = $this->arrConfiguration['multiple'];
        $this->blnIsGallery = $this->arrConfiguration['isGallery'];
        $this->blnIsDownloads = $this->arrConfiguration['isDownloads'];

        // Include the assets
        $GLOBALS['TL_JAVASCRIPT']['fineuploader'] = 'system/modules/fineuploader/assets/fineuploader/fineuploader-5.0.2.min.js';
        $GLOBALS['TL_JAVASCRIPT']['fineuploader_handler'] = 'system/modules/fineuploader/assets/handler.min.js';
        $GLOBALS['TL_CSS']['fineuploader'] = 'system/modules/fineuploader/assets/fineuploader/fineuploader-5.0.2.min.css';
    }


    /**
     * Validate the upload
     * @return string
     */
    public function validateUpload()
    {
        $varInput = parent::validateUpload();

        // Check image size
        if (($arrImageSize = @getimagesize(TL_ROOT . '/' . $varInput)) !== false) {

            // Image exceeds maximum image width
            if ($arrImageSize[0] > $GLOBALS['TL_CONFIG']['imageWidth']) {
                $this->addError(sprintf($GLOBALS['TL_LANG']['ERR']['filewidth'], '', $GLOBALS['TL_CONFIG']['imageWidth']));
            }

            // Image exceeds maximum image height
            if ($arrImageSize[1] > $GLOBALS['TL_CONFIG']['imageHeight']) {
                $this->addError(sprintf($GLOBALS['TL_LANG']['ERR']['fileheight'], '', $GLOBALS['TL_CONFIG']['imageHeight']));
            }
        }

        return $varInput;
    }


    /**
     * Store the file information in the session
     * @param mixed
     * @return mixed
     */
    protected function validator($varInput)
    {
        $varReturn = parent::validator($varInput);
        $intCount = 0;

        foreach ((array) $varReturn as $varFile)
        {
            // Get the file model
            if (\Validator::isBinaryUuid($varFile))
            {
                $objModel = \FilesModel::findByUuid($varFile);

                if ($objModel === null)
                {
                    continue;
                }

                $varFile = $objModel->path;
            }

            $objFile = new \File($varFile, true);

            $_SESSION['FILES'][$this->strName . '_' . $intCount++] = array
            (
                'name'     => $objFile->path,
                'type'     => $objFile->mime,
                'tmp_name' => TL_ROOT . '/' . $objFile->path,
                'error'    => 0,
                'size'     => $objFile->size,
                'uploaded' => true,
                'uuid'     => ($objModel !== null) ? \String::binToUuid($objFile->uuid) : ''
            );
        }

        return $varReturn;
    }


    /**
     * Generate the widget and return it as string
     * @return string
     */
    public function generate()
    {
        $arrSet = array();
        $arrValues = array();

        if (!empty($this->varValue)) // Can be an array
        {
            $arrUuids = array();
            $arrTemp = array();
            $this->varValue = (array) $this->varValue;

            foreach ($this->varValue as $varFile)
            {
                if (\Validator::isBinaryUuid($varFile))
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
        }

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

        $strSet = implode(',', $arrSet);

        $return = '<div><div>
  <input type="hidden" name="'.$this->strName.'_fineuploader" id="ctrl_'.$this->strId.'_fineuploader" value="">
  <input type="hidden" name="'.$this->strName.'" id="ctrl_'.$this->strId.'" value="'.$strSet.'">
  <div class="selector_container">
    <ul id="sort_'.$this->strId.'"'.($this->blnIsGallery ? ' class="sgallery"' : '').'>';

        foreach ($arrValues as $k=>$v)
        {
            $return .= '<li data-id="'.(in_array($k, $arrTemp) ? $k : \String::binToUuid($k)).'">
<a href="#" class="delete" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['delete']).'" onclick="ContaoFineUploader.deleteItem(this, \''.$this->strId.'\');return false;"></a>
'.$v.'
</li>';
        }

        $objTemplate = new \FrontendTemplate($this->arrConfiguration['uploaderTemplate'] ? $this->arrConfiguration['uploaderTemplate'] : 'fineuploader_default');

        $return .= '</div>' . $objTemplate->parse() . '
  <div id="'.$this->strId.'_fineuploader" class="upload_container"></div>
  <script>
  ContaoFineUploader.init(document.getElementById("'.$this->strId.'_fineuploader"), {
      field: "'.$this->strId.'",
      request_token: "'.REQUEST_TOKEN.'",
      backend: false,
      extensions: '.json_encode(trimsplit(',', $this->arrConfiguration['extensions'])).',
      limit: '.($this->arrConfiguration['uploaderLimit'] ? $this->arrConfiguration['uploaderLimit'] : 0).',
      sizeLimit: '.($this->arrConfiguration['maxlength'] ? $this->arrConfiguration['maxlength'] : 0).'
    },
    {'.($this->arrConfiguration['uploaderConfig'] ? $this->arrConfiguration['uploaderConfig'] : "").'});
  </script>';

        return $return . '</div>';
    }
}
