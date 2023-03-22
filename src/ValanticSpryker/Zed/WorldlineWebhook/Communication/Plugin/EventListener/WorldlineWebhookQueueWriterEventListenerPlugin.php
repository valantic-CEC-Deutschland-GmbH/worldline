<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\WorldlineWebhook\Communication\Plugin\EventListener;

use Generated\Shared\Transfer\WorldlineWebhookRequestTransfer;
use Generated\Shared\Transfer\WorldlineWebhookResponseTransfer;
use Spryker\Shared\Log\LoggerTrait;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use ValanticSpryker\Zed\WorldlineWebhook\WorldlineWebhookConfig;

/**
 * @method \ValanticSpryker\Zed\WorldlineWebhook\WorldlineWebhookConfig getConfig()
 * @method \ValanticSpryker\Zed\WorldlineWebhook\Business\WorldlineWebhookFacadeInterface getFacade()
 * @method \ValanticSpryker\Zed\WorldlineWebhook\Communication\WorldlineWebhookCommunicationFactory getFactory()
 */
class WorldlineWebhookQueueWriterEventListenerPlugin extends AbstractPlugin implements WorldlineWebhookEventListenerPluginInterface
{
    use LoggerTrait;

    /**
     * @inheritDoc
     */
    public function handleEvent(WorldlineWebhookRequestTransfer $apiRequestTransfer, WorldlineWebhookResponseTransfer $responseTransfer): WorldlineWebhookResponseTransfer
    {
        $requestType = $apiRequestTransfer->getRequestTypeOrFail();
        if ($requestType === 'POST') {
            $this->getLogger()->debug('Writing webhook event to queue.');
            $this->getFacade()->writeEventToQueue($apiRequestTransfer);
            $responseTransfer->setCode(WorldlineWebhookConfig::HTTP_CODE_SUCCESS);
        }

        return $responseTransfer;
    }
}
