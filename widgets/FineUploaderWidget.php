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
 * Class FineUploaderWidget
 *
 * Provide methods to handle input field "fine uploader".
 */
class FineUploaderWidget extends FineUploaderBase
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
        parent::__construct($arrAttributes);
        $this->strOrderField = $this->arrConfiguration['orderField'];
        $this->blnIsMultiple = $this->arrConfiguration['multiple'];

        // Prepare the order field
        if ($this->strOrderField != '')
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
        $GLOBALS['TL_JAVASCRIPT']['fineuploader'] = 'system/modules/fineuploader/assets/fineuploader/fineuploader-5.0.2.min.js';
        $GLOBALS['TL_JAVASCRIPT']['fineuploader_handler'] = 'system/modules/fineuploader/assets/handler.min.js';
        $GLOBALS['TL_CSS']['fineuploader_handler'] = 'system/modules/fineuploader/assets/handler.min.css';
    }


    /**
     * Return an array if the "multiple" attribute is set
     * @param mixed
     * @return mixed
     */
    protected function validator($varInput)
    {
        $varReturn = parent::validator($varInput);

        // Store the order value
        if ($this->strOrderField != '')
        {
            $arrNew = explode(',', \Input::post($this->strOrderName));

            // Map the files
            foreach ($arrNew as $k => $v)
            {
                if (isset($this->arrFilesMapper[$v]))
                {
                    $arrNew[$k] = $this->arrFilesMapper[$v];
                }
            }

            // Only proceed if the value has changed
            if ($arrNew !== $this->{$this->strOrderField})
            {
                $objVersions = new \Versions($this->strTable, \Input::get('id'));
                $objVersions->initialize();

                \Database::getInstance()->prepare("UPDATE {$this->strTable} SET tstamp=?, {$this->strOrderField}=? WHERE id=?")
                                        ->execute(time(), serialize($arrNew), \Input::get('id'));

                $objVersions->create(); // see #6285
            }
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
        $blnHasOrder = ($this->strOrderField != '' && is_array($this->{$this->strOrderField}) && TL_MODE == 'BE');

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

        $return = '<input type="hidden" name="'.$this->strName.'_fineuploader" id="ctrl_'.$this->strId.'_fineuploader" value="">
  <input type="hidden" name="'.$this->strName.'" id="ctrl_'.$this->strId.'" value="'.$strSet.'">' . ($blnHasOrder ? '
  <input type="hidden" name="'.$this->strOrderName.'" id="ctrl_'.$this->strOrderId.'" value="'.$strOrder.'">' : '') . '
  <div class="selector_container">' . (($blnHasOrder && count($arrValues)) ? '
    <p class="sort_hint">' . $GLOBALS['TL_LANG']['MSC']['dragItemsHint'] . '</p>' : '') . '
    <ul id="sort_'.$this->strId.'" class="'.trim(($blnHasOrder ? 'sortable ' : '').($this->blnIsGallery ? 'sgallery' : '')).'">';

        foreach ($arrValues as $k=>$v)
        {
            $return .= '<li data-id="'.(in_array($k, $arrTemp) ? $k : \String::binToUuid($k)).'">
<a href="#" class="delete" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['delete']).'" onclick="ContaoFineUploader.deleteItem(this, \''.$this->strId.'\');return false;"></a>
'.$v.'
</li>';
        }

        $return .= '</ul>' . ($blnHasOrder ? '
    <script>ContaoFineUploader.makeSortable("sort_'.$this->strId.'", "ctrl_'.$this->strOrderId.'")</script>' : '') . '
  </div>';

        if (!\Environment::get('isAjaxRequest'))
        {
            $objTemplate = new \BackendTemplate($this->arrConfiguration['uploaderTemplate'] ? $this->arrConfiguration['uploaderTemplate'] : 'fineuploader_default');

            $return = '<div><div>' . $return . '</div>';
            $return .= '<div id="'.$this->strId.'_fineuploader" class="upload_container"></div>' . $objTemplate->parse() . '
  <script>
    window.addEvent("domready", function() {
      ContaoFineUploader.init($("'.$this->strId.'_fineuploader"), {
          field: "'.$this->strId.'",
          request_token: "'.REQUEST_TOKEN.'",
          backend: true,
          extensions: '.json_encode(trimsplit(',', $this->arrConfiguration['extensions'])).',
          limit: '.($this->arrConfiguration['uploaderLimit'] ? $this->arrConfiguration['uploaderLimit'] : 0).',
          sizeLimit: '.($this->arrConfiguration['maxlength'] ? $this->arrConfiguration['maxlength'] : 0).'
        },
        {'.($this->arrConfiguration['uploaderConfig'] ? $this->arrConfiguration['uploaderConfig'] : "").'});
    });
  </script>
  </div>';
        }

        return $return;
    }
}
