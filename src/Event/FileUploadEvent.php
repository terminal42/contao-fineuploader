<?php

namespace Terminal42\FineUploaderBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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

    /**
     * FileUploadEvent constructor.
     *
     * @param Request      $request
     * @param JsonResponse $response
     * @param BaseWidget   $widget
     */
    public function __construct(Request $request, JsonResponse $response, BaseWidget $widget)
    {
        $this->request  = $request;
        $this->response = $response;
        $this->widget   = $widget;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param Request $request
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @return JsonResponse
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param JsonResponse $response
     */
    public function setResponse(JsonResponse $response)
    {
        $this->response = $response;
    }

    /**
     * @return BaseWidget
     */
    public function getWidget()
    {
        return $this->widget;
    }

    /**
     * @param BaseWidget $widget
     */
    public function setWidget(BaseWidget $widget)
    {
        $this->widget = $widget;
    }
}