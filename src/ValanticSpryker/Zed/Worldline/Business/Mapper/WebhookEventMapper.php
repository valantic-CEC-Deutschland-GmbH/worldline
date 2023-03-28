<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\Mapper;

use Generated\Shared\Transfer\WebhookEventTransfer;
use Ingenico\Connect\Sdk\Domain\Webhooks\WebhooksEvent;

class WebhookEventMapper extends AbstractWorldlineMapper implements WebhookEventMapperInterface
{
    /**
     * @inheritDoc
     */
    public function mapWebhooksEventToWebhookEventTransfer(WebhooksEvent $webhookEvent): WebhookEventTransfer
    {
        $webhookEventJson = $webhookEvent->toJson();
        $webhookEventArray = json_decode($webhookEventJson, true, 512, JSON_THROW_ON_ERROR);

        $webhookEventTransfer = new WebhookEventTransfer();
        $webhookEventTransfer->fromArray($webhookEventArray, true);

        return $webhookEventTransfer;
    }
}
