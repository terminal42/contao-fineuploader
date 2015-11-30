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
 * Class FineUploaderBase
 *
 * Parent class for "fine uploader" widgets.
 */
abstract class FineUploaderBase extends \Widget
{
    /**
     * Temporary upload path
     * @var string
     */
    protected $strTemporaryPath = 'system/tmp';

    /**
     * Initialize the object
     * @param array
     */
    public function __construct($arrAttributes=null)
    {
        parent::__construct($arrAttributes);

        // Clean the chunks session when the widget is initialized
        if (!\Environment::get('isAjaxRequest')) {
            unset($_SESSION[$this->strName . '_FINEUPLOADER_CHUNKS']);
        }
    }

    /**
     * Add the required attribute if mandatory
     *
     * @param string
     * @param mixed
     */
    public function __set($strKey, $varValue)
    {
        switch ($strKey) {
            case 'mandatory':
                if ($varValue) {
                    $this->arrAttributes['required'] = 'required';
                } else {
                    unset($this->arrAttributes['required']);
                }
                // DO NOT BREAK HERE
        }

        parent::__set($strKey, $varValue);
    }

    /**
     * Validate the upload
     * @return string
     */
    public function validateUpload()
    {
        \Message::reset();
        $strTempName = $this->strName . '_fineuploader';
        $objUploader = new \FileUpload();
        $objUploader->setName($this->strName);
        $blnIsChunk = isset($_POST['qqpartindex']);

        // Convert the $_FILES array to Contao format
        if (!empty($_FILES[$strTempName])) {
            $arrFile = array
            (
                'name' => array($_FILES[$strTempName]['name']),
                'type' => array($_FILES[$strTempName]['type']),
                'tmp_name' => array($_FILES[$strTempName]['tmp_name']),
                'error' => array($_FILES[$strTempName]['error']),
                'size' => array($_FILES[$strTempName]['size']),
            );

            // Set the UUID as the filename
            if ($blnIsChunk) {
                $arrFile['name'][0] = \Input::post('qquuid') . '.chunk';
            }

            // Check if the file exists
            if (file_exists(TL_ROOT . '/' . $this->strTemporaryPath . '/' . $arrFile['name'][0])) {
                $arrFile['name'][0] = $this->getFileName($arrFile['name'][0], $this->strTemporaryPath);
            }

            $_FILES[$this->strName] = $arrFile;
            unset($_FILES[$strTempName]); // Unset the temporary file
        }

        $varInput = '';
        $extensions = null;
        $maxlength = null;

        // Add the "chunk" extension to upload types
        if ($blnIsChunk) {
            $extensions = $GLOBALS['TL_CONFIG']['uploadTypes'];
            $GLOBALS['TL_CONFIG']['uploadTypes'] .= ',chunk';
        }

        // Override the default maxlength value
        if (isset($this->arrConfiguration['maxlength']) || $blnIsChunk) {
            // Store the original value
            $maxlength = $GLOBALS['TL_CONFIG']['maxFileSize'];

            // If chunking is enabled and the current file is a chunk, use the chunk size setting
            if ($blnIsChunk) {
                $GLOBALS['TL_CONFIG']['maxFileSize'] = $this->arrConfiguration['chunkSize'];
            } else {
                $GLOBALS['TL_CONFIG']['maxFileSize'] = $this->arrConfiguration['maxlength'];
            }
        }

        try {
            $varInput = $objUploader->uploadTo($this->strTemporaryPath);

            if ($objUploader->hasError()) {
                foreach ($_SESSION['TL_ERROR'] as $strError) {
                    $this->addError($strError);
                }
            }

            \Message::reset();
        } catch (\Exception $e) {
            $this->addError($e->getMessage());
        }

        // Restore the default maxlength value
        if ($maxlength !== null) {
            $GLOBALS['TL_CONFIG']['maxFileSize'] = $maxlength;
        }

        // Restore the default extensions value
        if ($extensions !== null) {
            $GLOBALS['TL_CONFIG']['uploadTypes'] = $extensions;
        }

        if (!is_array($varInput) || empty($varInput)) {
            $this->addError($GLOBALS['TL_LANG']['MSC']['fineuploader_error']);
        }

        $varInput = $varInput[0];

        // Store the chunk in the session for further merge
        if ($blnIsChunk) {
            $_SESSION[$this->strName . '_FINEUPLOADER_CHUNKS'][\Input::post('qqfilename')][] = $varInput;

            // This is the last chunking request, merge the chunks and create the final file
            if (\Input::post('qqpartindex') == \Input::post('qqtotalparts') - 1) {
                $strFileName = \Input::post('qqfilename');

                // Get the new file name
                if (file_exists(TL_ROOT . '/' . $this->strTemporaryPath . '/' . $strFileName)) {
                    $strFileName = $this->getFileName($strFileName, $this->strTemporaryPath);
                }

                $objFile = new \File($this->strTemporaryPath . '/' . $strFileName);

                // Merge the chunks
                foreach ($_SESSION[$this->strName . '_FINEUPLOADER_CHUNKS'][\Input::post('qqfilename')] as $strChunk) {
                    $objFile->append(file_get_contents(TL_ROOT . '/' . $strChunk), '');

                    // Delete the file
                    \Files::getInstance()->delete($strChunk);
                }

                $objFile->close();
                $varInput = $objFile->path;

                // Reset the chunk flag
                $blnIsChunk = false;
            }
        }

        // Validate and move the file immediately
        if ($this->arrConfiguration['directUpload'] && !$blnIsChunk) {
            $varInput = $this->validatorSingle($varInput, $this->getDestinationFolder());
        }

        return $varInput;
    }

