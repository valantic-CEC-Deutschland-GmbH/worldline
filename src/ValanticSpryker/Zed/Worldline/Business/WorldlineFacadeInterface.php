<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business;

use Generated\Shared\Transfer\CheckoutResponseTransfer;
use Generated\Shared\Transfer\OrderTransfer;
use Generated\Shared\Transfer\PaymentMethodsTransfer;
use Generated\Shared\Transfer\QueueReceiveMessageTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\RestCheckoutRequestAttributesTransfer;
use Generated\Shared\Transfer\SaveOrderTransfer;
use Generated\Shared\Transfer\WorldlineCancelPaymentResponseTransfer;
use Generated\Shared\Transfer\WorldlineCancelPaymentTransfer;
use Generated\Shared\Transfer\WorldlineCapturePaymentRequestTransfer;
use Generated\Shared\Transfer\WorldlineCaptureResponseTransfer;
use Generated\Shared\Transfer\WorldlineCreateHostedCheckoutResponseTransfer;
use Generated\Shared\Transfer\WorldlineCreateHostedCheckoutTransfer;
use Generated\Shared\Transfer\WorldlineCreditCardTokenTransfer;
use Generated\Shared\Transfer\WorldlineDeleteTokenRequestTransfer;
use Generated\Shared\Transfer\WorldlineDeleteTokenResponseTransfer;
use Generated\Shared\Transfer\WorldlineGetHostedCheckoutStatusResponseTransfer;
use Generated\Shared\Transfer\WorldlineGetHostedCheckoutStatusTransfer;
use Generated\Shared\Transfer\WorldlineGetPaymentProductsRequestTransfer;
use Generated\Shared\Transfer\WorldlineGetPaymentProductsResponseTransfer;
use Generated\Shared\Transfer\WorldlineGetPaymentRequestTransfer;
use Generated\Shared\Transfer\WorldlineGetPaymentResponseTransfer;
use Generated\Shared\Transfer\WorldlineGetRefundRequestTransfer;
use Generated\Shared\Transfer\WorldlinePaymentTokenRequestTransfer;
use Generated\Shared\Transfer\WorldlinePaymentTokensResponseTransfer;
use Generated\Shared\Transfer\WorldlineRefundRequestTransfer;
use Generated\Shared\Transfer\WorldlineRefundResponseTransfer;
use Orm\Zed\Sales\Persistence\SpySalesOrderItem;

interface WorldlineFacadeInterface
{
    /**
     * @param \Generated\Shared\Transfer\WorldlineCreateHostedCheckoutTransfer $hostedCheckoutTransfer
     *
     * @return \Generated\Shared\Transfer\WorldlineCreateHostedCheckoutResponseTransfer
     */
    public function createHostedCheckout(WorldlineCreateHostedCheckoutTransfer $hostedCheckoutTransfer): WorldlineCreateHostedCheckoutResponseTransfer;

    /**
     * @param \Generated\Shared\Transfer\WorldlineGetHostedCheckoutStatusTransfer $getHostedCheckoutStatusTransfer
     *
     * @return \Generated\Shared\Transfer\WorldlineGetHostedCheckoutStatusResponseTransfer
     */
    public function getHostedCheckoutStatus(WorldlineGetHostedCheckoutStatusTransfer $getHostedCheckoutStatusTransfer): WorldlineGetHostedCheckoutStatusResponseTransfer;

    /**
     * @param \Generated\Shared\Transfer\WorldlineGetPaymentProductsRequestTransfer $getPaymentProductsRequestTransfer
     *
     * @return \Generated\Shared\Transfer\WorldlineGetPaymentProductsResponseTransfer
     */
    public function getPaymentProducts(WorldlineGetPaymentProductsRequestTransfer $getPaymentProductsRequestTransfer): WorldlineGetPaymentProductsResponseTransfer;

    /**
     * @param \Generated\Shared\Transfer\WorldlinePaymentTokenRequestTransfer $worldlinePaymentTokenRequestTransfer
     *
     * @return \Generated\Shared\Transfer\WorldlinePaymentTokensResponseTransfer
     */
    public function getPaymentTokens(WorldlinePaymentTokenRequestTransfer $worldlinePaymentTokenRequestTransfer): WorldlinePaymentTokensResponseTransfer;

