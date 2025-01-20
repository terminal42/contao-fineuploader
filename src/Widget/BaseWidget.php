<?php

declare(strict_types=1);

namespace Terminal42\FineUploaderBundle\Widget;

use Contao\StringUtil;
use Contao\System;
use Contao\Template;
use Contao\UploadableWidgetInterface;
use Contao\Widget;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Terminal42\FineUploaderBundle\AssetsManager;
use Terminal42\FineUploaderBundle\ConfigGenerator;
use Terminal42\FineUploaderBundle\UploaderConfig;
use Terminal42\FineUploaderBundle\WidgetHelper;

abstract class BaseWidget extends Widget implements UploadableWidgetInterface
{
    /**
     * Submit user input.
     *
     * @var bool
     */
    protected $blnSubmitInput = true;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var UploaderConfig
     */
    protected $uploaderConfig;

    /**
     * Initialize the object.
     *
     * @param array $attributes
     */
    public function __construct($attributes = null)
    {
        parent::__construct($attributes);

        $this->container = System::getContainer();
        $request = $this->container->get('request_stack')->getCurrentRequest();

        // Set the default attributes
        $this->setDefaultAttributes();

        // Clean the chunks session when the widget is initialized in a non-ajax request
        if (!$request->isXmlHttpRequest()) {
            $this->container->get('terminal42_fineuploader.chunk_uploader')->clearSession($this);
        }
    }

    /**
     * Set the widget property.
     *
     * @param string $key
     *
     * @throws \InvalidArgumentException
     */
    public function __set($key, $value): void
    {
        switch ($key) {
            case 'value':
                $this->varValue = StringUtil::deserialize($value);

                // Handle the special case where value being set is a comma-separated list of files, e.g. in the MP Forms extension
                if (is_string($this->varValue) && str_contains($this->varValue, ',') && ($this->arrConfiguration['multiple'] ?? false)) {
                    $this->varValue = StringUtil::trimsplit(',', $value);
                }
                break;

            case 'imageSize':
            case 'uploaderConfig':
                if (!\is_array($value)) {
                    return;
                }

                $this->arrConfiguration[$key] = $value;
                break;

            case 'isGallery':
            case 'isDownloads':
                $this->arrConfiguration[$key] = $value ? true : false;
                break;

            case 'multiple':
                $this->arrConfiguration[$key] = $value ? true : false;

                // Set the uploader limit to 1 if it's not multiple
                if (!$value) {
                    $this->uploaderLimit = 1;
                }
                break;

                /** @noinspection PhpMissingBreakStatementInspection */
            case 'mandatory':
                if ($value) {
                    $this->arrAttributes['required'] = 'required';
                } else {
                    unset($this->arrAttributes['required']);
                }
            // DO NOT BREAK HERE

            // no break
            default:
                parent::__set($key, $value);
        }
    }

    /**
     * Parse the template file and return it as string.
     *
     * @param array $attributes An optional attributes array
     *
     * @return string The template markup
     */
    public function parse($attributes = null)
    {
        if (!$this->jsConfig) {
            $this->jsConfig = $this->getConfigGenerator()->generateJavaScriptConfig($this->getUploaderConfig());
        }

        return parent::parse($attributes);
    }

    /**
     * Get the uploader config.
     *
     * @return UploaderConfig
     */
    public function getUploaderConfig()
    {
        if (null === $this->uploaderConfig) {
            $this->uploaderConfig = $this->getConfigGenerator()->generateFromWidgetAttributes($this->arrConfiguration);
        }

        return $this->uploaderConfig;
    }

    /**
     * Parse the values and return them as HTML string.
     *
     * @return string
     */
    public function parseValues()
    {
        return $this->getWidgetHelper()->generateValuesTemplate($this)->parse();
    }

    /**
     * Get the widget configuration.
     *
     * @return array
     */
    public function getConfiguration()
    {
        return $this->arrConfiguration;
    }

    /**
     * Required by \Contao\Widget class. Use the parse() method instead.
     *
     * @throws \BadMethodCallException
     */
    public function generate(): void
    {
        throw new \BadMethodCallException('Use the parse() method instead');
    }

    /**
     * Get the item template.
     *
     * @return Template
     */
    abstract public function getItemTemplate();

    /**
     * Get the values template.
     *
     * @return Template
     */
    abstract public function getValuesTemplate();

    /**
     * Set the default attributes.
     */
    protected function setDefaultAttributes(): void
    {
        $this->decodeEntities = true;

        // Set the default image size
        if (!$this->imageSize) {
            $this->imageSize = [80, 60, 'center_center'];
        }

        // Set the uploader limit to 1 if it's not multiple
        if (!$this->multiple) {
            $this->uploaderLimit = 1;
        }

        // Set the default upload button title
        if (!$this->uploadButtonTitle) {
            $this->uploadButtonTitle = $GLOBALS['TL_LANG']['MSC']['fineuploader.upload'];
        }
    }

    /**
     * Return an array if the "multiple" attribute is set.
     *
     * @param string $input
     *
     * @return array|string
     */
    protected function validator($input)
    {
        return $this->container->get('terminal42_fineuploader.validator')->validateInput($this, $input);
    }

    /**
     * Get the config generator.
     *
     * @return ConfigGenerator
     */
    protected function getConfigGenerator()
    {
        return $this->container->get('terminal42_fineuploader.config_generator');
    }

    /**
     * Get the widget helper.
     *
     * @return WidgetHelper
     */
    protected function getWidgetHelper()
    {
        return $this->container->get('terminal42_fineuploader.widget_helper');
    }

    /**
     * Get the assets manager.
     *
     * @return AssetsManager
     */
    protected function getAssetsManager()
    {
        return $this->container->get('terminal42_fineuploader.assets_manager');
    }
}
