<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\WorldlineWebhook\Communication\Plugin\EventListener;

use Generated\Shared\Transfer\WorldlineWebhookRequestTransfer;
use Generated\Shared\Transfer\WorldlineWebhookResponseTransfer;

interface WorldlineWebhookEventListenerPluginInterface
{
    /**
     * @param \Generated\Shared\Transfer\WorldlineWebhookRequestTransfer $apiRequestTransfer
     * @param \Generated\Shared\Transfer\WorldlineWebhookResponseTransfer $responseTransfer
     *
     * @return \Generated\Shared\Transfer\WorldlineWebhookResponseTransfer
     */
    public function handleEvent(WorldlineWebhookRequestTransfer $apiRequestTransfer, WorldlineWebhookResponseTransfer $responseTransfer): WorldlineWebhookResponseTransfer;
}
