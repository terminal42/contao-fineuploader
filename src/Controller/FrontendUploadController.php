<?php

namespace Terminal42\FineUploaderBundle\Controller;

use Contao\CoreBundle\Controller\AbstractController;
use Contao\FormFieldModel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Terminal42\FineUploaderBundle\RequestHandler\FrontendHandler;
use Terminal42\FineUploaderBundle\Widget\FrontendWidget;

#[Route('_fineuploader', defaults: ['_scope' => 'frontend'])]
class FrontendUploadController extends AbstractController
{
    public function __construct(
        private FrontendHandler $frontendHandler
    ) {
    }

    #[Route('/upload', name: 'fineuploader_frontend_upload', methods: ['POST'])]
    public function uploadFile(Request $request)
    {
        $this->initializeContaoFramework();

        $widgetId = $request->request->get('widget_id');
        $formField = FormFieldModel::findById($widgetId);

        if (!$formField || $formField->type !== 'fineUploader') {
            return new JsonResponse([
                'success' => false,
                'error' => $GLOBALS['TL_LANG']['ERR']['general'],
                'preventRetry' => true,
            ]);
        }

        $widget = new FrontendWidget($formField->row());

        return $this->frontendHandler->handleUploadRequest($request, $widget);
    }
}