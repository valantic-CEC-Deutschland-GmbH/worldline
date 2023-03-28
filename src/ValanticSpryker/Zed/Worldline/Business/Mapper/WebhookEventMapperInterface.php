<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\Mapper;

use Generated\Shared\Transfer\WebhookEventTransfer;
use Ingenico\Connect\Sdk\Domain\Webhooks\WebhooksEvent;

interface WebhookEventMapperInterface
{
    /**
     * @param \Ingenico\Connect\Sdk\Domain\Webhooks\WebhooksEvent $webhookEvent
     *
     * @return \Generated\Shared\Transfer\WebhookEventTransfer
     */
    public function mapWebhooksEventToWebhookEventTransfer(WebhooksEvent $webhookEvent): WebhookEventTransfer;
}
