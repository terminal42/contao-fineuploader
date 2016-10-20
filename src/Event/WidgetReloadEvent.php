<?php

namespace Terminal42\FineUploaderBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Terminal42\FineUploaderBundle\Widget\BaseWidget;

class WidgetReloadEvent extends Event
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var Response
     */
    private $response;

    /**
     * @var BaseWidget
     */
    private $widget;

    /**
     * FileUploadEvent constructor.
     *
     * @param Request    $request
     * @param Response   $response
     * @param BaseWidget $widget
     */
    public function __construct(Request $request, Response $response, BaseWidget $widget)
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
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param Response $response
     */
    public function setResponse(Response $response)
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
