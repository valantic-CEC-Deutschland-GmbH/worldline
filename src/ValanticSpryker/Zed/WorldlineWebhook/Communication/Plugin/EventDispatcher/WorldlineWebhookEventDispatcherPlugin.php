<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\WorldlineWebhook\Communication\Plugin\EventDispatcher;

use Spryker\Service\Container\ContainerInterface;
use Spryker\Shared\EventDispatcher\EventDispatcherInterface;
use Spryker\Shared\EventDispatcherExtension\Dependency\Plugin\EventDispatcherPluginInterface;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @method \ValanticSpryker\Zed\WorldlineWebhook\Business\WorldlineWebhookFacadeInterface getFacade()
 * @method \ValanticSpryker\Zed\WorldlineWebhook\Communication\WorldlineWebhookCommunicationFactory getFactory()
 * @method \ValanticSpryker\Zed\WorldlineWebhook\WorldlineWebhookConfig getConfig()
 */
class WorldlineWebhookEventDispatcherPlugin extends AbstractPlugin implements EventDispatcherPluginInterface
{
    /**
     * @param \Spryker\Shared\EventDispatcher\EventDispatcherInterface $eventDispatcher
     * @param \Spryker\Service\Container\ContainerInterface $container
     *
     * @return \Spryker\Shared\EventDispatcher\EventDispatcherInterface
     */
    public function extend(EventDispatcherInterface $eventDispatcher, ContainerInterface $container): EventDispatcherInterface
    {
        $eventDispatcher->addListener(KernelEvents::CONTROLLER, function (ControllerEvent $event): void {
            $this->getFactory()->createApiControllerEventListener()->onKernelControllerEvent($event);
        });

        return $eventDispatcher;
    }
}
