<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\WorldlineWebhook\Business\Queue;

use Generated\Shared\Transfer\WorldlineWebhookRequestTransfer;

interface WorldlineWebhookEventQueueWriterInterface
{
    /**
     * @param \Generated\Shared\Transfer\WorldlineWebhookRequestTransfer $apiRequestTransfer
     *
     * @return void
     */
    public function writeEventToQueue(WorldlineWebhookRequestTransfer $apiRequestTransfer): void;
}
