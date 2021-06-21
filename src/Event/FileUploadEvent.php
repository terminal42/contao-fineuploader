<?php

declare(strict_types=1);

namespace Terminal42\FineUploaderBundle\Event;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;
use Terminal42\FineUploaderBundle\Widget\BaseWidget;

class FileUploadEvent extends Event
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var JsonResponse
     */
    private $response;

    /**
     * @var BaseWidget
     */
    private $widget;

    public function __construct(Request $request, JsonResponse $response, BaseWidget $widget)
    {
        $this->request = $request;
        $this->response = $response;
        $this->widget = $widget;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function setRequest(Request $request): void
    {
        $this->request = $request;
    }

    public function getResponse(): JsonResponse
    {
        return $this->response;
    }

    public function setResponse(JsonResponse $response): void
    {
        $this->response = $response;
    }

    public function getWidget(): BaseWidget
    {
        return $this->widget;
    }

    public function setWidget(BaseWidget $widget): void
    {
        $this->widget = $widget;
    }
}
