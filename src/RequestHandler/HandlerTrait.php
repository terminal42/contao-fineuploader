<?php

declare(strict_types=1);

namespace Terminal42\FineUploaderBundle\RequestHandler;

use Contao\Input;
use Contao\StringUtil;
use Contao\Validator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Terminal42\FineUploaderBundle\Event\FileUploadEvent;
use Terminal42\FineUploaderBundle\Event\WidgetReloadEvent;
use Terminal42\FineUploaderBundle\UploaderEvents;
use Terminal42\FineUploaderBundle\Widget\BaseWidget;

trait HandlerTrait
{
    /**
     * Get the file upload response.
     *
     * @return JsonResponse
     */
    protected function getUploadResponse(EventDispatcherInterface $eventDispatcher, Request $request, BaseWidget $widget)
    {
        $event = new FileUploadEvent($request, new JsonResponse(), $widget);
        $eventDispatcher->dispatch($event, UploaderEvents::FILE_UPLOAD);

        return $event->getResponse();
    }

    /**
     * Get the widget reload response.
     *
     * @return Response
     */
    protected function getReloadResponse(EventDispatcherInterface $eventDispatcher, Request $request, BaseWidget $widget)
    {
        $event = new WidgetReloadEvent($request, new Response(), $widget);
        $eventDispatcher->dispatch($event, UploaderEvents::WIDGET_RELOAD);

        return $event->getResponse();
    }

    /**
     * Parse the value by converting UUIDs to binary data.
     *
     * @param string $value
     *
     * @return string
     */
    protected function parseValue($value, string $projectDir)
    {
        $value = StringUtil::trimsplit(',', Input::decodeEntities($value));

        foreach ($value as $k => $v) {
            if (Validator::isUuid($v) && !is_file(Path::join($projectDir, $v))) {
                $value[$k] = StringUtil::uuidToBin($v);
            }
        }

        return serialize($value);
    }
}
