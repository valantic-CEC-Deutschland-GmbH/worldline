<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\WorldlineWebhook\Communication\Plugin\EventListener;

use Generated\Shared\Transfer\WorldlineWebhookRequestTransfer;
use Generated\Shared\Transfer\WorldlineWebhookResponseTransfer;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use ValanticSpryker\Zed\WorldlineWebhook\WorldlineWebhookConfig;

/**
 * @method \ValanticSpryker\Zed\WorldlineWebhook\WorldlineWebhookConfig getConfig()
 * @method \ValanticSpryker\Zed\WorldlineWebhook\Business\WorldlineWebhookFacadeInterface getFacade()
 * @method \ValanticSpryker\Zed\WorldlineWebhook\Communication\WorldlineWebhookCommunicationFactory getFactory()
 */
class WorldlineWebhookGetRequestEventListenerPlugin extends AbstractPlugin implements WorldlineWebhookEventListenerPluginInterface
{
    /**
     * @inheritDoc
     */
    public function handleEvent(WorldlineWebhookRequestTransfer $apiRequestTransfer, WorldlineWebhookResponseTransfer $responseTransfer): WorldlineWebhookResponseTransfer
    {
        $requestType = $apiRequestTransfer->getRequestTypeOrFail();
        if ($requestType === 'GET') {
            $responseTransfer->setCode(WorldlineWebhookConfig::HTTP_CODE_SUCCESS);
            $headers = $apiRequestTransfer->getHeaderData();
            $verificationString = $headers[mb_strtolower(WorldlineWebhookConfig::HEADER_X_GCS_WEBHOOKS_ENDPOINT_VERIFICATION)];
            $responseTransfer->setData($verificationString);
            $responseTransfer->setIsPlainText(true);
        }

        return $responseTransfer;
    }
}
