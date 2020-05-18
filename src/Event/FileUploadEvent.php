<?php

declare(strict_types=1);

/*
 * FineUploader Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2020, terminal42 gmbh
 * @author     terminal42 <https://terminal42.ch>
 * @license    MIT
 */

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
     */
    public function __construct(Request $request, JsonResponse $response, BaseWidget $widget)
    {
        $this->request = $request;
        $this->response = $response;
        $this->widget = $widget;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    public function setRequest(Request $request): void
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

    public function setResponse(JsonResponse $response): void
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

    public function setWidget(BaseWidget $widget): void
    {
        $this->widget = $widget;
    }
}
