<?php

declare(strict_types=1);

/*
 * FineUploader Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2020, terminal42 gmbh
 * @author     terminal42 <https://terminal42.ch>
 * @license    MIT
 */

namespace Terminal42\FineUploaderBundle\Widget;

use Contao\BackendTemplate;
use Contao\Config;

class BackendWidget extends BaseWidget
{
    /**
     * Template.
     *
     * @var string
     */
    protected $strTemplate = 'fineuploader_backend';

    /**
     * Initialize the widget.
     *
     * @param array $attributes
     */
    public function __construct($attributes = null)
    {
        parent::__construct($attributes);

        $manager = $this->getAssetsManager();

        // Include the assets in case they have not been included yet
        $manager->includeAssets(array_merge($manager->getBasicAssets(), $manager->getBackendAssets()));
    }

    /**
     * Generate the widget and return it as string.
     *
     * @param array $attributes
     *
     * @return string
     */
    public function parse($attributes = null)
    {
        // Load the fonts for the drag hint (see #4838)
        Config::set('loadGoogleFonts', true);

        return parent::parse($attributes);
    }

    /**
     * Get the item template.
     *
     * @return BackendTemplate
     */
    public function getItemTemplate()
    {
        return new BackendTemplate($this->itemTemplate ?: 'fineuploader_item_backend');
    }

    /**
     * Get the values template.
     *
     * @return BackendTemplate
     */
    public function getValuesTemplate()
    {
        return new BackendTemplate($this->itemTemplate ?: 'fineuploader_values_backend');
    }
}
