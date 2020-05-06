<?php

namespace Terminal42\FineUploaderBundle\EventListener;

use Contao\Config;
use Contao\File;
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
     * @param Uploader $uploader
     */
    public function __construct(Uploader $uploader)
    {
        $this->uploader = $uploader;
    }

    /**
     * On file upload
     *
     * @param FileUploadEvent $event
     */
    public function onFileUpload(FileUploadEvent $event)
    {
        $widget   = $event->getWidget();
        $filePath = $this->uploader->upload($event->getRequest(), $widget);

        if ($filePath === null) {
            $event->setResponse(new JsonResponse([
                'success' => false,
                'error' => $GLOBALS['TL_LANG']['ERR']['general'],
                'preventRetry' => true,
            ]));

            return;
        }

        // Validate the image dimensions for the frontend widget
        if ($widget instanceof FrontendWidget) {
            $this->validateImageDimensions($widget, $filePath);
        }

        if ($widget->hasErrors()) {
            $response = [
                'success'      => false,
                'error'        => $widget->getErrorAsString(),
                'preventRetry' => true,
            ];
        } else {
            $response = ['success' => true, 'file' => $filePath];
        }

        $event->setResponse(new JsonResponse($response));
    }

    /**
     * Validate the image dimensions
     *
     * @param FrontendWidget $widget
     * @param string         $filePath
     */
    private function validateImageDimensions(FrontendWidget $widget, $filePath)
    {
        $file = new File($filePath);

        if ($file->isImage) {
            $maxWidth  = Config::get('imageWidth');
            $maxHeight = Config::get('imageHeight');

            // Image exceeds maximum image width
            if ($file->width > $maxWidth) {
                $widget->addError(sprintf($GLOBALS['TL_LANG']['ERR']['filewidth'], '', $maxWidth));
            }

            // Image exceeds maximum image height
            if ($file->height > $maxHeight) {
                $widget->addError(sprintf($GLOBALS['TL_LANG']['ERR']['fileheight'], '', $maxHeight));
            }
        }
    }
}
