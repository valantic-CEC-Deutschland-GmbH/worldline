<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\WorldlineWebhook\Business;

use Generated\Shared\Transfer\WorldlineWebhookRequestTransfer;
use Generated\Shared\Transfer\WorldlineWebhookResponseTransfer;
use Spryker\Zed\Kernel\Business\AbstractFacade;

/**
 * @method \ValanticSpryker\Zed\WorldlineWebhook\Business\WorldlineWebhookBusinessFactory getFactory()
 */
class WorldlineWebhookFacade extends AbstractFacade implements WorldlineWebhookFacadeInterface
{
    /**
     * @param array<\Generated\Shared\Transfer\QueueReceiveMessageTransfer> $queueMessageTransfers
     *
     * @return array<\Generated\Shared\Transfer\QueueReceiveMessageTransfer>
     */
    public function processMessages(array $queueMessageTransfers): array
    {
        return $this->getFactory()->createQueueMessageProcessor()->processMessages($queueMessageTransfers);
    }

    /**
     * @inheritDoc
     */
    public function filterApiRequestTransfer(WorldlineWebhookRequestTransfer $apiRequestTransfer)
    {
        return $this->getFactory()
            ->createRequestTransferFilter()
            ->filter(clone $apiRequestTransfer);
    }

    /**
     * @inheritDoc
     */
    public function dispatchWebhookEvent(WorldlineWebhookRequestTransfer $apiRequestTransfer): WorldlineWebhookResponseTransfer
    {
        return $this->getFactory()->createWebhookEventDispatcher()->dispatchWebhookEvent($apiRequestTransfer);
    }

    /**
     * @inheritDoc
     */
    public function writeEventToQueue(WorldlineWebhookRequestTransfer $apiRequestTransfer): void
    {
        $this->getFactory()->createWebhookEventQueueWriter()->writeEventToQueue($apiRequestTransfer);
    }
}