    /**
     * @param \Generated\Shared\Transfer\WorldlineGetPaymentRequestTransfer $getPaymentTransfer
     *
     * @return \Generated\Shared\Transfer\WorldlineGetPaymentResponseTransfer
     */
    public function getPayment(WorldlineGetPaymentRequestTransfer $getPaymentTransfer): WorldlineGetPaymentResponseTransfer;

    /**
     * @param \Generated\Shared\Transfer\WorldlineCancelPaymentTransfer $cancelPaymentTransfer
     *
     * @return \Generated\Shared\Transfer\WorldlineCancelPaymentResponseTransfer
     */
    public function cancelPayment(WorldlineCancelPaymentTransfer $cancelPaymentTransfer): WorldlineCancelPaymentResponseTransfer;

    /**
     * @param \Generated\Shared\Transfer\WorldlineCapturePaymentRequestTransfer $capturePaymentTransfer
     *
     * @return \Generated\Shared\Transfer\WorldlineCaptureResponseTransfer
     */
    public function capturePayment(WorldlineCapturePaymentRequestTransfer $capturePaymentTransfer): WorldlineCaptureResponseTransfer;

    /**
     * @param \Generated\Shared\Transfer\WorldlineRefundRequestTransfer $refundRequestTransfer
     *
     * @return \Generated\Shared\Transfer\WorldlineRefundResponseTransfer
     */
    public function createRefund(WorldlineRefundRequestTransfer $refundRequestTransfer): WorldlineRefundResponseTransfer;

    /**
     * @param \Generated\Shared\Transfer\WorldlineGetRefundRequestTransfer $getRefundRequestTransfer
     *
     * @return \Generated\Shared\Transfer\WorldlineRefundResponseTransfer
     */
    public function getRefund(WorldlineGetRefundRequestTransfer $getRefundRequestTransfer): WorldlineRefundResponseTransfer;

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     * @param \Generated\Shared\Transfer\SaveOrderTransfer $saveOrderTransfer
     *
     * @return void
     */
    public function saveOrderPayment(QuoteTransfer $quoteTransfer, SaveOrderTransfer $saveOrderTransfer): void;

    /**
     * @param \Generated\Shared\Transfer\PaymentMethodsTransfer $paymentMethodsTransfer
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return \Generated\Shared\Transfer\PaymentMethodsTransfer
     */
    public function filterPaymentMethods(PaymentMethodsTransfer $paymentMethodsTransfer, QuoteTransfer $quoteTransfer): PaymentMethodsTransfer;

    /**
     * @param array<\Orm\Zed\Sales\Persistence\SpySalesOrderItem> $orderItems
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return void
     */
    public function handleCreateHostedCheckoutCommand(array $orderItems, OrderTransfer $orderTransfer): void;

    /**
     * @param \Generated\Shared\Transfer\RestCheckoutRequestAttributesTransfer $restCheckoutRequestAttributesTransfer
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return \Generated\Shared\Transfer\QuoteTransfer
     */
    public function mapRestCheckoutRequestAttributesToQuote(
        RestCheckoutRequestAttributesTransfer $restCheckoutRequestAttributesTransfer,
        QuoteTransfer $quoteTransfer
    ): QuoteTransfer;

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     * @param \Generated\Shared\Transfer\CheckoutResponseTransfer $checkoutResponseTransfer
     *
     * @return \Generated\Shared\Transfer\CheckoutResponseTransfer
     */
    public function orderPostSave(QuoteTransfer $quoteTransfer, CheckoutResponseTransfer $checkoutResponseTransfer): CheckoutResponseTransfer;

    /**
     * @param \Orm\Zed\Sales\Persistence\SpySalesOrderItem $orderItem
     *
     * @return bool
     */
    public function isHostedCheckoutCreated(SpySalesOrderItem $orderItem): bool;

    /**
     * @param \Orm\Zed\Sales\Persistence\SpySalesOrderItem $orderItem
     *
     * @return bool
     */
    public function isHostedCheckoutFailed(SpySalesOrderItem $orderItem): bool;

