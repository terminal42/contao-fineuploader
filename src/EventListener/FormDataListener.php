<?php

declare(strict_types=1);

namespace Terminal42\FineUploaderBundle\EventListener;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\Form;
use Contao\FormFieldModel;

class FormDataListener
{
    /** 
     * @Hook("prepareFormData")
     */
    public function onPrepareFormData(array &$submittedData, array $labels, array $fields, Form $form, array &$files = null): void
    {
        if (version_compare(ContaoCoreBundle::getVersion(), '5@dev', '<')) {
            return;
        }

        /** @var FormFieldModel $model */
        foreach ($fields as $name => $model) {
            if ('fineUploader' !== $model->type) {
                continue;
            }

            if (isset($submittedData[$name])) {
                $submittedData[$name] = array_map(static function(array $file): string {
                    return $file['tmp_name'];
                }, $submittedData[$name]);
            }

            if (isset($files[$name])) {
                foreach ($files[$name] as $key => $file) {
                    $files[$key] = $file;
                }

                unset($files[$name]);
            }
        }
    }
}
