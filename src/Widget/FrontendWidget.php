<?php

declare(strict_types=1);

namespace Terminal42\FineUploaderBundle\Widget;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\CoreBundle\Exception\ResponseException;
use Contao\FrontendTemplate;
use Contao\System;

class FrontendWidget extends BaseWidget
{
    /**
     * Template.
     *
     * @var string
     */
    protected $strTemplate = 'fineuploader_frontend';

    /**
     * The CSS class prefix.
     *
     * @var string
     */
    protected $strPrefix = 'widget widget-fineuploader';

    /**
     * Initialize the widget.
     *
     * @param array $attributes
     *
     * @throws ResponseException
     */
    public function __construct($attributes = null)
    {
        parent::__construct($attributes);

        $response = $this->container->get('terminal42_fineuploader.request.frontend_handler')->handleWidgetInitRequest(
            $this->container->get('request_stack')->getCurrentRequest(),
            $this
        );

        if (null !== $response) {
            throw new ResponseException($response);
        }
    }

    /**
     * Get the item template.
     *
     * @return FrontendTemplate
     */
    public function getItemTemplate()
    {
        return new FrontendTemplate($this->itemTemplate ?: 'fineuploader_item_frontend');
    }

    /**
     * Get the values template.
     *
     * @return FrontendTemplate
     */
    public function getValuesTemplate()
    {
        return new FrontendTemplate($this->valuesTemplate ?: 'fineuploader_values_frontend');
    }

    /**
     * Store the file information in the session to reproduce Contao 4.13 uploader behavior.
     */
    protected function validator($input)
    {
        $return = parent::validator($input);

        if (version_compare(ContaoCoreBundle::getVersion(), '5@dev', '<')) {
            $this->getWidgetHelper()->addFilesToSession($this->strName, array_filter((array) $return), $this->storeFile);
        }

        return $return;
    }

    /**
     * Include the assets.
     *
     * @param bool $frontendAssets
     */
    protected function includeAssets($frontendAssets = true): void
    {
        $manager = $this->getAssetsManager();
        $assets = $manager->getBasicAssets();

        if ($frontendAssets) {
            $assets = array_merge($assets, $manager->getFrontendAssets($this->sortable && $this->multiple));
        }

        $manager->includeAssets($assets);
    }
}
