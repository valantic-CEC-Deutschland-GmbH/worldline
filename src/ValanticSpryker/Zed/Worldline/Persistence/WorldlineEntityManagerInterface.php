<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Persistence;

use Generated\Shared\Transfer\PaymentWorldlineTransactionStatusTransfer;
use Generated\Shared\Transfer\PaymentWorldlineTransfer;
use Generated\Shared\Transfer\WorldlineApiLogTransfer;
use Generated\Shared\Transfer\WorldlineCreditCardTokenTransfer;
use Generated\Shared\Transfer\WorldlineThreeDSecureDataTransfer;

interface WorldlineEntityManagerInterface
{
    /**
     * @param \Generated\Shared\Transfer\WorldlineApiLogTransfer $apiLogTransfer
     *
     * @return void
     */
    public function saveApiCallRequest(WorldlineApiLogTransfer $apiLogTransfer): void;

    /**
     * @param \Generated\Shared\Transfer\WorldlineApiLogTransfer $apiLogTransfer
     *
     * @return void
     */
    public function saveApiCallResponse(WorldlineApiLogTransfer $apiLogTransfer): void;

    /**
     * @param \Generated\Shared\Transfer\WorldlineApiLogTransfer $apiLogTransfer
     *
     * @return void
     */
    public function saveApiCallException(WorldlineApiLogTransfer $apiLogTransfer): void;

    /**
     * @param \Generated\Shared\Transfer\PaymentWorldlineTransfer $paymentWorldlineTransfer
     *
     * @return \Generated\Shared\Transfer\PaymentWorldlineTransfer
     */
    public function updatePaymentWorldline(PaymentWorldlineTransfer $paymentWorldlineTransfer): PaymentWorldlineTransfer;

    /**
     * @inheritDoc
     */
    public function savePaymentWorldlineTransactionStatus(PaymentWorldlineTransactionStatusTransfer $paymentWorldlineTransactionStatusTransfer): PaymentWorldlineTransactionStatusTransfer;

    /**
     * @param \Generated\Shared\Transfer\WorldlineThreeDSecureDataTransfer $threeDSecureDataTransfer
     *
     * @return \Generated\Shared\Transfer\WorldlineThreeDSecureDataTransfer
     */
    public function saveThreeDSecureData(WorldlineThreeDSecureDataTransfer $threeDSecureDataTransfer): WorldlineThreeDSecureDataTransfer;

    /**
     * @param \Generated\Shared\Transfer\WorldlineCreditCardTokenTransfer $tokenTransfer
     *
     * @return \Generated\Shared\Transfer\WorldlineCreditCardTokenTransfer
     */
    public function saveWorldlineCreditCardToken(WorldlineCreditCardTokenTransfer $tokenTransfer): WorldlineCreditCardTokenTransfer;

    /**
     * @param int $getLimitOfDeletedTokensToRemove
     *
     * @return int
     */
    public function deleteWorldlineTokensMarkedAsDeleted(int $getLimitOfDeletedTokensToRemove): int;
}
