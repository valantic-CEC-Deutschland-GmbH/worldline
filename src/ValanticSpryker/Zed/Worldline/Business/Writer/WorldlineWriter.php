<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\Writer;

use DateTime;
use DateTimeZone;
use Generated\Shared\Transfer\OrderTransfer;
use Generated\Shared\Transfer\PaymentEventDataTransfer;
use Generated\Shared\Transfer\PaymentWorldlineTransactionStatusTransfer;
use Generated\Shared\Transfer\PaymentWorldlineTransfer;
use Generated\Shared\Transfer\WebhookEventTransfer;
use Generated\Shared\Transfer\WorldlineCardPaymentMethodSpecificOutputTransfer;
use Generated\Shared\Transfer\WorldlineCardTransfer;
use Generated\Shared\Transfer\WorldlineCreateHostedCheckoutResponseTransfer;
use Generated\Shared\Transfer\WorldlineCreditCardTokenTransfer;
use Generated\Shared\Transfer\WorldlineGetHostedCheckoutStatusResponseTransfer;
use Generated\Shared\Transfer\WorldlinePaymentHostedCheckoutTransfer;
use Generated\Shared\Transfer\WorldlinePaymentStatusOutputTransfer;
use Generated\Shared\Transfer\WorldlineThreeDSecureDataTransfer;
use Generated\Shared\Transfer\WorldlineTokenEventDataTransfer;
use Orm\Zed\Worldline\Persistence\VsyWorldlineRestLog;
use Orm\Zed\Worldline\Persistence\VsyWorldlineRestReceiveLog;
use ValanticSpryker\Shared\Worldline\WorldlineConstants;
use ValanticSpryker\Zed\Worldline\Business\Exception\MerchantReferenceNotSetException;
use ValanticSpryker\Zed\Worldline\Business\Timestamp\WorldlineTimestampConverterInterface;
use ValanticSpryker\Zed\Worldline\Persistence\WorldlineEntityManagerInterface;
use ValanticSpryker\Zed\Worldline\Persistence\WorldlineQueryContainerInterface;
use ValanticSpryker\Zed\Worldline\WorldlineConfig;
use Spryker\Shared\Log\LoggerTrait;

class WorldlineWriter implements WorldlineWriterInterface
{
    use LoggerTrait;

    /**
     * @param \ValanticSpryker\Zed\Worldline\Persistence\WorldlineEntityManagerInterface $entityManager
     * @param \ValanticSpryker\Zed\Worldline\Persistence\WorldlineQueryContainerInterface $worldlineQueryContainer
     * @param \ValanticSpryker\Zed\Worldline\Business\Timestamp\WorldlineTimestampConverterInterface $worldlineTimestampConverter
     * @param \ValanticSpryker\Zed\Worldline\WorldlineConfig $worldlineConfig
     */
    public function __construct(private WorldlineEntityManagerInterface $entityManager, private WorldlineQueryContainerInterface $worldlineQueryContainer, private WorldlineTimestampConverterInterface $worldlineTimestampConverter, private WorldlineConfig $worldlineConfig)
    {
    }

