<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Persistence;

use Generated\Shared\Transfer\PaymentWorldlineTransactionStatusTransfer;
use Generated\Shared\Transfer\PaymentWorldlineTransfer;
use Generated\Shared\Transfer\WorldlineApiLogTransfer;
use Generated\Shared\Transfer\WorldlineCreditCardTokenTransfer;
use Generated\Shared\Transfer\WorldlineThreeDSecureDataTransfer;
use Orm\Zed\Worldline\Persistence\Map\VsyWorldlineTokenTableMap;
use Orm\Zed\Worldline\Persistence\VsyPaymentWorldlineTransactionStatusLog;
use Orm\Zed\Worldline\Persistence\VsyWorldlineThreeDSecureResult;
use Orm\Zed\Worldline\Persistence\VsyWorldlineToken;
use Orm\Zed\Worldline\Persistence\VsyWorldlineTokenQuery;
use ValanticSpryker\Zed\Worldline\Persistence\Mapper\WorldlinePersistenceMapperInterface;
use Spryker\Zed\Kernel\Persistence\AbstractEntityManager;
use Spryker\Zed\PropelOrm\Business\Runtime\ActiveQuery\Criteria;

/**
 * @method \ValanticSpryker\Zed\Worldline\Persistence\WorldlinePersistenceFactory getFactory()
 */
class WorldlineEntityManager extends AbstractEntityManager implements WorldlineEntityManagerInterface
{
    /**
     * @param \Generated\Shared\Transfer\WorldlineApiLogTransfer $apiLogTransfer
     *
     * @return void
     */
    public function saveApiCallRequest(WorldlineApiLogTransfer $apiLogTransfer): void
    {
        $apiCallLogEntity = $this->getFactory()->createWorldlineApiCallLogQuery()
            ->filterByRequestId($apiLogTransfer->getRequestId())
            ->findOneOrCreate();

        $apiCallLogEntity->fromArray($apiLogTransfer->toArray());
        $apiCallLogEntity->setUrl($apiLogTransfer->getRequestUri());

        if ($apiCallLogEntity->isNew() || $apiCallLogEntity->isModified()) {
            $apiCallLogEntity->save();
        }
    }

    /**
     * @param \Generated\Shared\Transfer\WorldlineApiLogTransfer $apiLogTransfer
     *
     * @return void
     */
    public function saveApiCallResponse(WorldlineApiLogTransfer $apiLogTransfer): void
    {
        $apiCallLogEntity = $this->getFactory()->createWorldlineApiCallLogQuery()
            ->filterByRequestId($apiLogTransfer->getRequestId())
            ->findOneOrCreate();

        $apiCallLogEntity->setResponseBody($apiLogTransfer->getResponseBody());
        if ($apiCallLogEntity->isNew() || $apiCallLogEntity->isModified()) {
            $apiCallLogEntity->save();
        }
    }

    /**
     * @param \Generated\Shared\Transfer\WorldlineApiLogTransfer $apiLogTransfer
     *
     * @return void
     */
    public function saveApiCallException(WorldlineApiLogTransfer $apiLogTransfer): void
    {
        $apiCallLogEntity = $this->getFactory()->createWorldlineApiCallLogQuery()
            ->filterByRequestId($apiLogTransfer->getRequestId())
            ->findOneOrCreate();

        $apiCallLogEntity->setErrorCode($apiLogTransfer->getErrorCode());
        $apiCallLogEntity->setErrorMessage($apiLogTransfer->getErrorMessage());
        if ($apiCallLogEntity->isNew() || $apiCallLogEntity->isModified()) {
            $apiCallLogEntity->save();
        }
    }

