<?php

declare(strict_types=1);

/*
 * FineUploader Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2020, terminal42 gmbh
 * @author     terminal42 <https://terminal42.ch>
 * @license    MIT
 */

namespace Terminal42\FineUploaderBundle\EventListener;

use Contao\Config;
use Contao\File;
use Contao\FilesModel;
use Contao\Validator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Terminal42\FineUploaderBundle\Event\FileUploadEvent;
use Terminal42\FineUploaderBundle\Uploader;
use Terminal42\FineUploaderBundle\Widget\FrontendWidget;

class FileUploadListener
{
    /**
     * @var Uploader
     */
    private $uploader;

    /**
     * FileUploadListener constructor.
     */
    public function __construct(Uploader $uploader)
    {
        $this->uploader = $uploader;
    }

    /**
     * On file upload.
     */
    public function onFileUpload(FileUploadEvent $event): void
    {
        $widget = $event->getWidget();
        $filePath = $this->uploader->upload($event->getRequest(), $widget);

        if (null === $filePath) {
            $event->setResponse(new JsonResponse([
                'success' => false,
                'error' => $GLOBALS['TL_LANG']['ERR']['general'],
                'preventRetry' => true,
            ]));

            return;
        }

        if (Validator::isUuid($filePath)) {
            $fileModel = FilesModel::findByUuid($filePath);

            if (null === $fileModel) {
                $event->setResponse(new JsonResponse([
                    'success' => false,
                    'error' => $GLOBALS['TL_LANG']['ERR']['general'],
                    'preventRetry' => true,
                ]));
    
                return;
            }

            $filePath = $fileModel->path;
        }

        // Validate the image dimensions for the frontend widget
        if ($widget instanceof FrontendWidget) {
            $this->validateImageDimensions($widget, $filePath);
        }

        if ($widget->hasErrors()) {
            $response = [
                'success' => false,
                'error' => $widget->getErrorAsString(),
                'preventRetry' => true,
            ];
        } else {
            $response = ['success' => true, 'file' => $filePath];
        }

        $event->setResponse(new JsonResponse($response));
    }

    /**
     * Validate the image dimensions.
     *
     * @param string $filePath
     */
    private function validateImageDimensions(FrontendWidget $widget, $filePath): void
    {
        $file = new File($filePath);

        if ($file->isImage) {
            $config = $widget->getUploaderConfig();

            $maxWidth = $config->getMaxImageWidth() ?: Config::get('imageWidth');
            $maxHeight = $config->getMaxImageHeight() ?: Config::get('imageHeight');

            // Image exceeds maximum image width
            if ($maxWidth > 0 && $file->width > $maxWidth) {
                $widget->addError(sprintf($GLOBALS['TL_LANG']['ERR']['filewidth'], '', $maxWidth));
            }

            // Image exceeds maximum image height
            if ($maxHeight > 0 && $file->height > $maxHeight) {
                $widget->addError(sprintf($GLOBALS['TL_LANG']['ERR']['fileheight'], '', $maxHeight));
            }
        }
    }
}