    /**
     * Return an array if the "multiple" attribute is set
     * @param mixed
     * @return mixed
     */
    protected function validator($varInput)
    {
        $varReturn = $this->blnIsMultiple ? array() : '';
        $strDestination = $this->getDestinationFolder();

        // Check if mandatory
        if ($varInput == '' && $this->mandatory) {
            $this->addError(sprintf($GLOBALS['TL_LANG']['ERR']['mandatory'], $this->strLabel));
        }
        // Single file
        elseif (strpos($varInput, ',') === false) {
            $varInput = $this->validatorSingle($varInput, $strDestination);
            $varReturn = $this->blnIsMultiple ? array($varInput) : $varInput;
        }
        // Multiple files
        else {
            $arrValues = array_filter(explode(',', $varInput));

            // Limit the number of uploads
            if ($this->arrConfiguration['uploaderLimit'] > 0) {
                $arrValues = array_slice($arrValues, 0, $this->arrConfiguration['uploaderLimit']);
            }

            foreach ($arrValues as $k => $v) {
                $arrValues[$k] = $this->validatorSingle($v, $strDestination);
            }

            $varReturn = $this->blnIsMultiple ? $arrValues : $arrValues[0];
        }

        return $varReturn;
    }

    /**
     * Get the destination folder
     *
     * @return mixed
     */
    protected function getDestinationFolder()
    {
        $destination = \Config::get('uploadPath');
        $folder = null;

        // Specify the target folder in the DCA (eval)
        if (isset($this->arrConfiguration['uploadFolder'])) {
            $folder = $this->arrConfiguration['uploadFolder'];
        }

        // Use the user's home directory
        if ($this->arrConfiguration['useHomeDir'] && FE_USER_LOGGED_IN) {
            $user = FrontendUser::getInstance();

            if ($user->assignDir && $user->homeDir) {
                $folder = $user->homeDir;
            }
        }

        if ($folder !== null && \Validator::isUuid($folder)) {
            $folderModel = \FilesModel::findByUuid($folder);

            if ($folderModel !== null) {
                $destination = $folderModel->path;
            }
        } else {
            $destination = $folder;
        }

        return $destination;
    }

    /**
     * Validate a single file.
     *
     * @param mixed
     * @param string
     * @return mixed
     */
    protected function validatorSingle($varFile, $strDestination)
    {
        // Move the temporary file
        if (!\Validator::isStringUuid($varFile) && is_file(TL_ROOT . '/' . $varFile)) {
            $varFile = $this->moveTemporaryFile($varFile, $strDestination);
        }

        // Convert uuid to binary format
        if (\Validator::isStringUuid($varFile)) {
            $varFile = \StringUtil::uuidToBin($varFile);
        }

        return $varFile;
    }


