<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\WorldlineWebhook\Communication\Plugin\Filter;

use Generated\Shared\Transfer\WorldlineWebhookRequestTransfer;

interface WorldlineWebhookRequestTransferFilterPluginInterface
{
    /**
     * @param \Generated\Shared\Transfer\WorldlineWebhookRequestTransfer $webhookRequestTransfer
     *
     * @return \Generated\Shared\Transfer\WorldlineWebhookRequestTransfer
     */
    public function filter(WorldlineWebhookRequestTransfer $webhookRequestTransfer): WorldlineWebhookRequestTransfer;
}
