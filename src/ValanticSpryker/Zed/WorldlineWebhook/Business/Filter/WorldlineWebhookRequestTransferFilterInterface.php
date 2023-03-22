<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\WorldlineWebhook\Business\Filter;

use Generated\Shared\Transfer\WorldlineWebhookRequestTransfer;

interface WorldlineWebhookRequestTransferFilterInterface
{
    /**
     * @param \Generated\Shared\Transfer\WorldlineWebhookRequestTransfer $requestTransfer
     *
     * @return \Generated\Shared\Transfer\WorldlineWebhookRequestTransfer
     */
    public function filter(WorldlineWebhookRequestTransfer $requestTransfer): WorldlineWebhookRequestTransfer;
}
