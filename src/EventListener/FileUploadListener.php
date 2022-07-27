<?php

declare(strict_types=1);

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

            $minWidth = $config->getMinImageWidth() ?: 0;
            $minHeight = $config->getMinImageHeight() ?: 0;
            $maxWidth = $config->getMaxImageWidth() ?: Config::get('imageWidth');
            $maxHeight = $config->getMaxImageHeight() ?: Config::get('imageHeight');

            // Image deceeds minimum image width
            if ($minWidth > 0 && $file->width < $minWidth) {
                $widget->addError(sprintf($GLOBALS['TL_LANG']['ERR']['fileminwidth'], '', $minWidth));
            }

            // Image deceeds minimum image height
            if ($minHeight > 0 && $file->height < $minHeight) {
                $widget->addError(sprintf($GLOBALS['TL_LANG']['ERR']['fileminheight'], '', $minHeight));
            }

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
