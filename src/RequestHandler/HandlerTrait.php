<?php

namespace Terminal42\FineUploaderBundle\RequestHandler;

use Contao\Input;
use Contao\StringUtil;
use Contao\Validator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Terminal42\FineUploaderBundle\Event\WidgetReloadEvent;
use Terminal42\FineUploaderBundle\UploaderEvents;
use Terminal42\FineUploaderBundle\Event\FileUploadEvent;
use Terminal42\FineUploaderBundle\Widget\BaseWidget;

trait HandlerTrait
{
    /**
     * Get the file upload response
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param Request                  $request
     * @param BaseWidget               $widget
     *
     * @return JsonResponse
     */
    protected function getUploadResponse(
        EventDispatcherInterface $eventDispatcher,
        Request $request,
        BaseWidget $widget
    ) {
        $event = new FileUploadEvent($request, new JsonResponse(), $widget);
        $eventDispatcher->dispatch(UploaderEvents::FILE_UPLOAD, $event);

        return $event->getResponse();
    }

    /**
     * Get the widget reload response
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param Request                  $request
     * @param BaseWidget               $widget
     *
     * @return Response
     */
    protected function getReloadResponse(
        EventDispatcherInterface $eventDispatcher,
        Request $request,
        BaseWidget $widget
    ) {
        $event = new WidgetReloadEvent($request, new Response(), $widget);
        $eventDispatcher->dispatch(UploaderEvents::WIDGET_RELOAD, $event);

        return $event->getResponse();
    }

    /**
     * Validate the request
     *
     * @param Request $request
     * @param string  $scope
     *
     * @throws \RuntimeException
     */
    protected function validateRequest(Request $request, $scope)
    {
        if ($request->attributes->get('_scope') !== $scope) {
            throw new \RuntimeException(sprintf('This method can be executed only in the %s scope', $scope));
        }
    }

    /**
     * Parse the value by converting UUIDs to binary data
     *
     * @param string                   $value
     *
     * @return string
     */
    protected function parseValue($value)
    {
        $value = StringUtil::trimsplit(',', Input::decodeEntities($value));

        foreach ($value as $k => $v) {
            if (Validator::isUuid($v) && !is_file(TL_ROOT.'/'.$v)) {
                $value[$k] = StringUtil::uuidToBin($v);
            }
        }

        return serialize($value);
    }
}
