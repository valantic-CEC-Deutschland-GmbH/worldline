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
use Spryker\Zed\Kernel\Business\AbstractFacade;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @method \ValanticSpryker\Zed\Worldline\Business\WorldlineBusinessFactory getFactory()
 * @method \ValanticSpryker\Zed\Worldline\Persistence\WorldlineEntityManagerInterface getEntityManager()
 * @method \ValanticSpryker\Zed\Worldline\Persistence\WorldlineRepositoryInterface getRepository()
 */
class WorldlineFacade extends AbstractFacade implements WorldlineFacadeInterface
{
    /**
     * @param \Generated\Shared\Transfer\WorldlineCreateHostedCheckoutTransfer $createHostedCheckoutTransfer
     *
     * @return \Generated\Shared\Transfer\WorldlineCreateHostedCheckoutResponseTransfer
     */
    public function createHostedCheckout(WorldlineCreateHostedCheckoutTransfer $createHostedCheckoutTransfer): WorldlineCreateHostedCheckoutResponseTransfer
    {
        /** @var \Generated\Shared\Transfer\WorldlineCreateHostedCheckoutResponseTransfer $responseTransfer */
        $responseTransfer = $this->getFactory()->createCreateHostedCheckoutApiCallHandler()->handleApiCall($createHostedCheckoutTransfer);

        return $responseTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\WorldlineGetHostedCheckoutStatusTransfer $getHostedCheckoutStatusTransfer
     *
     * @return \Generated\Shared\Transfer\WorldlineGetHostedCheckoutStatusResponseTransfer
     */
    public function getHostedCheckoutStatus(WorldlineGetHostedCheckoutStatusTransfer $getHostedCheckoutStatusTransfer): WorldlineGetHostedCheckoutStatusResponseTransfer
    {
        /** @var \Generated\Shared\Transfer\WorldlineGetHostedCheckoutStatusResponseTransfer $responseTransfer */
        $responseTransfer = $this->getFactory()->createGetHostedCheckoutStatusApiCallHandler()->handleApiCall($getHostedCheckoutStatusTransfer);

        return $responseTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\WorldlineGetPaymentProductsRequestTransfer $getPaymentProductsRequestTransfer
     *
     * @return \Generated\Shared\Transfer\WorldlineGetPaymentProductsResponseTransfer
     */
    public function getPaymentProducts(WorldlineGetPaymentProductsRequestTransfer $getPaymentProductsRequestTransfer): WorldlineGetPaymentProductsResponseTransfer
    {
        /** @var \Generated\Shared\Transfer\WorldlineGetPaymentProductsResponseTransfer $responseTransfer */
        $responseTransfer = $this->getFactory()->createGetPaymentProductsApiCallHandler()->handleApiCall($getPaymentProductsRequestTransfer);

        return $responseTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\WorldlineGetPaymentRequestTransfer $getPaymentTransfer
     *
     * @return \Generated\Shared\Transfer\WorldlineGetPaymentResponseTransfer
     */
    public function getPayment(WorldlineGetPaymentRequestTransfer $getPaymentTransfer): WorldlineGetPaymentResponseTransfer
    {
        /** @var \Generated\Shared\Transfer\WorldlineGetPaymentResponseTransfer $responseTransfer */
        $responseTransfer = $this->getFactory()->createGetPaymentApiCallHandler()->handleApiCall($getPaymentTransfer);

        return $responseTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\WorldlineCancelPaymentTransfer $cancelPaymentTransfer
     *
     * @return \Generated\Shared\Transfer\WorldlineCancelPaymentResponseTransfer
     */
    public function cancelPayment(WorldlineCancelPaymentTransfer $cancelPaymentTransfer): WorldlineCancelPaymentResponseTransfer
    {
        /** @var \Generated\Shared\Transfer\WorldlineCancelPaymentResponseTransfer $responseTransfer */
        $responseTransfer = $this->getFactory()->createCancelPaymentApiCallHandler()->handleApiCall($cancelPaymentTransfer);

        return $responseTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\WorldlineCapturePaymentRequestTransfer $capturePaymentTransfer
     *
     * @return \Generated\Shared\Transfer\WorldlineCaptureResponseTransfer
     */
    public function capturePayment(WorldlineCapturePaymentRequestTransfer $capturePaymentTransfer): WorldlineCaptureResponseTransfer
    {
        /** @var \Generated\Shared\Transfer\WorldlineCaptureResponseTransfer $responseTransfer */
        $responseTransfer = $this->getFactory()->createCapturePaymentApiCallHandler()->handleApiCall($capturePaymentTransfer);

        return $responseTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\WorldlineRefundRequestTransfer $refundRequestTransfer
     *
     * @return \Generated\Shared\Transfer\WorldlineRefundResponseTransfer
     */
    public function createRefund(WorldlineRefundRequestTransfer $refundRequestTransfer): WorldlineRefundResponseTransfer
    {
        /** @var \Generated\Shared\Transfer\WorldlineRefundResponseTransfer $responseTransfer */
        $responseTransfer = $this->getFactory()->createRefundApiCallHandler()->handleApiCall($refundRequestTransfer);

        return $responseTransfer;
    }

    /**
     * @inheritDoc
     */
    public function getRefund(WorldlineGetRefundRequestTransfer $getRefundRequestTransfer): WorldlineRefundResponseTransfer
    {
        /** @var \Generated\Shared\Transfer\WorldlineRefundResponseTransfer $responseTransfer */
        $responseTransfer = $this->getFactory()->createGetRefundApiCallHandler()->handleApiCall($getRefundRequestTransfer);

        return $responseTransfer;
    }

    /**
     * @inheritDoc
     */
    public function saveOrderPayment(QuoteTransfer $quoteTransfer, SaveOrderTransfer $saveOrderTransfer): void
    {
        $this->getFactory()->createOrderManager()->saveOrderPayment($quoteTransfer, $saveOrderTransfer);
    }

    /**
     * @inheritDoc
     */
    public function filterPaymentMethods(PaymentMethodsTransfer $paymentMethodsTransfer, QuoteTransfer $quoteTransfer): PaymentMethodsTransfer
    {
        return $this->getFactory()->createPaymentMethodsFilter()->filterPaymentMethods($paymentMethodsTransfer, $quoteTransfer);
    }

    /**
     * @inheritDoc
     */
    public function handleCreateHostedCheckoutCommand(array $orderItems, OrderTransfer $orderTransfer): void
    {
        $this->getFactory()->createCreateHostedCheckoutCommandHandler()->handle($orderItems, $orderTransfer);
    }

    /**
     * @inheritDoc
     */
    public function handleGetHostedCheckoutStatusCommand(array $orderItems, OrderTransfer $getOrderTransfer): void
    {
        $this->getFactory()->createGetHostedCheckoutStatusCommandHandler()->handle($orderItems, $getOrderTransfer);
    }

    /**
     * @inheritDoc
     */
    public function mapRestCheckoutRequestAttributesToQuote(
        RestCheckoutRequestAttributesTransfer $restCheckoutRequestAttributesTransfer,
        QuoteTransfer $quoteTransfer
    ): QuoteTransfer {
        return $this->getFactory()->createQuoteMapper()->mapPaymentsToQuote($restCheckoutRequestAttributesTransfer, $quoteTransfer);
    }

    /**
     * @inheritDoc
     */
    public function orderPostSave(QuoteTransfer $quoteTransfer, CheckoutResponseTransfer $checkoutResponseTransfer): CheckoutResponseTransfer
    {
        return $this->getFactory()->createPostSaveHook()->executePostSaveHook($quoteTransfer, $checkoutResponseTransfer);
    }

    /**
     * @inheritDoc
     */
    public function isHostedCheckoutCreated(SpySalesOrderItem $orderItem): bool
    {
        return $this->getFactory()->createHostedCheckoutCreatedChecker()->isHostedCheckoutCreated($orderItem);
    }

    /**
     * @inheritDoc
     */
    public function isHostedCheckoutFailed(SpySalesOrderItem $orderItem): bool
    {
        return $this->getFactory()->createHostedCheckoutFailedChecker()->isHostedCheckoutFailed($orderItem);
    }

    /**
     * @inheritDoc
     */
    public function isHostedCheckoutTimedOut(SpySalesOrderItem $orderItem): bool
    {
        return $this->getFactory()->createHostedCheckoutStatusTimeoutChecker()->isHostedCheckoutStatusTakingTooLong($orderItem);
    }

    /**
     * @inheritDoc
     */
    public function isHostedCheckoutPaymentCreated(SpySalesOrderItem $orderItem): bool
    {
        return $this->getFactory()->createHostedCheckoutPaymentCreatedChecker()->isHostedCheckoutPaymentCreated($orderItem);
    }

    /**
     * @inheritDoc
     */
    public function isHostedCheckoutStatusCancelled(SpySalesOrderItem $orderItem): bool
    {
        return $this->getFactory()->createHostedCheckoutStatusCancelledChecker()->isHostedCheckoutStatusCancelled($orderItem);
    }

    /**
     * @inheritDoc
     */
    public function hydrateOrderTransferWithHostedCheckoutStatusForOrder(OrderTransfer $orderTransfer): OrderTransfer
    {
        return $this->getFactory()->createHostedCheckoutOrderHydrator()->hydrateOrderTransfer($orderTransfer);
    }

    /**
     * @inheritDoc
     */
    public function processEventMessage(QueueReceiveMessageTransfer $queueMessage): QueueReceiveMessageTransfer
    {
        return $this->getFactory()->createEventMessageProcessor()->processEventMessage($queueMessage);
    }

    /**
     * @inheritDoc
     */
    public function isPaymentGuaranteed(SpySalesOrderItem $orderItem): bool
    {
        return $this->getFactory()->createPaymentGuaranteedChecker()->isPaymentGuaranteed($orderItem);
    }

    /**
     * @inheritDoc
     */
    public function isPaymentCancelled(SpySalesOrderItem $orderItem): bool
    {
        return $this->getFactory()->createPaymentCancelledChecker()->isPaymentCancelled($orderItem);
    }

    /**
     * @inheritDoc
     */
    public function isPaymentRejected(SpySalesOrderItem $orderItem): bool
    {
        return $this->getFactory()->createPaymentRejectedChecker()->isPaymentRejected($orderItem);
    }

    /**
     * @inheritDoc
     */
    public function handleGetPaymentStatusCommand(array $orderItems, OrderTransfer $orderTransfer): void
    {
        $this->getFactory()->createGetPaymentStatusCommandHandler()->handle($orderItems, $orderTransfer);
    }

    /**
     * @inheritDoc
     */
    public function handleApprovePaymentCommand(array $orderItems, OrderTransfer $orderTransfer): void
    {
        $this->getFactory()->createApprovePaymentCommandHandler()->handle($orderItems, $orderTransfer);
    }

    /**
     * @inheritDoc
     */
    public function isPaymentCaptured(SpySalesOrderItem $orderItem): bool
    {
        return $this->getFactory()->createPaymentCapturedChecker()->isPaymentCaptured($orderItem);
    }

    /**
     * @param \Orm\Zed\Sales\Persistence\SpySalesOrderItem $orderItem
     *
     * @return bool
     */
    public function isPaymentCaptureRejected(SpySalesOrderItem $orderItem): bool
    {
        return $this->getFactory()->createPaymentCaptureRejectedChecker()->isPaymentCaptureRejected($orderItem);
    }

    /**
     * @inheritDoc
     */
    public function isPaymentCaptureTimedOut(SpySalesOrderItem $orderItem): bool
    {
        return $this->getFactory()->createPaymentCaptureTimedOutChecker()->isPaymentCaptureTimedOut($orderItem);
    }

    /**
     * @inheritDoc
     */
    public function getPaymentTokens(WorldlinePaymentTokenRequestTransfer $worldlinePaymentTokenRequestTransfer): WorldlinePaymentTokensResponseTransfer
    {
        return $this->getFactory()->createTokenReader()->getPaymentTokensByCustomerId($worldlinePaymentTokenRequestTransfer);
    }

    /**
     * @inheritDoc
     */
    public function deleteWorldlineTokensMarkedAsDeleted(): int
    {
        return $this->getFactory()->createDeletedWorldlineTokenRemover()->deleteWorldlineTokensMarkedAsDeleted();
    }

    /**
     * @inheritDoc
     */
    public function deletePaymentTokenById(WorldlineDeleteTokenRequestTransfer $tokenTransfer): WorldlineDeleteTokenResponseTransfer
    {
        return $this->getFactory()->createPaymentTokenDeleter()->deletePaymentTokenById($tokenTransfer);
    }

    /**
     * @inheritDoc
     */
    public function findPaymentTokenById(int $idToken): WorldlineCreditCardTokenTransfer
    {
        return $this->getRepository()->findPaymentTokenById($idToken);
    }
}