    /**
     * Move the temporary file to its destination
     * @param string
     * @param string
     * @return string
     */
    protected function moveTemporaryFile($strFile, $strDestination)
    {
        if (!is_file(TL_ROOT . '/' . $strFile)) {
            return '';
        }

        // Do not store the file
        if (!$this->arrConfiguration['storeFile']) {
            return $strFile;
        }

        // The file is not temporary
        if (stripos($strFile, $this->strTemporaryPath) === false) {
            return $strFile;
        }

        $strNew = $strDestination . '/' . basename($strFile);

        // Do not overwrite existing files
        if ($this->arrConfiguration['doNotOverwrite']) {
            $strNew = $strDestination . '/' . $this->getFileName(basename($strFile), $strDestination);
        }

        $blnRename = \Files::getInstance()->rename($strFile, $strNew);

        // Add the file to Dbafs
        if ($this->arrConfiguration['addToDbafs'] && $blnRename) {
            $objModel = \Dbafs::addResource($strNew);

            if ($objModel !== null) {
                $strNew = $objModel->uuid;
            }
        }

        return $strNew;
    }


    /**
     * Get the new file name if it already exists in the folder
     * @param string
     * @param string
     * @return string
     */
    protected function getFileName($strFile, $strFolder)
    {
        if (!file_exists(TL_ROOT . '/' . $strFolder . '/' . $strFile)) {
            return $strFile;
        }

        $offset = 1;
        $pathinfo = pathinfo($strFile);
        $name = $pathinfo['filename'];

        $arrAll = scan(TL_ROOT . '/' . $strFolder);
        $arrFiles = preg_grep('/^' . preg_quote($name, '/') . '.*\.' . preg_quote($pathinfo['extension'], '/') . '/', $arrAll);

        foreach ($arrFiles as $file) {
            if (preg_match('/__[0-9]+\.' . preg_quote($pathinfo['extension'], '/') . '$/', $file)) {
                $file = str_replace('.' . $pathinfo['extension'], '', $file);
                $intValue = intval(substr($file, (strrpos($file, '_') + 1)));

                $offset = max($offset, $intValue);
            }
        }

        return str_replace($name, $name . '__' . ++$offset, $strFile);
    }


    /**
     * Generate a file item and return it as HTML string
     * @param string
     * @return string
     */
    protected function generateFileItem($strPath)
    {
        if (!is_file(TL_ROOT . '/' . $strPath)) {
            return '';
        }

        $objFile = new \File($strPath, true);
        $strInfo = $strPath . ' <span class="tl_gray">(' . \System::getReadableSize($objFile->size) . ($objFile->isGdImage ? ', ' . $objFile->width . 'x' . $objFile->height . ' px' : '') . ')</span>';
        $allowedDownload = trimsplit(',', strtolower($GLOBALS['TL_CONFIG']['allowedDownload']));
        $strReturn = '';

        // Show files and folders
        if (!$this->blnIsGallery && !$this->blnIsDownloads) {
            if ($objFile->isGdImage) {
                $strReturn = \Image::getHtml(\Image::get($strPath, 80, 60, 'center_center'), '', 'class="gimage" title="' . specialchars($strInfo) . '"');
            } else {
                $strReturn = \Image::getHtml($objFile->icon) . ' ' . $strInfo;
            }
        }

        // Show a sortable list of files only
        else {
            if ($this->blnIsGallery) {
                // Only show images
                if ($objFile->isGdImage) {
                    $strReturn = \Image::getHtml(\Image::get($strPath, 80, 60, 'center_center'), '', 'class="gimage" title="' . specialchars($strInfo) . '"');
                }
            } else {
                // Only show allowed download types
                if (in_array($objFile->extension, $allowedDownload) && !preg_match('/^meta(_[a-z]{2})?\.txt$/', $objFile->basename)) {
                    $strReturn = \Image::getHtml($objFile->icon) . ' ' . $strPath;
                }
            }
        }

        return $strReturn;
    }
}
