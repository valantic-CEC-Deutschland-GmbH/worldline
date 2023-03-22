<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\WorldlineWebhook\Business\Dispatcher;

use Generated\Shared\Transfer\WorldlineWebhookRequestTransfer;
use Generated\Shared\Transfer\WorldlineWebhookResponseTransfer;
use ValanticSpryker\Zed\WorldlineWebhook\WorldlineWebhookConfig;

class WorldlineWebhookEventDispatcher implements WorldlineWebhookEventDispatcherInterface
{
    /**
     * @param array<\ValanticSpryker\Zed\WorldlineWebhook\Communication\Plugin\EventListener\WorldlineWebhookEventListenerPluginInterface> $worldlineWebhookEventListenerPlugins
     */
    public function __construct(private array $worldlineWebhookEventListenerPlugins)
    {
    }

    /**
     * @inheritDoc
     */
    public function dispatchWebhookEvent(WorldlineWebhookRequestTransfer $apiRequestTransfer): WorldlineWebhookResponseTransfer
    {
        $responseTransfer = new WorldlineWebhookResponseTransfer();
        $responseTransfer->setCode(WorldlineWebhookConfig::HTTP_CODE_BAD_REQUEST);

        foreach ($this->worldlineWebhookEventListenerPlugins as $worldlineWebhookEventListenerPlugin) {
            $responseTransfer = $worldlineWebhookEventListenerPlugin->handleEvent($apiRequestTransfer, $responseTransfer);
        }

        return $responseTransfer;
    }
}
