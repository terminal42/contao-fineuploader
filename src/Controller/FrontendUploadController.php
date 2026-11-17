<?php

namespace Terminal42\FineUploaderBundle\Controller;

use Contao\CoreBundle\Controller\AbstractController;
use Contao\FormFieldModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Terminal42\FineUploaderBundle\RequestHandler\FrontendHandler;
use Terminal42\FineUploaderBundle\Widget\FrontendWidget;

/**
 * @Route("_fineuploader", defaults={"_scope": "frontend"})
 */
class FrontendUploadController extends AbstractController
{
    private FrontendHandler $frontendHandler;

    public function __construct(
        FrontendHandler $frontendHandler
    ) {
        $this->frontendHandler = $frontendHandler;
    }

    /**
     * @Route("/upload", name="fineuploader_frontend_upload", methods={"POST"})
     */
    public function uploadFile(Request $request)
    {
        $this->initializeContaoFramework();

        $widgetId = $request->request->get('widget_id');
        $formField = FormFieldModel::findById($widgetId);

        if (!$formField || $formField->type !== 'fineUploader' || !$formField->useUploadUrl) {
            return new Response();
        }

        $widget = new FrontendWidget($formField->row());

        return $this->frontendHandler->handleUploadRequest($request, $widget);
    }
}