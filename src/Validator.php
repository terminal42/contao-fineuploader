<?php

declare(strict_types=1);

namespace Terminal42\FineUploaderBundle;

use Contao\StringUtil;
use Terminal42\FineUploaderBundle\Widget\BaseWidget;

class Validator
{
    /**
     * @var Uploader
     */
    private $uploader;

    /**
     * Validator constructor.
     */
    public function __construct(Uploader $uploader)
    {
        $this->uploader = $uploader;
    }

    /**
     * Validate the widget input.
     *
     * @param string $input
     *
     * @return array|string
     */
    public function validateInput(BaseWidget $widget, $input)
    {
        // No input
        if (!$input) {
            return $this->validateEmptyValue($widget);
        }

        // Validate multiple files if input is an array (could be the case e.g. when terminal42/contao-mp_forms extension is used)
        if (\is_array($input)) {
            return $this->validateMultipleFiles($widget, array_filter($input));
        }

        // Single file
        if (false === strpos($input, ',')) {
            return $this->validateSingleFile($widget, $input);
        }

        return $this->validateMultipleFiles($widget, array_filter(StringUtil::trimsplit(',', $input)));
    }

    /**
     * Validate an empty value.
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
     * Validate the single file.
     *
     * @param string $input
     *
     * @return string
     */
    private function validateSingleFile(BaseWidget $widget, $input)
    {
        try {
            return $this->uploader->storeFile($widget->getUploaderConfig(), $input);
        } catch (\Exception $e) {
            $widget->addError($GLOBALS['TL_LANG']['ERR']['emptyUpload']);
        }

        return $input;
    }

    /**
     * Validate the multiple files.
     *
     * @return array
     */
    private function validateMultipleFiles(BaseWidget $widget, array $inputs)
    {
        $config = $widget->getUploaderConfig();

        // Limit the number of uploads
        if ($config->getLimit() > 0) {
            $inputs = \array_slice($inputs, 0, $config->getLimit());
        }

        // Store the files
        foreach ($inputs as $k => $v) {
            try {
                $inputs[$k] = $this->uploader->storeFile($config, $v);
            } catch (\Exception $e) {
                $widget->addError($GLOBALS['TL_LANG']['ERR']['emptyUpload']);
            }
        }

        return $inputs;
    }
}
