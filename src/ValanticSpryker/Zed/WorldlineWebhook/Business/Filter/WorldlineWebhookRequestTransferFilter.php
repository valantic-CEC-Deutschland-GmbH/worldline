<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\WorldlineWebhook\Business\Filter;

use Generated\Shared\Transfer\WorldlineWebhookRequestTransfer;

class WorldlineWebhookRequestTransferFilter implements WorldlineWebhookRequestTransferFilterInterface
{
    /**
     * @param array<\ValanticSpryker\Zed\WorldlineWebhook\Communication\Plugin\Filter\WorldlineWebhookRequestTransferFilterPluginInterface> $webhookRequestTransferFilterPlugins
     */
    public function __construct(private array $webhookRequestTransferFilterPlugins)
    {
    }

    /**
     * @param \Generated\Shared\Transfer\WorldlineWebhookRequestTransfer $requestTransfer
     *
     * @return \Generated\Shared\Transfer\WorldlineWebhookRequestTransfer
     */
    public function filter(WorldlineWebhookRequestTransfer $requestTransfer): WorldlineWebhookRequestTransfer
    {
        foreach ($this->webhookRequestTransferFilterPlugins as $webhookRequestTransferFilterPlugin) {
            $requestTransfer = $webhookRequestTransferFilterPlugin->filter($requestTransfer);
        }

        return $requestTransfer;
    }
}
