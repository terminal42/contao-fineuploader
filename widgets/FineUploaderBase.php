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
     * Files mapper
     * @var array
     */
    protected $arrFilesMapper = array();


    /**
     * Add specific attributes
     * @param string
     * @param mixed
     */
    public function __set($strKey, $varValue)
    {
        switch ($strKey)
        {
            case 'maxlength':
                // Do not add as attribute (see #3094)
                $this->arrConfiguration['maxlength'] = $varValue;
                break;

            case 'mSize':
                $this->arrConfiguration['uploaderLimit'] = $varValue;
                break;

            case 'mandatory':
                if ($varValue)
                {
                    $this->arrAttributes['required'] = 'required';
                }
                else
                {
                    unset($this->arrAttributes['required']);
                }
                parent::__set($strKey, $varValue);
                break;

            default:
                parent::__set($strKey, $varValue);
                break;
        }
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

        // Convert the $_FILES array to Contao format
        if (!empty($_FILES[$strTempName]))
        {
            $arrFile = array
            (
                'name' => array($_FILES[$strTempName]['name']),
                'type' => array($_FILES[$strTempName]['type']),
                'tmp_name' => array($_FILES[$strTempName]['tmp_name']),
                'error' => array($_FILES[$strTempName]['error']),
                'size' => array($_FILES[$strTempName]['size']),
            );

            // Check if the file exists
            if (file_exists(TL_ROOT . '/' . $this->strTemporaryPath . '/' . $arrFile['name'][0]))
            {
                $arrFile['name'][0] = $this->getFileName($arrFile['name'][0], $this->strTemporaryPath);
            }

            $_FILES[$this->strName] = $arrFile;
            unset($_FILES[$strTempName]); // Unset the temporary file
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
            $varInput = $objUploader->uploadTo($this->strTemporaryPath);

            if ($objUploader->hasError()) {
                foreach ($_SESSION['TL_ERROR'] as $strError) {
                    $this->addError($strError);
                }
            }

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
            $this->addError($GLOBALS['TL_LANG']['MSC']['fineuploader_error']);
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
        $varReturn = $this->blnIsMultiple ? array() : '';
        $strDestination = $GLOBALS['TL_CONFIG']['uploadPath'];

        // Specify the target folder in the DCA (eval)
        if (isset($this->arrConfiguration['uploadFolder']))
        {
            $varFolder = $this->arrConfiguration['uploadFolder'];

            // Use the user's home directory
            if ($this->arrConfiguration['useHomeDir'] && FE_USER_LOGGED_IN)
            {
                $this->import('FrontendUser', 'User');

                if ($this->User->assignDir && $this->User->homeDir)
                {
                    $varFolder = $this->User->homeDir;
                }
            }

            if (\Validator::isBinaryUuid($varFolder) || \Validator::isStringUuid($varFolder))
            {
                $objFolder = \FilesModel::findByUuid($varFolder);

                if ($objFolder !== null)
                {
                    $strDestination = $objFolder->path;
                }
            }
            else
            {
                $strDestination = $varFolder;
            }
        }

        // Check the mandatoriness
        if ($varInput == '' && $this->mandatory)
        {
            $this->addError(sprintf($GLOBALS['TL_LANG']['ERR']['mandatory'], $this->strLabel));
        }
        // Single file
        elseif (strpos($varInput, ',') === false)
        {
            $old = $varInput;

            // Move the temporary file
            if (!\Validator::isStringUuid($varInput) && is_file(TL_ROOT . '/' . $varInput))
            {
                $varInput = $this->moveTemporaryFile($varInput, $strDestination);
            }

            // Convert uuid to binary format
            if (\Validator::isStringUuid($varInput))
            {
                $varInput = \String::uuidToBin($varInput);
            }

            $varReturn = $this->blnIsMultiple ? array($varInput) : $varInput;

            // Store in the mapper
            $this->arrFilesMapper[$old] = $varInput;
        }
        // Multiple files
        else
        {
            $arrValues = array_filter(explode(',', $varInput));

            // Limit the number of uploads
            if ($this->arrConfiguration['uploaderLimit'] > 0)
            {
                $arrValues = array_slice($arrValues, 0, $this->arrConfiguration['uploaderLimit']);
            }

            foreach ($arrValues as $k => $v)
            {
                $old = $v;

                // Move the temporary file
                if (!\Validator::isStringUuid($v) && is_file(TL_ROOT . '/' . $v))
                {
                    $v = $this->moveTemporaryFile($v, $strDestination);
                }

                // Convert uuid to binary format
                if (\Validator::isStringUuid($v))
                {
                    $v = \String::uuidToBin($v);
                }

                $arrValues[$k] = $v;

                // Store in the mapper
                $this->arrFilesMapper[$old] = $v;
            }

            $varReturn = $this->blnIsMultiple ? $arrValues : $arrValues[0];
        }

        return $varReturn;
    }


    /**
     * Move the temporary file to its destination
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

        // Do not store the file
        if (!$this->arrConfiguration['storeFile'])
        {
            return $strFile;
        }

        // The file is not temporary
        if (stripos($strFile, $this->strTemporaryPath) === false)
        {
            return $strFile;
        }

        $strNew = $strDestination . '/' . basename($strFile);

        // Do not overwrite existing files
        if ($this->arrConfiguration['doNotOverwrite'])
        {
            $strNew = $strDestination . '/' . $this->getFileName(basename($strFile), $strDestination);
        }

        $blnRename = \Files::getInstance()->rename($strFile, $strNew);

        // Add the file to Dbafs
        if ($this->arrConfiguration['addToDbafs'] && $blnRename)
        {
            $objModel = \Dbafs::addResource($strNew);

            if ($objModel !== null)
            {
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
