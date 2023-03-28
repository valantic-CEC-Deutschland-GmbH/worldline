<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\WorldlineWebhook\Communication\EventListener;

use Symfony\Component\HttpKernel\Event\ControllerEvent;

interface WorldlineWebhookControllerEventListenerInterface
{
    /**
     * @param \Symfony\Component\HttpKernel\Event\ControllerEvent $controllerEvent
     *
     * @return void
     */
    public function onKernelControllerEvent(ControllerEvent $controllerEvent): void;
}
