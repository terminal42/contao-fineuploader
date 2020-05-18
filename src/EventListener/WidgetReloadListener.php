<?php

declare(strict_types=1);

/*
 * FineUploader Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2020, terminal42 gmbh
 * @author     terminal42 <https://terminal42.ch>
 * @license    MIT
 */

namespace Terminal42\FineUploaderBundle\EventListener;

use Symfony\Component\HttpFoundation\Response;
use Terminal42\FineUploaderBundle\Event\WidgetReloadEvent;

class WidgetReloadListener
{
    /**
     * On widget reload.
     */
    public function onWidgetReload(WidgetReloadEvent $event): void
    {
        $event->setResponse(new Response($event->getWidget()->parseValues(), 200, ['Content-Type' => 'text/html']));
    }
}
