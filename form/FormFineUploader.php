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
    protected $strTemplate = 'fineuploader_frontend';

    /**
     * The CSS class prefix
     *
     * @var string
     */
    protected $strPrefix = 'widget widget-fineuploader';

    /**
     * Multiple flag
     * @var boolean
     */
    protected $blnIsMultiple = false;

    /**
     * Values are already prepared
     * @var boolean
     */
    protected $blnValuesPrepared = false;

    /**
     * Load the database object
     * @param array
     */
    public function __construct($arrAttributes=null)
    {
        // Execute the AJAX actions in front end
        if (\Environment::get('isAjaxRequest') && ($arrAttributes['id'] === \Input::post('name') || $arrAttributes['name'] === \Input::post('name')) && \Input::get('no_ajax') != 1) {
            $this->addAttributes($arrAttributes);

            $objHandler = new FineUploaderAjax();
            $objHandler->executeAjaxActions($this->arrConfiguration);

            return;
        }

        parent::__construct($arrAttributes);
        $this->blnIsMultiple    = $this->arrConfiguration['multiple'];
        $this->blnIsGallery     = $this->arrConfiguration['isGallery'];
        $this->blnIsDownloads   = $this->arrConfiguration['isDownloads'];

        if (!$this->blnIsMultiple) {
            $this->arrConfiguration['uploaderLimit'] = 1;
        }

        // Include the assets
        $GLOBALS['TL_JAVASCRIPT']['fineuploader']         = 'system/modules/fineuploader/assets/fine-uploader/fine-uploader.min.js';
        $GLOBALS['TL_JAVASCRIPT']['fineuploader_handler'] = 'system/modules/fineuploader/assets/handler.min.js';
        $GLOBALS['TL_CSS']['fineuploader']                = 'system/modules/fineuploader/assets/fine-uploader/fine-uploader.min.css';
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
        $arrReturn = array_filter((array) $varReturn);
        $intCount = 0;

        foreach ($arrReturn as $varFile) {
            // Get the file model
            if (\Validator::isUuid($varFile)) {
                $objModel = \FilesModel::findByUuid($varFile);

                if ($objModel === null) {
                    continue;
                }

                $varFile = $objModel->path;
            }

            $objFile = new \File($varFile, true);

            $_SESSION['FILES'][$this->strName . '_' . $intCount++] = array
            (
                'name'     => $objFile->name,
                'type'     => $objFile->mime,
                'tmp_name' => TL_ROOT . '/' . $objFile->path,
                'error'    => 0,
                'size'     => $objFile->size,
                'uploaded' => true,
                'uuid'     => ($objModel !== null) ? \StringUtil::binToUuid($objModel->uuid) : ''
            );
        }

        return $varReturn;
    }

    /**
     * Generate the widget and return it as string
     * @param array
     * @return string
     */
    public function parse($arrAttributes=null)
    {
        if (!$this->blnValuesPrepared) {
            $arrSet = array();
            $arrValues = array();
            $arrUuids = array();
            $arrTemp = array();

            if (!empty($this->varValue)) { // Can be an array

                $this->varValue = (array) $this->varValue;

                foreach ($this->varValue as $varFile) {
                    if (\Validator::isUuid($varFile)) {
                        $arrUuids[] = $varFile;
                    } else {
                        $arrTemp[] = $varFile;
                    }
                }

                $objFiles = \FilesModel::findMultipleByUuids($arrUuids);

                // Get the database files
                if ($objFiles !== null) {
                    while ($objFiles->next()) {
                        $chunk = $this->generateFileItem($objFiles->path);

                        if (strlen($chunk)) {
                            $arrValues[$objFiles->uuid] = array
                            (
                                'id' => (in_array($objFiles->uuid, $arrTemp) ? $objFiles->uuid : \StringUtil::binToUuid($objFiles->uuid)),
                                'value' => $chunk
                            );

                            $arrSet[] = $objFiles->uuid;
                        }
                    }
                }

                // Get the temporary files
                foreach ($arrTemp as $varFile) {
                    $chunk = $this->generateFileItem($varFile);

                    if (strlen($chunk)) {
                        $arrValues[$varFile] = array
                        (
                            'id' => (in_array($varFile, $arrTemp) ? $varFile : \StringUtil::binToUuid($varFile)),
                            'value' => $chunk
                        );

                        $arrSet[] = $varFile;
                    }
                }
            }

            // Parse the set array
            foreach ($arrSet as $k=>$v) {
                if (in_array($v, $arrTemp)) {
                    $strSet[$k] = $v;
                } else {
                    $arrSet[$k] = \StringUtil::binToUuid($v);
                }
            }

            $this->set = implode(',', $arrSet);
            $this->values = $arrValues;
            $this->deleteTitle = specialchars($GLOBALS['TL_LANG']['MSC']['delete']);
            $this->extensions = json_encode(trimsplit(',', $this->arrConfiguration['extensions']));
            $this->limit = $this->arrConfiguration['uploaderLimit'] ? $this->arrConfiguration['uploaderLimit'] : 0;
            $this->minSizeLimit = $this->arrConfiguration['minlength'] ? $this->arrConfiguration['minlength'] : 0;
            $this->sizeLimit = $this->arrConfiguration['maxlength'] ? $this->arrConfiguration['maxlength'] : 0;
            $this->chunkSize = $this->arrConfiguration['chunkSize'] ? $this->arrConfiguration['chunkSize'] : 0;
            $this->concurrent = $this->arrConfiguration['concurrent'] ? true : false;
            $this->maxConnections = $this->arrConfiguration['maxConnections'] ? $this->arrConfiguration['maxConnections'] : 3;

            $this->blnValuesPrepared = true;
        }

        return parent::parse($arrAttributes);
    }

    /**
     * Use the parse() method instead.
     *
     * @throw \BadMethodCallException
     */
    public function generate()
    {
        throw new \BadMethodCallException('Please use the parse() method instead!');
    }
}