    /**
     * @param \Orm\Zed\Sales\Persistence\SpySalesOrderItem $orderItem
     *
     * @return bool
     */
    public function isHostedCheckoutTimedOut(SpySalesOrderItem $orderItem): bool;

    /**
     * @param array $orderItems
     * @param \Generated\Shared\Transfer\OrderTransfer $getOrderTransfer
     *
     * @return void
     */
    public function handleGetHostedCheckoutStatusCommand(array $orderItems, OrderTransfer $getOrderTransfer): void;

    /**
     * @param \Orm\Zed\Sales\Persistence\SpySalesOrderItem $orderItem
     *
     * @return bool
     */
    public function isHostedCheckoutPaymentCreated(SpySalesOrderItem $orderItem): bool;

    /**
     * @param \Orm\Zed\Sales\Persistence\SpySalesOrderItem $orderItem
     *
     * @return bool
     */
    public function isHostedCheckoutStatusCancelled(SpySalesOrderItem $orderItem): bool;

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return \Generated\Shared\Transfer\OrderTransfer
     */
    public function hydrateOrderTransferWithHostedCheckoutStatusForOrder(OrderTransfer $orderTransfer): OrderTransfer;

    /**
     * @param \Generated\Shared\Transfer\QueueReceiveMessageTransfer $queueMessage
     *
     * @return \Generated\Shared\Transfer\QueueReceiveMessageTransfer
     */
    public function processEventMessage(QueueReceiveMessageTransfer $queueMessage): QueueReceiveMessageTransfer;

    /**
     * @param \Orm\Zed\Sales\Persistence\SpySalesOrderItem $orderItem
     *
     * @return bool
     */
    public function isPaymentGuaranteed(SpySalesOrderItem $orderItem): bool;

    /**
     * @param \Orm\Zed\Sales\Persistence\SpySalesOrderItem $orderItem
     *
     * @return bool
     */
    public function isPaymentCancelled(SpySalesOrderItem $orderItem): bool;

    /**
     * @param \Orm\Zed\Sales\Persistence\SpySalesOrderItem $orderItem
     *
     * @return bool
     */
    public function isPaymentRejected(SpySalesOrderItem $orderItem): bool;

    /**
     * @param array $orderItems
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return void
     */
    public function handleGetPaymentStatusCommand(array $orderItems, OrderTransfer $orderTransfer): void;

    /**
     * @param array $orderItems
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return void
     */
    public function handleApprovePaymentCommand(array $orderItems, OrderTransfer $orderTransfer): void;

    /**
     * @param \Orm\Zed\Sales\Persistence\SpySalesOrderItem $orderItem
     *
     * @return bool
     */
    public function isPaymentCaptured(SpySalesOrderItem $orderItem): bool;

    /**
     * @param \Orm\Zed\Sales\Persistence\SpySalesOrderItem $orderItem *
     *
     * @return bool
     */
    public function isPaymentCaptureRejected(SpySalesOrderItem $orderItem): bool;

    /**
     * @param \Orm\Zed\Sales\Persistence\SpySalesOrderItem $orderItem
     *
     * @return bool
     */
    public function isPaymentCaptureTimedOut(SpySalesOrderItem $orderItem): bool;

    /**
     * @return int
     */
    public function deleteWorldlineTokensMarkedAsDeleted(): int;

    /**
     * Calls the Worldline API function DeleteToken and marks the Token as deleted in the DB
     *
     * @param \Generated\Shared\Transfer\WorldlineDeleteTokenRequestTransfer $tokenTransfer
     *
     * @return \Generated\Shared\Transfer\WorldlineDeleteTokenResponseTransfer
     */
    public function deletePaymentTokenById(WorldlineDeleteTokenRequestTransfer $tokenTransfer): WorldlineDeleteTokenResponseTransfer;

    /**
     * @param int $idToken
     *
     * @return \Generated\Shared\Transfer\WorldlineCreditCardTokenTransfer
     */
    public function findPaymentTokenById(int $idToken): WorldlineCreditCardTokenTransfer;
}
