<?php

declare(strict_types=1);

/*
 * FineUploader Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2020, terminal42 gmbh
 * @author     terminal42 <https://terminal42.ch>
 * @license    MIT
 */

namespace Terminal42\FineUploaderBundle\RequestHandler;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\CoreBundle\Monolog\ContaoContext;
use Monolog\Logger;
use Psr\Log\LogLevel;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Terminal42\FineUploaderBundle\Widget\FrontendWidget;

class FrontendHandler
{
    use HandlerTrait;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * FrontendHandler constructor.
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, Logger $logger)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->logger = $logger;
    }

    /**
     * Handle widget initialization request.
     *
     * @return Response|null
     */
    public function handleWidgetInitRequest(Request $request, FrontendWidget $widget)
    {
        if (!$request->isXmlHttpRequest()
            || $widget->name !== $request->request->get('name')
            || $request->attributes->get('fineuploader_ajax')
        ) {
            return null;
        }

        // Avoid circular reference
        $request->attributes->set('fineuploader_ajax', true);

        try {
            $response = $this->dispatchRequest($request, $widget);
        } catch (\Exception $e) {
            $this->logger->log(
                LogLevel::ERROR,
                $e->getMessage(),
                ['contao' => new ContaoContext(($e->getTrace())[1]['function'], TL_ERROR)]
            );

            $response = new Response('Bad Request', 400);
        }

        return $response;
    }

    /**
     * Handle upload request.
     *
     * @return JsonResponse
     *
     * @throw \RuntimeException
     */
    public function handleUploadRequest(Request $request, FrontendWidget $widget)
    {
        $this->validateRequest($request, ContaoCoreBundle::SCOPE_FRONTEND);

        return $this->getUploadResponse($this->eventDispatcher, $request, $widget);
    }

    /**
     * Handle reload request.
     *
     * @return Response
     *
     * @throw \RuntimeException
     */
    public function handleReloadRequest(Request $request, FrontendWidget $widget)
    {
        $this->validateRequest($request, ContaoCoreBundle::SCOPE_FRONTEND);

        // Set the value from request
        $widget->value = $this->parseValue($request->request->get('value'));

        return $this->getReloadResponse($this->eventDispatcher, $request, $widget);
    }

    /**
     * Dispatch the request.
     *
     * @return JsonResponse|null
     */
    private function dispatchRequest(Request $request, FrontendWidget $widget)
    {
        $response = null;

        // File upload
        if ('fineuploader_upload' === $request->request->get('action')) {
            $response = $this->handleUploadRequest($request, $widget);
        }

        // Widget reload
        if ('fineuploader_reload' === $request->request->get('action')) {
            $response = $this->handleReloadRequest($request, $widget);
        }

        return $response;
    }
}