    /**
     * @param \Generated\Shared\Transfer\PaymentWorldlineTransfer $paymentWorldlineTransfer
     *
     * @return \Generated\Shared\Transfer\PaymentWorldlineTransfer
     */
    public function updatePaymentWorldline(PaymentWorldlineTransfer $paymentWorldlineTransfer): PaymentWorldlineTransfer
    {
        $paymentWorldlineEntity = $this->getFactory()->createVsyPaymentWorldlineQuery()
            ->findOneByMerchantReference($paymentWorldlineTransfer->getMerchantReference());

        if (!$paymentWorldlineEntity) {
            $paymentWorldlineEntity = $this->getFactory()->createVsyPaymentWorldlineQuery()
                ->findOneByFkSalesOrder($paymentWorldlineTransfer->getFkSalesOrder());
        }

        if (!$paymentWorldlineEntity) {
            return $paymentWorldlineTransfer;
        }
        $paymentWorldlineEntity->fromArray($paymentWorldlineTransfer->modifiedToArray());

        $paymentWorldlineEntity->save();

        $paymentWorldlineHostedCheckoutEntity = $this->getFactory()->createVsyPaymentWorldlineHostedCheckoutQuery()
            ->filterByFkPaymentWorldline($paymentWorldlineEntity->getIdPaymentWorldline())
            ->findOneOrCreate();
        if ($paymentWorldlineTransfer->getPaymentHostedCheckout()) {
            $paymentWorldlineHostedCheckoutEntity->fromArray($paymentWorldlineTransfer->getPaymentHostedCheckout()->modifiedToArray());

            if ($paymentWorldlineHostedCheckoutEntity->isNew() || $paymentWorldlineHostedCheckoutEntity->isModified()) {
                $paymentWorldlineHostedCheckoutEntity->save();
            }
        }

        $paymentWorldlineTransfer = $this->getMapper()->mapEntityToPaymentWorldlineTransfer($paymentWorldlineEntity, $paymentWorldlineTransfer);

        return $paymentWorldlineTransfer;
    }

    /**
     * @return \ValanticSpryker\Zed\Worldline\Persistence\Mapper\WorldlinePersistenceMapperInterface
     */
    private function getMapper(): WorldlinePersistenceMapperInterface
    {
        return $this->getFactory()->createWorldlinePersistenceMapper();
    }

    /**
     * @inheritDoc
     */
    public function savePaymentWorldlineTransactionStatus(PaymentWorldlineTransactionStatusTransfer $paymentWorldlineTransactionStatusTransfer): PaymentWorldlineTransactionStatusTransfer
    {
        $vsyPaymentWorldlineTransactionStatusLogEntity = new VsyPaymentWorldlineTransactionStatusLog();

        $vsyPaymentWorldlineTransactionStatusLogEntity->fromArray($paymentWorldlineTransactionStatusTransfer->toArray());

        $vsyPaymentWorldlineTransactionStatusLogEntity->save();

        $paymentWorldlineTransactionStatusTransfer->fromArray($vsyPaymentWorldlineTransactionStatusLogEntity->toArray(), true);

        return $paymentWorldlineTransactionStatusTransfer;
    }

    /**
     * @inheritDoc
     */
    public function saveThreeDSecureData(WorldlineThreeDSecureDataTransfer $threeDSecureDataTransfer): WorldlineThreeDSecureDataTransfer
    {
        $vsyThreeDSecureResultsEntity = new VsyWorldlineThreeDSecureResult();

        $vsyThreeDSecureResultsEntity->fromArray($threeDSecureDataTransfer->toArray());
        $vsyThreeDSecureResultsEntity->setUtctimestamp($threeDSecureDataTransfer->getUtcTimestamp());

        $vsyThreeDSecureResultsEntity->save();
        $threeDSecureDataTransfer->setIdThreeDSecureResult($vsyThreeDSecureResultsEntity->getIdThreeDSecureResult());

        return $threeDSecureDataTransfer;
    }

