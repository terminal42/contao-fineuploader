<?php

declare(strict_types=1);

namespace Terminal42\FineUploaderBundle\Widget;

use Contao\CoreBundle\Exception\ResponseException;
use Contao\FrontendTemplate;

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
