<?php

namespace Terminal42\FineUploaderBundle\EventListener;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\CoreBundle\Exception\ResponseException;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\DataContainer;
use Monolog\Logger;
use Psr\Log\LogLevel;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Terminal42\FineUploaderBundle\AssetsManager;
use Terminal42\FineUploaderBundle\RequestHandler\BackendHandler;

class BackendListener
{
    /**
     * @var AssetsManager
     */
    private $assetsManager;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var BackendHandler
     */
    private $requestHandler;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * BackendListener constructor.
     *
     * @param AssetsManager  $assetsManager
     * @param Logger         $logger
     * @param BackendHandler $requestHandler
     * @param RequestStack   $requestStack
     */
    public function __construct(
        AssetsManager $assetsManager,
        Logger $logger,
        BackendHandler $requestHandler,
        RequestStack $requestStack
    ) {
        $this->assetsManager  = $assetsManager;
        $this->logger         = $logger;
        $this->requestHandler = $requestHandler;
        $this->requestStack   = $requestStack;
    }

    /**
     * Load the widget assets if they are needed. Load them here so the widget in subpalette can work as well.
     *
     * @param string $table
     */
    public function onLoadDataContainer($table)
    {
        $request = $this->requestStack->getCurrentRequest();

        // Return if the scope is not backend or the DCA has no fields
        if ($request === null
            || $request->attributes->get('_scope') !== ContaoCoreBundle::SCOPE_BACKEND
            || !is_array($GLOBALS['TL_DCA'][$table]['fields'])
        ) {
            return;
        }

        foreach ($GLOBALS['TL_DCA'][$table]['fields'] as $field) {
            if (isset($field['inputType']) && $field['inputType'] === 'fineUploader') {
                $this->assetsManager->includeAssets($this->assetsManager->getBackendAssets());
                break;
            }
        }
    }

    /**
     * Dispatch an AJAX request in the backend
     *
     * @param string        $action
     * @param DataContainer $dc
     *
     * @throws ResponseException
     */
    public function onExecutePostActions($action, DataContainer $dc)
    {
        try {
            $response = $this->dispatchAction($action, $dc);
        } catch (\Exception $e) {
            $this->logger->log(
                LogLevel::ERROR,
                $e->getMessage(),
                ['contao' => new ContaoContext(($e->getTrace())[1]['function'], TL_ERROR)]
            );

            $response = new Response('Bad Request', 400);
        }

        if ($response !== null) {
            throw new ResponseException($response);
        }
    }

    /**
     * Dispatch the action
     *
     * @param string        $action
     * @param DataContainer $dc
     *
     * @return Response|null
     */
    private function dispatchAction($action, DataContainer $dc)
    {
        if ($action === 'fineuploader_upload') {
            return $this->requestHandler->handleUploadRequest($this->requestStack->getCurrentRequest(), $dc);
        }

        if ($action === 'fineuploader_reload') {
            return $this->requestHandler->handleReloadRequest($this->requestStack->getCurrentRequest(), $dc);
        }

        return null;
    }
}
