<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\Writer;

use Generated\Shared\Transfer\OrderTransfer;
use Generated\Shared\Transfer\WebhookEventTransfer;
use Generated\Shared\Transfer\WorldlineCreateHostedCheckoutResponseTransfer;
use Generated\Shared\Transfer\WorldlineCreditCardTokenTransfer;
use Generated\Shared\Transfer\WorldlineGetHostedCheckoutStatusResponseTransfer;

interface WorldlineWriterInterface
{
    /**
     * @param \Generated\Shared\Transfer\WorldlineCreateHostedCheckoutResponseTransfer $responseTransfer
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return void
     */
    public function saveCreatedHostedCheckoutResponse(WorldlineCreateHostedCheckoutResponseTransfer $responseTransfer, OrderTransfer $orderTransfer): void;

    /**
     * @param \Generated\Shared\Transfer\WorldlineGetHostedCheckoutStatusResponseTransfer $responseTransfer
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return void
     */
    public function saveGetHostedCheckoutStatusResponse(WorldlineGetHostedCheckoutStatusResponseTransfer $responseTransfer, OrderTransfer $orderTransfer): void;

    /**
     * @param \Generated\Shared\Transfer\WebhookEventTransfer $webhookEventTransfer
     *
     * @return void
     */
    public function savePaymentEvent(WebhookEventTransfer $webhookEventTransfer): void;

    /**
     * @param \Generated\Shared\Transfer\WebhookEventTransfer $webhookEventTransfer
     *
     * @return void
     */
    public function updateWorldlineTokenByEvent(WebhookEventTransfer $webhookEventTransfer): void;

    /**
     * @param \Generated\Shared\Transfer\WebhookEventTransfer $webhookEventTransfer
     *
     * @return void
     */
    public function setTokenExpiredByEvent(WebhookEventTransfer $webhookEventTransfer): void;

    /**
     * @param \Generated\Shared\Transfer\WebhookEventTransfer $webhookEventTransfer
     *
     * @return void
     */
    public function markTokenDeletedByEvent(WebhookEventTransfer $webhookEventTransfer): void;

    /**
     * @param \Generated\Shared\Transfer\WorldlineCreditCardTokenTransfer $tokenTransfer
     *
     * @return void
     */
    public function markTokenDeletedById(WorldlineCreditCardTokenTransfer $tokenTransfer): void;
}
