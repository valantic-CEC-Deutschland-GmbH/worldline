<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\WorldlineWebhook\Business;

use Generated\Shared\Transfer\WorldlineWebhookRequestTransfer;
use Generated\Shared\Transfer\WorldlineWebhookResponseTransfer;

interface WorldlineWebhookFacadeInterface
{
    /**
     * @param array<\Generated\Shared\Transfer\QueueReceiveMessageTransfer> $queueMessageTransfers
     *
     * @return array<\Generated\Shared\Transfer\QueueReceiveMessageTransfer>
     */
    public function processMessages(array $queueMessageTransfers): array;

    /**
     * @param \Generated\Shared\Transfer\WorldlineWebhookRequestTransfer $apiRequestTransfer
     *
     * @return mixed
     */
    public function filterApiRequestTransfer(WorldlineWebhookRequestTransfer $apiRequestTransfer);

    /**
     * @param \Generated\Shared\Transfer\WorldlineWebhookRequestTransfer $apiRequestTransfer
     *
     * @return \Generated\Shared\Transfer\WorldlineWebhookResponseTransfer
     */
    public function dispatchWebhookEvent(WorldlineWebhookRequestTransfer $apiRequestTransfer): WorldlineWebhookResponseTransfer;

    /**
     * @param \Generated\Shared\Transfer\WorldlineWebhookRequestTransfer $apiRequestTransfer
     *
     * @return void
     */
    public function writeEventToQueue(WorldlineWebhookRequestTransfer $apiRequestTransfer): void;
}