    /**
     * @param \Generated\Shared\Transfer\WorldlineCreateHostedCheckoutResponseTransfer $responseTransfer
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return void
     */
    public function saveCreatedHostedCheckoutResponse(WorldlineCreateHostedCheckoutResponseTransfer $responseTransfer, OrderTransfer $orderTransfer): void
    {
        $paymentWorldlineTransfer = new PaymentWorldlineTransfer();

        $paymentWorldlineTransfer->setMerchantReference($responseTransfer->getMerchantReference());

        $paymentHostedCheckoutTransfer = new WorldlinePaymentHostedCheckoutTransfer();
        $paymentHostedCheckoutTransfer->setReturnmac($responseTransfer->getRETURNMAC());
        $paymentHostedCheckoutTransfer->setHostedCheckoutId($responseTransfer->getHostedCheckoutId());
        $paymentHostedCheckoutTransfer->setPartialRedirectUrl($responseTransfer->getPartialRedirectUrl());

        $paymentWorldlineTransfer->setPaymentHostedCheckout($paymentHostedCheckoutTransfer);

        $this->entityManager->updatePaymentWorldline($paymentWorldlineTransfer);

        if ($responseTransfer->getInvalidTokens()) {
            $tokens = $responseTransfer->getInvalidTokens();

            foreach ($tokens as $token) {
                $tokenEntities = $this->worldlineQueryContainer->findTokenByExternalTokenId($token);
                foreach ($tokenEntities as $tokenEntity) {
                    $now = new DateTime('now', new DateTimeZone('UTC'));
                    $tokenEntity->setDeletedAt((string)$now->getTimestamp());
                    $tokenEntity->save();
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function saveGetHostedCheckoutStatusResponse(WorldlineGetHostedCheckoutStatusResponseTransfer $responseTransfer, OrderTransfer $orderTransfer): void
    {
        $createdPaymentOutput = $responseTransfer->getCreatedPaymentOutput();
        if (!$createdPaymentOutput || !$createdPaymentOutput->getPayment() || !$responseTransfer->getIsSuccess()) {
            return;
        }

        $paymentWorldlineTransfer = new PaymentWorldlineTransfer();
        $paymentWorldlineTransfer->setFkSalesOrder($orderTransfer->getIdSalesOrder());
        $paymentWorldlineTransfer->setPaymentHostedCheckout(new WorldlinePaymentHostedCheckoutTransfer());

        switch ($responseTransfer->getStatus()) {
            case WorldlineConstants::STATUS_HOSTED_CHECKOUT_STATUS_PAYMENT_CREATED:
                $paymentWorldlineTransfer->setPaymentId($createdPaymentOutput->getPayment()->getId());

                break;
            default:
                break;
        }

        $this->entityManager->updatePaymentWorldline($paymentWorldlineTransfer);

        if (
            $createdPaymentOutput->getTokenizationSucceeded()
            && $createdPaymentOutput->getPayment()->getPaymentOutput()?->getCardPaymentMethodSpecificOutput()
        ) {
            $orderTransfer->requireCustomer();

            $paymentOutput = $createdPaymentOutput->getPayment()->getPaymentOutput();
            $cardPaymentSpecificOutputTransfer = $paymentOutput->getCardPaymentMethodSpecificOutput();

            $threeDSecureDataTransfer = null;
            if ($cardPaymentSpecificOutputTransfer?->getThreeDSecureResults()?->getThreeDSecureData()) {
                $threeDSecureDataTransfer = $cardPaymentSpecificOutputTransfer->getThreeDSecureResults()->getThreeDSecureData();
                $threeDSecureDataTransfer = $this->entityManager->saveThreeDSecureData($threeDSecureDataTransfer);
            }

            $idCustomer = $orderTransfer->getCustomer()->getIdCustomer();
            $preExistingTokens = '';

            $token = $cardPaymentSpecificOutputTransfer->getToken();
            if (!$token) {
                $preExistingTokens = $this->getExistingTokens($idCustomer);
            }

            $tokens = $createdPaymentOutput->getTokens();
            if ($tokens === $preExistingTokens) {
                return;
            }

            if ($token) {
                $tokens = $token;
            } else {
                foreach (explode(',', $preExistingTokens) as $preExistingToken) {
                    $tokens = str_replace($preExistingToken, '', $tokens);
                }
            }
            $cardTransfer = $cardPaymentSpecificOutputTransfer->getCard();
            $this->saveNewOrUpdatedTokens($tokens, $idCustomer, $threeDSecureDataTransfer, $cardPaymentSpecificOutputTransfer, $cardTransfer);
        }
    }

    /**
     * @param \Generated\Shared\Transfer\WebhookEventTransfer $webhookEventTransfer
     *
     * @throws \ValanticSpryker\Zed\Worldline\Business\Exception\MerchantReferenceNotSetException
     *
     * @return void
     */
    public function savePaymentEvent(WebhookEventTransfer $webhookEventTransfer): void
    {
        if ($this->worldlineQueryContainer->isEventAlreadyHandled($webhookEventTransfer) || !$webhookEventTransfer->getPayment()) {
            $this->getLogger()->debug('Received webhook event that has already been handled.');

            return;
        }

        if (
            (!$webhookEventTransfer->getPayment()->getPaymentOutput())
            || (!$webhookEventTransfer->getPayment()->getPaymentOutput()->getReferences())
            || (!$webhookEventTransfer->getPayment()->getPaymentOutput()->getReferences()->getMerchantReference())
        ) {
            throw new MerchantReferenceNotSetException();
        }

        $paymentWorldlineEntity = $this->worldlineQueryContainer->findPaymentWorldlineByMerchantReference($webhookEventTransfer->getPayment()->getPaymentOutput()->getReferences()->getMerchantReference());

        if (!$paymentWorldlineEntity) {
            $this->getLogger()->debug('Received webhook event with unknown order reference.');

            return;
        }

        $idPaymentWorldline = $paymentWorldlineEntity->getIdPaymentWorldline();

        $paymentEventDataTransfer = $webhookEventTransfer->getPayment();

        $worldlineRestLog = $this->saveWebhookEventLogs($webhookEventTransfer, $idPaymentWorldline);

        $paymentWorldlineTransactionStatusTransfer = $this->getPaymentWorldlineTransactionStatusTransfer($idPaymentWorldline, $worldlineRestLog->getIdWorldlineRestLog(), $paymentEventDataTransfer);

        $this->entityManager->savePaymentWorldlineTransactionStatus($paymentWorldlineTransactionStatusTransfer);

        $this->getLogger()->debug('done saving payment webhook event');
    }

    /**
     * @param int $idPaymentWorldline
     * @param int $idWorldlineRestLog
     * @param \Generated\Shared\Transfer\PaymentEventDataTransfer $paymentEventDataTransfer
     *
     * @return \Generated\Shared\Transfer\PaymentWorldlineTransactionStatusTransfer
     */
    protected function getPaymentWorldlineTransactionStatusTransfer(
        int $idPaymentWorldline,
        int $idWorldlineRestLog,
        PaymentEventDataTransfer $paymentEventDataTransfer
    ): PaymentWorldlineTransactionStatusTransfer {
        $paymentWorldlineTransactionStatusTransfer = new PaymentWorldlineTransactionStatusTransfer();
        $paymentWorldlineTransactionStatusTransfer->setFkPaymentWorldline($idPaymentWorldline);
        $paymentWorldlineTransactionStatusTransfer->setFkWorldlineRestLog($idWorldlineRestLog);
        $paymentWorldlineTransactionStatusTransfer->setTransactionType('payment');

        $status = $paymentEventDataTransfer->getStatus();
        $paymentWorldlineTransactionStatusTransfer->setStatus($status);
        $paymentEventDataTransfer->requirePaymentOutput();

        $paymentOutput = $paymentEventDataTransfer->getPaymentOutput();
        $paymentOutput->requireAmountOfMoney();
        $paymentWorldlineTransactionStatusTransfer->setAmount($paymentOutput->getAmountOfMoney()->getAmount());

        $paymentEventDataTransfer->requireStatusOutput();
        $statusOutput = $paymentEventDataTransfer->getStatusOutput();
        $paymentWorldlineTransactionStatusTransfer = $this->setPaymentStatusOutputFieldsInTransactionStatus($statusOutput, $paymentWorldlineTransactionStatusTransfer);

        return $paymentWorldlineTransactionStatusTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\WebhookEventTransfer $webhookEventTransfer
     * @param int $idReceiveLog
     * @param int|null $idPaymentWorldline
     *
     * @return \Orm\Zed\Worldline\Persistence\VsyWorldlineRestLog
     */
    protected function getWorldlineRestLog(
        WebhookEventTransfer $webhookEventTransfer,
        int $idReceiveLog,
        ?int $idPaymentWorldline
    ): VsyWorldlineRestLog {
        $worldlineRestLog = new VsyWorldlineRestLog();

        $worldlineRestLog->setEventId($webhookEventTransfer->getId());
        $worldlineRestLog->setEventType($webhookEventTransfer->getType());
        $worldlineRestLog->setEventApiVersion($webhookEventTransfer->getApiVersion());
        $worldlineRestLog->setEventCreationDate($webhookEventTransfer->getCreated());
        $worldlineRestLog->setMerchantId($webhookEventTransfer->getMerchantId());
        $worldlineRestLog->setFkWorldlineRestReceiveLog($idReceiveLog);

        $worldlineRestLog->setFkPaymentWorldline($idPaymentWorldline);

        return $worldlineRestLog;
    }

    /**
     * @param \Generated\Shared\Transfer\WebhookEventTransfer $webhookEventTransfer
     *
     * @return \Orm\Zed\Worldline\Persistence\VsyWorldlineRestReceiveLog
     */
    protected function getReceiveLogEntity(WebhookEventTransfer $webhookEventTransfer): VsyWorldlineRestReceiveLog
    {
        $restReceiveLogEntity = new VsyWorldlineRestReceiveLog();

        $restReceiveLogEntity->setEventId($webhookEventTransfer->getId());
        $restReceiveLogEntity->setEventBody(
            json_encode(
                $webhookEventTransfer->toArray(true, true),
                JSON_PRETTY_PRINT,
            ),
        );

        return $restReceiveLogEntity;
    }

    /**
     * @param \Generated\Shared\Transfer\WorldlinePaymentStatusOutputTransfer $statusOutput
     * @param \Generated\Shared\Transfer\PaymentWorldlineTransactionStatusTransfer $paymentWorldlineTransactionStatusTransfer
     *
     * @return \Generated\Shared\Transfer\PaymentWorldlineTransactionStatusTransfer
     */
    protected function setPaymentStatusOutputFieldsInTransactionStatus(
        WorldlinePaymentStatusOutputTransfer $statusOutput,
        PaymentWorldlineTransactionStatusTransfer $paymentWorldlineTransactionStatusTransfer
    ): PaymentWorldlineTransactionStatusTransfer {
        $paymentWorldlineTransactionStatusTransfer->setAuthorized($statusOutput->getIsAuthorized());
        $paymentWorldlineTransactionStatusTransfer->setCancellable($statusOutput->getIsCancellable());
        $paymentWorldlineTransactionStatusTransfer->setRefundable($statusOutput->getIsRefundable());

        $paymentWorldlineTransactionStatusTransfer->setStatusCategory($statusOutput->getStatusCategory());
        $paymentWorldlineTransactionStatusTransfer->setStatusCode($statusOutput->getStatusCode());
        $paymentWorldlineTransactionStatusTransfer->setStatusCodeChangeDateTime($this->worldlineTimestampConverter->getWorldlineTimestampInUTC($statusOutput->getStatusCodeChangeDateTime()));

        return $paymentWorldlineTransactionStatusTransfer;
    }

    /**
     * @param string $tokens
     * @param int $idCustomer
     * @param \Generated\Shared\Transfer\WorldlineThreeDSecureDataTransfer|null $threeDSecureDataTransfer
     * @param \Generated\Shared\Transfer\WorldlineCardPaymentMethodSpecificOutputTransfer $cardPaymentSpecificOutPutTransfer
     * @param \Generated\Shared\Transfer\WorldlineCardTransfer $cardTransfer
     *
     * @return void
     */
    protected function saveNewOrUpdatedTokens(
        string $tokens,
        int $idCustomer,
        ?WorldlineThreeDSecureDataTransfer $threeDSecureDataTransfer,
        WorldlineCardPaymentMethodSpecificOutputTransfer $cardPaymentSpecificOutPutTransfer,
        WorldlineCardTransfer $cardTransfer
    ): void {
        foreach (explode(',', $tokens) as $tokenString) {
            if ($tokenString === '') {
                continue;
            }
            $tokenTransfer = $this->getTokenTransfer(
                $tokenString,
                $idCustomer,
                $threeDSecureDataTransfer,
                $cardPaymentSpecificOutPutTransfer->getPaymentProductId(),
                $cardPaymentSpecificOutPutTransfer,
                $cardTransfer,
            );

            $this->entityManager->saveWorldlineCreditCardToken($tokenTransfer);
        }
    }

    /**
     * @param int|null $idCustomer
     *
     * @return string
     */
    protected function getExistingTokens(?int $idCustomer): string
    {
        return $this->worldlineQueryContainer->getExistingTokens($idCustomer);
    }

    /**
     * @inheritDoc
     */
    public function updateWorldlineTokenByEvent(WebhookEventTransfer $webhookEventTransfer): void
    {
        $tokenDataTransfer = $webhookEventTransfer->getToken();

        $this->worldlineQueryContainer->getConnection()->beginTransaction();
        $this->saveWebhookEventLogs($webhookEventTransfer, null);

        $tokenEntities = $this->worldlineQueryContainer->findTokenByExternalTokenId($webhookEventTransfer->getToken()->getId());

        foreach ($tokenEntities as $tokenEntity) {
            /** @var \Orm\Zed\Worldline\Persistence\VsyWorldlineToken $tokenEntity */

            $tokenTransfer = $this->mapTokenEventDataToWorldlineTokenTransfer($tokenDataTransfer, $tokenEntity->getFkCustomer());
            $this->entityManager->saveWorldlineCreditCardToken($tokenTransfer);
        }
        $this->worldlineQueryContainer->getConnection()->commit();
    }

    /**
     * @param \Generated\Shared\Transfer\WebhookEventTransfer $webhookEventTransfer
     * @param int|null $idPaymentWorldline
     *
     * @return \Orm\Zed\Worldline\Persistence\VsyWorldlineRestLog
     */
    protected function saveWebhookEventLogs(
        WebhookEventTransfer $webhookEventTransfer,
        ?int $idPaymentWorldline
    ): VsyWorldlineRestLog {
        $restReceiveLogEntity = $this->getReceiveLogEntity($webhookEventTransfer);
        $restReceiveLogEntity->save();

        $idReceiveLog = $restReceiveLogEntity->getIdWorldlineRestReceiveLog();

        $worldlineRestLog = $this->getWorldlineRestLog($webhookEventTransfer, $idReceiveLog, $idPaymentWorldline);
        $worldlineRestLog->save();

        return $worldlineRestLog;
    }

    /**
     * @param string $tokenString
     * @param int|null $idCustomer
     * @param \Generated\Shared\Transfer\WorldlineThreeDSecureDataTransfer|null $threeDSecureDataTransfer
     * @param int|null $paymentProductId
     * @param \Generated\Shared\Transfer\WorldlineCardPaymentMethodSpecificOutputTransfer $cardPaymentSpecificOutPutTransfer
     * @param \Generated\Shared\Transfer\WorldlineCardTransfer $cardTransfer
     *
     * @return \Generated\Shared\Transfer\WorldlineCreditCardTokenTransfer
     */
    protected function getTokenTransfer(
        string $tokenString,
        ?int $idCustomer,
        ?WorldlineThreeDSecureDataTransfer $threeDSecureDataTransfer,
        ?int $paymentProductId,
        WorldlineCardPaymentMethodSpecificOutputTransfer $cardPaymentSpecificOutPutTransfer,
        WorldlineCardTransfer $cardTransfer
    ): WorldlineCreditCardTokenTransfer {
        $tokenTransfer = new WorldlineCreditCardTokenTransfer();
        $tokenTransfer->setToken($tokenString);
        $tokenTransfer->setFkCustomer($idCustomer);
        if ($threeDSecureDataTransfer) {
            $tokenTransfer->setFkInitialThreeDSecureResult($threeDSecureDataTransfer->getIdThreeDSecureResult());
        }
        if ($paymentProductId) {
            $tokenTransfer->setPaymentMethodKey(
                $this->worldlineConfig->getPaymentMethodKeyByPaymentProductId(
                    $paymentProductId,
                ),
            );
        }
        $tokenTransfer->setExpiryMonth($cardTransfer->getExpiryDate());
        if ($cardTransfer->getCardNumber()) {
            $tokenTransfer->setObfuscatedCardNumber($cardTransfer->getCardNumber());
        }
        if ($cardTransfer->getCardholderName()) {
            $tokenTransfer->setHolderName($cardTransfer->getCardholderName());
        }
        $tokenTransfer->setInitialSchemeTransactionId(
            $cardPaymentSpecificOutPutTransfer->getInitialSchemeTransactionId() ?: $cardPaymentSpecificOutPutTransfer->getSchemeTransactionId(),
        );

        return $tokenTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\WorldlineTokenEventDataTransfer $tokenDataTransfer
     * @param int|null $fkCustomer
     *
     * @return \Generated\Shared\Transfer\WorldlineCreditCardTokenTransfer
     */
    private function mapTokenEventDataToWorldlineTokenTransfer(
        WorldlineTokenEventDataTransfer $tokenDataTransfer,
        ?int $fkCustomer = null
    ): WorldlineCreditCardTokenTransfer {
        $cardTransfer = new WorldlineCardTransfer();

        if ($tokenDataTransfer->getCard() && $tokenDataTransfer->getCard()->getData() && $tokenDataTransfer->getCard()->getData()->getCardWithoutCvv()) {
            $cardTransfer->fromArray(
                $tokenDataTransfer->getCard()->getData()->getCardWithoutCvv()->toArray(),
                true,
            );
        }

        $worldlineCardPaymentSpecificOutputTransfer = new WorldlineCardPaymentMethodSpecificOutputTransfer();

        return $this->getTokenTransfer(
            $tokenDataTransfer->getId(),
            $fkCustomer,
            null,
            $tokenDataTransfer->getPaymentProductId(),
            $worldlineCardPaymentSpecificOutputTransfer,
            $cardTransfer,
        );
    }

    /**
     * @inheritDoc
     */
    public function setTokenExpiredByEvent(WebhookEventTransfer $webhookEventTransfer): void
    {
        $tokenDataTransfer = $webhookEventTransfer->getToken();
        $this->worldlineQueryContainer->getConnection()->beginTransaction();

        $this->saveWebhookEventLogs($webhookEventTransfer, null);

        $tokenEntities = $this->worldlineQueryContainer->findTokenByExternalTokenId($webhookEventTransfer->getToken()->getId());

        foreach ($tokenEntities as $tokenEntity) {
            /** @var \Orm\Zed\Worldline\Persistence\VsyWorldlineToken $tokenEntity */

            $tokenTransfer = $this->mapTokenEventDataToWorldlineTokenTransfer($tokenDataTransfer, $tokenEntity->getFkCustomer());
            $now = new DateTime('now', new DateTimeZone('UTC'));
            $tokenTransfer->setExpiredAt((string)$now->getTimestamp());
            $this->entityManager->saveWorldlineCreditCardToken($tokenTransfer);
        }
        $this->worldlineQueryContainer->getConnection()->commit();
    }

    /**
     * @inheritDoc
     */
    public function markTokenDeletedByEvent(WebhookEventTransfer $webhookEventTransfer): void
    {
        $this->worldlineQueryContainer->getConnection()->beginTransaction();
        $tokenDataTransfer = $webhookEventTransfer->getToken();

        $this->saveWebhookEventLogs($webhookEventTransfer, null);

        $tokenTransfer = $this->mapTokenEventDataToWorldlineTokenTransfer($tokenDataTransfer);

        $this->markTokensAsDeletedLoop($webhookEventTransfer->getToken()->getId(), $tokenTransfer);
        $this->worldlineQueryContainer->getConnection()->commit();
    }

    /**
     * @inheritDoc
     */
    public function markTokenDeletedById(WorldlineCreditCardTokenTransfer $tokenTransfer): void
    {
        $this->worldlineQueryContainer->getConnection()->beginTransaction();
        $this->markTokensAsDeletedLoop($tokenTransfer->getToken(), $tokenTransfer);
        $this->worldlineQueryContainer->getConnection()->commit();
    }

    /**
     * @param string $id
     * @param \Generated\Shared\Transfer\WorldlineCreditCardTokenTransfer $tokenTransfer
     *
     * @return void
     */
    protected function markTokensAsDeletedLoop(string $id, WorldlineCreditCardTokenTransfer $tokenTransfer): void
    {
        $tokenEntities = $this->worldlineQueryContainer->findTokenByExternalTokenId($id);

        foreach ($tokenEntities as $tokenEntity) {
            /** @var \Orm\Zed\Worldline\Persistence\VsyWorldlineToken $tokenEntity */

            $now = new DateTime('now', new DateTimeZone('UTC'));
            $tokenTransfer->setFkCustomer($tokenEntity->getFkCustomer());
            $tokenTransfer->setDeletedAt((string)$now->getTimestamp());
            $this->entityManager->saveWorldlineCreditCardToken($tokenTransfer);
        }
    }
}
