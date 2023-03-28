<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\WorldlineWebhook\Business\Dispatcher;

use Generated\Shared\Transfer\WorldlineWebhookRequestTransfer;
use Generated\Shared\Transfer\WorldlineWebhookResponseTransfer;

interface WorldlineWebhookEventDispatcherInterface
{
    /**
     * @param \Generated\Shared\Transfer\WorldlineWebhookRequestTransfer $apiRequestTransfer
     *
     * @return \Generated\Shared\Transfer\WorldlineWebhookResponseTransfer
     */
    public function dispatchWebhookEvent(WorldlineWebhookRequestTransfer $apiRequestTransfer): WorldlineWebhookResponseTransfer;
}
