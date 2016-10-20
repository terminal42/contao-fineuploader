<?php

namespace Terminal42\FineUploaderBundle\EventListener;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\File;
use Symfony\Component\HttpFoundation\JsonResponse;
use Terminal42\FineUploaderBundle\Event\FileUploadEvent;
use Terminal42\FineUploaderBundle\Uploader;
use Terminal42\FineUploaderBundle\Widget\FrontendWidget;

class FileUploadListener
{
    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

    /**
     * @var Uploader
     */
    private $uploader;

    /**
     * FileUploadListener constructor.
     *
     * @param ContaoFrameworkInterface $framework
     * @param Uploader                 $uploader
     */
    public function __construct(ContaoFrameworkInterface $framework, Uploader $uploader)
    {
        $this->framework = $framework;
        $this->uploader  = $uploader;
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

        // @todo - $filePath can return null

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
            /** @var \Contao\Config $config */
            $config    = $this->framework->createInstance('\Contao\Config');
            $maxWidth  = $config->get('imageWidth');
            $maxHeight = $config->get('imageHeight');

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