    /**
     * @inheritDoc
     */
    public function saveWorldlineCreditCardToken(WorldlineCreditCardTokenTransfer $tokenTransfer): WorldlineCreditCardTokenTransfer
    {
        $vsyWorldlineTokenEntity = VsyWorldlineTokenQuery::create()
            ->filterByToken($tokenTransfer->getToken())
            ->filterByFkCustomer($tokenTransfer->getFkCustomer())
            ->findOneOrCreate();

        if (!$vsyWorldlineTokenEntity->isNew()) {
            $tokenTransfer = $this->setNonRequiredEmptyTokenValuesToOriginalValues($tokenTransfer, $vsyWorldlineTokenEntity);
        }

        $vsyWorldlineTokenEntity->fromArray($tokenTransfer->toArray());
        $cardNumber = $vsyWorldlineTokenEntity->getObfuscatedCardNumber();
        if (is_numeric(str_replace('-', '', $cardNumber))) {
            $obfuscatedCardNumber = $this->obfuscadeCardNumber($cardNumber);
            $vsyWorldlineTokenEntity->setObfuscatedCardNumber($obfuscatedCardNumber);
        }

        $vsyWorldlineTokenEntity->save();

        $tokenTransfer->fromArray($vsyWorldlineTokenEntity->toArray(), true);

        return $tokenTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\WorldlineCreditCardTokenTransfer $tokenTransfer
     * @param \Orm\Zed\Worldline\Persistence\VsyWorldlineToken $vsyWorldlineTokenEntity
     *
     * @return \Generated\Shared\Transfer\WorldlineCreditCardTokenTransfer
     */
    private function setNonRequiredEmptyTokenValuesToOriginalValues(
        WorldlineCreditCardTokenTransfer $tokenTransfer,
        VsyWorldlineToken $vsyWorldlineTokenEntity
    ): WorldlineCreditCardTokenTransfer {
        $tokenTransfer->setIdToken($vsyWorldlineTokenEntity->getIdToken());
        if (!$tokenTransfer->getInitialSchemeTransactionId()) {
            $tokenTransfer->setInitialSchemeTransactionId($vsyWorldlineTokenEntity->getInitialSchemeTransactionId());
        }
        if (!$tokenTransfer->getFkInitialThreeDSecureResult()) {
            $tokenTransfer->setFkInitialThreeDSecureResult($vsyWorldlineTokenEntity->getFkInitialThreeDSecureResult());
        }
        if (!$tokenTransfer->getObfuscatedCardNumber()) {
            $tokenTransfer->setObfuscatedCardNumber($vsyWorldlineTokenEntity->getObfuscatedCardNumber());
        }
        if (!$tokenTransfer->getHolderName()) {
            $tokenTransfer->setHolderName($vsyWorldlineTokenEntity->getHolderName());
        }
        if (!$tokenTransfer->getExpiryMonth()) {
            $tokenTransfer->setExpiryMonth($vsyWorldlineTokenEntity->getExpiryMonth());
        }
        if (!$tokenTransfer->getPaymentMethodKey()) {
            $tokenTransfer->setPaymentMethodKey($vsyWorldlineTokenEntity->getPaymentMethodKey());
        }

        if (!$tokenTransfer->getExpiredAt()) {
            $tokenTransfer->setExpiredAt($vsyWorldlineTokenEntity->getExpiredAt() ? (string)$vsyWorldlineTokenEntity->getExpiredAt()->getTimestamp() : null);
        }
        if (!$tokenTransfer->getDeletedAt()) {
            $tokenTransfer->setDeletedAt($vsyWorldlineTokenEntity->getDeletedAt() ? (string)$vsyWorldlineTokenEntity->getDeletedAt()->getTimestamp() : null);
        }

        return $tokenTransfer;
    }

    /**
     * @param string $cardNumber
     *
     * @return string
     */
    private function obfuscadeCardNumber(string $cardNumber): string
    {
        // Clean out
        $cc = str_replace(['-', ' '], '', $cardNumber);
        $ccLength = strlen($cc);

        if ($ccLength <= 4) {
            return $cardNumber;
        }

        $maskTo = $ccLength - 4;
        $maskChar = '*';

        return str_repeat($maskChar, $maskTo) . substr($cc, $maskTo);
    }

    /**
     * @inheritDoc
     */
    public function deleteWorldlineTokensMarkedAsDeleted(int $getLimitOfDeletedTokensToRemove): int
    {
        $tokensToDelete = $this->getFactory()
            ->createVsyWorldlineTokenQuery()
            ->filterByDeletedAt(comparison: Criteria::ISNOTNULL)
            ->select(VsyWorldlineTokenTableMap::COL_TOKEN)
            ->distinct()
            ->limit($getLimitOfDeletedTokensToRemove)
            ->find();

        return $this->getFactory()
            ->createVsyWorldlineTokenQuery()
            ->filterByToken($tokensToDelete->toArray(), Criteria::IN)
            ->delete();
    }
}
