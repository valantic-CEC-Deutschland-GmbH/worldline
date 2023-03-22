<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Persistence;

use Generated\Shared\Transfer\WebhookEventTransfer;
use Orm\Zed\Worldline\Persistence\VsyPaymentWorldline;
use Orm\Zed\Worldline\Persistence\VsyPaymentWorldlineQuery;
use Orm\Zed\Worldline\Persistence\VsyPaymentWorldlineTransactionStatusLogQuery;
use Orm\Zed\Worldline\Persistence\VsyWorldlineTokenQuery;
use Spryker\Zed\Kernel\Persistence\AbstractQueryContainer;
use Spryker\Zed\PropelOrm\Business\Runtime\ActiveQuery\Criteria;

/**
 * @method \ValanticSpryker\Zed\Worldline\Persistence\WorldlinePersistenceFactory getFactory()
 */
class WorldlineQueryContainer extends AbstractQueryContainer implements WorldlineQueryContainerInterface
{
    /**
     * @inheritDoc
     */
    public function isEventAlreadyHandled(WebhookEventTransfer $webhookEventTransfer): bool
    {
        $receiveLogEntity = $this->getFactory()->createVsyWorldlineRestReceiveLogQuery()->findOneByEventId($webhookEventTransfer->getId());

        return $receiveLogEntity !== null;
    }

    /**
     * @inheritDoc
     */
    public function findPaymentWorldlineByPaymentId(string $paymentId): ?VsyPaymentWorldline
    {
        return $this->getFactory()->createVsyPaymentWorldlineQuery()->findOneByPaymentId($paymentId);
    }

    /**
     * @inheritDoc
     */
    public function queryTransactionStatusLog(): VsyPaymentWorldlineTransactionStatusLogQuery
    {
        return $this->getFactory()->createVsyPaymentWorldlineTransactionLogQuery();
    }

    /**
     * @inheritDoc
     */
    public function queryPaymentWorldline(): VsyPaymentWorldlineQuery
    {
        return $this->getFactory()->createVsyPaymentWorldlineQuery();
    }

    /**
     * @param string $merchantReference
     *
     * @return \Orm\Zed\Worldline\Persistence\VsyPaymentWorldline|null
     */
    public function findPaymentWorldlineByMerchantReference(string $merchantReference): ?VsyPaymentWorldline
    {
        return $this->getFactory()->createVsyPaymentWorldlineQuery()->findOneByMerchantReference($merchantReference);
    }

    /**
     * @inheritDoc
     */
    public function findTokenByExternalTokenId(?string $externalTokenId): mixed
    {
        return $this->getFactory()->createVsyWorldlineTokenQuery()->filterByToken($externalTokenId)->find();
    }

    /**
     * @inheritDoc
     */
    public function getExistingTokens(?int $idCustomer): string
    {
        $preExistingTokens = $this->getFactory()->createVsyWorldlineTokenQuery()->filterByFkCustomer($idCustomer)->select(['token'])->find();

        return implode(',', $preExistingTokens->toArray());
    }

    /**
     * @inheritDoc
     */
    public function findAvailableTokensByFkCustomer(int $idCustomer): mixed
    {
        return $this->getFactory()->createVsyWorldlineTokenQuery()
            ->filterByDeletedAt(comparison: Criteria::ISNULL)
            ->filterByExpiredAt(comparison: Criteria::ISNULL)
            ->filterByFkCustomer($idCustomer)
            ->find();
    }

    /**
     * @inheritDoc
     */
    public function findTokenByExternalTokenIdAndFkCustomer(?string $externalTokenId, int $fkCustomer): mixed
    {
        return $this->getFactory()->createVsyWorldlineTokenQuery()
            ->filterByFkCustomer($fkCustomer)
            ->filterByToken($externalTokenId)->find();
    }

    /**
     * @inheritDoc
     */
    public function queryTokens(): VsyWorldlineTokenQuery
    {
        return $this->getFactory()->createVsyWorldlineTokenQuery();
    }
}
