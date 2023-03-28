<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\WorldlineWebhook\Communication\Plugin\Router;

use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Spryker\Zed\RouterExtension\Dependency\Plugin\RouterPluginInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * @method \ValanticSpryker\Zed\WorldlineWebhook\Business\WorldlineWebhookFacadeInterface getFacade()
 * @method \ValanticSpryker\Zed\WorldlineWebhook\Communication\WorldlineWebhookCommunicationFactory getFactory()
 * @method \ValanticSpryker\Zed\WorldlineWebhook\WorldlineWebhookConfig getConfig()
 */
class WorldlineWebhookRouterPlugin extends AbstractPlugin implements RouterPluginInterface
{
    /**
     * @inheritDoc
     */
    public function getRouter(): RouterInterface
    {
        return $this->getFactory()
            ->createWorldlineWebhookRouter();
    }
}
