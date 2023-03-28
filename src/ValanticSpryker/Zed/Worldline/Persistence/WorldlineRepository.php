<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Persistence;

use Exception;
use Generated\Shared\Transfer\PaymentWorldlineTransactionStatusTransfer;
use Generated\Shared\Transfer\PaymentWorldlineTransfer;
use Generated\Shared\Transfer\WorldlineCreditCardTokenTransfer;
use Generated\Shared\Transfer\WorldlinePaymentHostedCheckoutTransfer;
use Generated\Shared\Transfer\WorldlinePaymentTokenErrorItemTransfer;
use Generated\Shared\Transfer\WorldlinePaymentTokensResponseTransfer;
use Spryker\Zed\Kernel\Persistence\AbstractRepository;
use Spryker\Zed\PropelOrm\Business\Runtime\ActiveQuery\Criteria;

/**
 * @method \ValanticSpryker\Zed\Worldline\Persistence\WorldlinePersistenceFactory getFactory()
 */
class WorldlineRepository extends AbstractRepository implements WorldlineRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function getPaymentWorldlineTransferByFkSalesOrder(?int $fkSalesOrder): PaymentWorldlineTransfer
    {
        $query = $this->getFactory()->createVsyPaymentWorldlineQuery();

        $paymentWorldlineEntity = $query->findOneByFkSalesOrder($fkSalesOrder);

        $paymentWorldlineTransfer = new PaymentWorldlineTransfer();

        if (!$paymentWorldlineEntity) {
            return $paymentWorldlineTransfer;
        }

        $paymentWorldlineTransfer->fromArray($paymentWorldlineEntity->toArray(), true);

        $worldlinePaymentDetailsTransfer = new WorldlinePaymentHostedCheckoutTransfer();
        /** @var \Orm\Zed\Worldline\Persistence\Base\VsyPaymentWorldlineHostedCheckout $vsyPaymentWorldlineHostedCheckout */
        $vsyPaymentWorldlineHostedCheckout = $paymentWorldlineEntity->getVsyPaymentWorldlineHostedCheckout();
        $worldlinePaymentDetailsTransfer->fromArray($vsyPaymentWorldlineHostedCheckout->toArray(), true);

        $paymentWorldlineTransfer->setPaymentHostedCheckout($worldlinePaymentDetailsTransfer);

        return $paymentWorldlineTransfer;
    }

    /**
     * @inheritDoc
     */
    public function getLatestPaymentWorldlineTransactionStatusLogByFkPaymentWorldline(int $idPaymentWorldline): PaymentWorldlineTransactionStatusTransfer
    {
        $paymentWorldlineTransactionStatusTransfer = new PaymentWorldlineTransactionStatusTransfer();

        $query = $this->getFactory()->createVsyPaymentWorldlineTransactionLogQuery();

        $paymentWorldlineTransactionStatusLogEntity = $query->filterByFkPaymentWorldline($idPaymentWorldline)->filterByTransactionType('payment')->orderByStatusCodeChangeDateTime(Criteria::DESC)->find()->getFirst();

        if (!$paymentWorldlineTransactionStatusLogEntity) {
            return $paymentWorldlineTransactionStatusTransfer;
        }

        $statusChangeDateTime = $paymentWorldlineTransactionStatusLogEntity->getStatusCodeChangeDateTime();

        $entities = $query->findByStatusCodeChangeDateTime($statusChangeDateTime);
        if ($entities->count() > 1) {
            /** @var \Orm\Zed\Worldline\Persistence\Base\VsyPaymentWorldlineTransactionStatusLog $latestEvent */
            $latestEvent = null;
            foreach ($entities as $entity) {
                if ($entity->getFkWorldlineApiLog()) {
                    $paymentWorldlineTransactionStatusLogEntity = $entity;
                    $latestEvent = null;

                    break;
                }
                if (!$latestEvent || ($latestEvent->getFkWorldlineRestLog() && $latestEvent->getVsyWorldlineRestLog()->getEventCreationDate() < $entity->getVsyWorldlineRestLog()->getEventCreationDate())) {
                    $latestEvent = $entity;
                }
            }
            if ($latestEvent) {
                $paymentWorldlineTransactionStatusLogEntity = $latestEvent;
            }
        }

        $paymentWorldlineTransactionStatusTransfer->fromArray($paymentWorldlineTransactionStatusLogEntity->toArray(), true);

        return $paymentWorldlineTransactionStatusTransfer;
    }

    /**
     * @inheritDoc
     */
    public function getPaymentTokensByCustomerId(int $customerId): WorldlinePaymentTokensResponseTransfer
    {
        $worldlinePaymentTokensResponseTransfer = new WorldlinePaymentTokensResponseTransfer();

        try {
            /** @var array<\Orm\Zed\Worldline\Persistence\VsyWorldlineToken> $vsyWorldlineTokenList */
            $vsyWorldlineTokenList = $this->getFactory()->createVsyWorldlineTokenQuery()->filterByFkCustomer($customerId)->find();

            foreach ($vsyWorldlineTokenList as $vsyWorldlineToken) {
                $worldlineCreditCardTokenTransfer = (new WorldlineCreditCardTokenTransfer())->fromArray(
                    $vsyWorldlineToken->toArray(),
                    true,
                );
                $worldlinePaymentTokensResponseTransfer->addToken($worldlineCreditCardTokenTransfer);
            }
            $worldlinePaymentTokensResponseTransfer->setIsSuccessful(true);
        } catch (Exception $exception) {
            $worldlinePaymentTokensResponseTransfer->setIsSuccessful(false);
            $worldlinePaymentTokensResponseTransfer->addError((new WorldlinePaymentTokenErrorItemTransfer())->setMessage($exception->getMessage()));
        }

        return $worldlinePaymentTokensResponseTransfer;
    }

    /**
     * @inheritDoc
     */
    public function findPaymentTokenById(int $idToken): WorldlineCreditCardTokenTransfer
    {
        $tokenEntity = $this->getFactory()->createVsyWorldlineTokenQuery()->findOneByIdToken($idToken);

        $tokenTransfer = new WorldlineCreditCardTokenTransfer();
        if ($tokenEntity) {
            $tokenTransfer->fromArray($tokenEntity->toArray(), true);
        }

        return $tokenTransfer;
    }
}
