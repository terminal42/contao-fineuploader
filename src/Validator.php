<?php

namespace Terminal42\FineUploaderBundle;

use Terminal42\FineUploaderBundle\Widget\BaseWidget;

class Validator
{
    /**
     * @var Uploader
     */
    private $uploader;

    /**
     * Validator constructor.
     *
     * @param Uploader $uploader
     */
    public function __construct(Uploader $uploader)
    {
        $this->uploader = $uploader;
    }

    /**
     * Validate the widget input
     *
     * @param BaseWidget $widget
     * @param string     $input
     *
     * @return array|string
     */
    public function validateInput(BaseWidget $widget, $input)
    {
        // No input
        if (!$input) {
            return $this->validateEmptyValue($widget);
        }

        // Single file
        if (strpos($input, ',') === false) {
            return $this->validateSingleFile($widget, $input);
        }

        return $this->validateMultipleFiles($widget, array_filter(trimsplit(',', $input)));
    }

    /**
     * Validate an empty value
     *
     * @param BaseWidget $widget
     *
     * @return array|string
     */
    private function validateEmptyValue(BaseWidget $widget)
    {
        // Add an error if the field is mandatory
        if ($widget->mandatory) {
            if ($widget->label) {
                $widget->addError(sprintf($GLOBALS['TL_LANG']['ERR']['mandatory'], $widget->label));
            } else {
                $widget->addError($GLOBALS['TL_LANG']['ERR']['mdtryNoLabel']);
            }
        }

        return $widget->multiple ? [] : '';
    }

    /**
     * Validate the single file
     *
     * @param BaseWidget $widget
     * @param string     $input
     *
     * @return string
     */
    private function validateSingleFile(BaseWidget $widget, $input)
    {
        return $this->uploader->storeFile($widget->getUploaderConfig(), $input);
    }

    /**
     * Validate the multiple files
     *
     * @param BaseWidget $widget
     * @param array      $inputs
     *
     * @return array
     */
    private function validateMultipleFiles(BaseWidget $widget, array $inputs)
    {
        $config = $widget->getUploaderConfig();

        // Limit the number of uploads
        if ($config->getLimit() > 0) {
            $inputs = array_slice($inputs, 0, $config->getLimit());
        }

        // Store the files
        foreach ($inputs as $k => $v) {
            $inputs[$k] = $this->uploader->storeFile($config, $v);
        }

        return $inputs;
    }
}
