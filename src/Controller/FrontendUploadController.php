<?php

declare(strict_types=1);

namespace Terminal42\FineUploaderBundle\Controller;

use Contao\CoreBundle\Controller\AbstractController;
use Contao\FormFieldModel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\Routing\Attribute\Route;
use Terminal42\FineUploaderBundle\Widget\FrontendWidget;

#[Route('_fineuploader', defaults: ['_scope' => 'frontend'])]
class FrontendUploadController extends AbstractController
{
    public function __construct(private readonly UriSigner $uriSigner)
    {
    }

    #[Route('/upload', name: 'fineuploader_frontend_upload', methods: ['POST'])]
    public function uploadFile(Request $request): Response
    {
        $this->initializeContaoFramework();

        // The URL is signed by FrontendWidget::getUploadUrl(), so only widgets
        // that have actually been rendered can be targeted
        if (!$this->uriSigner->checkRequest($request)) {
            return $this->createErrorResponse(Response::HTTP_FORBIDDEN);
        }

        $formField = FormFieldModel::findById($request->query->getInt('widget_id'));

        if (null === $formField || $formField->invisible) {
            return $this->createErrorResponse(Response::HTTP_NOT_FOUND);
        }

        $class = $GLOBALS['TL_FFL'][$formField->type] ?? null;

        if (null === $class || !is_a($class, FrontendWidget::class, true)) {
            return $this->createErrorResponse(Response::HTTP_BAD_REQUEST);
        }

        // The FrontendWidget constructor handles the upload/reload request and
        // throws a ResponseException, so reaching the end means the request
        // did not match the widget (e.g. not an AJAX request or wrong name).
        new $class($formField->row());

        return $this->createErrorResponse(Response::HTTP_BAD_REQUEST);
    }

    private function createErrorResponse(int $status): JsonResponse
    {
        return new JsonResponse(
            [
                'success' => false,
                'error' => $GLOBALS['TL_LANG']['ERR']['general'] ?? 'An error occurred.',
                'preventRetry' => true,
            ],
            $status,
        );
    }
}
