<?php

namespace Terminal42\FineUploaderBundle\EventListener;

use Symfony\Component\HttpFoundation\Response;
use Terminal42\FineUploaderBundle\Event\WidgetReloadEvent;

class WidgetReloadListener
{
    /**
     * On widget reload
     *
     * @param WidgetReloadEvent $event
     */
    public function onWidgetReload(WidgetReloadEvent $event)
    {
        $event->setResponse(new Response($event->getWidget()->parseValues(), 200, ['Content-Type' => 'text/html']));
    }
}
