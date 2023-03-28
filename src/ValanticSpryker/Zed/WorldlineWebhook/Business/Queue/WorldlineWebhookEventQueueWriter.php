<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\WorldlineWebhook\Business\Queue;

use Generated\Shared\Transfer\QueueSendMessageTransfer;
use Generated\Shared\Transfer\WorldlineWebhookRequestTransfer;
use Spryker\Client\Queue\QueueClientInterface;
use ValanticSpryker\Shared\WorldlineWebhook\WorldlineWebhookConstants;

class WorldlineWebhookEventQueueWriter implements WorldlineWebhookEventQueueWriterInterface
{
    /**
     * @param \Spryker\Client\Queue\QueueClientInterface $queueClient
     */
    public function __construct(private QueueClientInterface $queueClient)
    {
    }

    /**
     * @inheritDoc
     */
    public function writeEventToQueue(WorldlineWebhookRequestTransfer $apiRequestTransfer): void
    {
        $messageTransfer = new QueueSendMessageTransfer();
        $messageTransfer->setHeaders($apiRequestTransfer->getHeaderData());
        $messageTransfer->setBody($apiRequestTransfer->getRequestData()[0]);

        $this->queueClient->sendMessage(WorldlineWebhookConstants::WORLDLINE_WEBHOOK_EVENT_QUEUE_NAME, $messageTransfer);
    }
}
