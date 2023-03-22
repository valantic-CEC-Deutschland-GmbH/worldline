<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\EventProcessor;

use Generated\Shared\Transfer\WebhookEventTransfer;

interface EventProcessorInterface
{
    /**
     * @param \Generated\Shared\Transfer\WebhookEventTransfer $eventTransfer
     *
     * @return void
     */
    public function processEvent(WebhookEventTransfer $eventTransfer): void;
}
