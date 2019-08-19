<?php

namespace Terminal42\FineUploaderBundle\Widget;

use Contao\CoreBundle\Exception\ResponseException;
use Contao\FrontendTemplate;

class FrontendWidget extends BaseWidget
{
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
     * Initialize the widget
     *
     * @param array $attributes
     *
     * @throws ResponseException
     */
    public function __construct($attributes = null)
    {
        parent::__construct($attributes);

        set_time_limit(5);//@todo @debug

        $response = $this->container->get('terminal42_fineuploader.request.frontend_handler')->handleWidgetInitRequest(
            $this->container->get('request_stack')->getCurrentRequest(),
            $this
        );

        if ($response !== null) {
            throw new ResponseException($response);
        }
    }

    /**
     * Store the file information in the session
     *
     * @param mixed $input
     *
     * @return mixed
     */
    protected function validator($input)
    {
        $return = parent::validator($input);

        // Add files to the session
        $this->getWidgetHelper()->addFilesToSession($this->strName, array_filter((array)$return));

        return $return;
    }

    /**
     * Include the assets
     *
     * @param bool $frontendAssets
     */
    protected function includeAssets($frontendAssets = true)
    {
        $manager = $this->getAssetsManager();
        $assets  = $manager->getBasicAssets();

        if ($frontendAssets) {
            $assets = array_merge($assets, $manager->getFrontendAssets($this->sortable && $this->multiple));
        }

        $manager->includeAssets($assets);
    }

    /**
     * Get the item template
     *
     * @return FrontendTemplate
     */
    public function getItemTemplate()
    {
        return new FrontendTemplate($this->itemTemplate ?: 'fineuploader_item_frontend');
    }

    /**
     * Get the values template
     *
     * @return FrontendTemplate
     */
    public function getValuesTemplate()
    {
        return new FrontendTemplate($this->itemTemplate ?: 'fineuploader_values_frontend');
    }
}
