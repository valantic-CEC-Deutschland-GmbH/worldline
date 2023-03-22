<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Persistence;

use Generated\Shared\Transfer\WebhookEventTransfer;
use Orm\Zed\Worldline\Persistence\VsyPaymentWorldline;
use Orm\Zed\Worldline\Persistence\VsyPaymentWorldlineQuery;
use Orm\Zed\Worldline\Persistence\VsyPaymentWorldlineTransactionStatusLogQuery;
use Orm\Zed\Worldline\Persistence\VsyWorldlineTokenQuery;
use Spryker\Zed\Kernel\Persistence\QueryContainer\QueryContainerInterface;

interface WorldlineQueryContainerInterface extends QueryContainerInterface
{
    /**
     * @param \Generated\Shared\Transfer\WebhookEventTransfer $webhookEventTransfer
     *
     * @return bool
     */
    public function isEventAlreadyHandled(WebhookEventTransfer $webhookEventTransfer): bool;

    /**
     * @param string $paymentId
     *
     * @return \Orm\Zed\Worldline\Persistence\VsyPaymentWorldline|null
     */
    public function findPaymentWorldlineByPaymentId(string $paymentId): ?VsyPaymentWorldline;

    /**
     * @return \Orm\Zed\Worldline\Persistence\VsyPaymentWorldlineTransactionStatusLogQuery
     */
    public function queryTransactionStatusLog(): VsyPaymentWorldlineTransactionStatusLogQuery;

    /**
     * @return \Orm\Zed\Worldline\Persistence\VsyPaymentWorldlineQuery
     */
    public function queryPaymentWorldline(): VsyPaymentWorldlineQuery;

    /**
     * @param string $merchantReference
     *
     * @return \Orm\Zed\Worldline\Persistence\VsyPaymentWorldline|null
     */
    public function findPaymentWorldlineByMerchantReference(string $merchantReference): ?VsyPaymentWorldline;

    /**
     * @param string|null $externalTokenId
     *
     * @return mixed
     */
    public function findTokenByExternalTokenId(?string $externalTokenId): mixed;

    /**
     * @param int|null $idCustomer
     *
     * @return string
     */
    public function getExistingTokens(?int $idCustomer): string;

    /**
     * @param int $idCustomer
     *
     * @return mixed
     */
    public function findAvailableTokensByFkCustomer(int $idCustomer): mixed;

    /**
     * @return \Orm\Zed\Worldline\Persistence\VsyWorldlineTokenQuery
     */
    public function queryTokens(): VsyWorldlineTokenQuery;

    /**
     * @param string|null $externalTokenId
     * @param int $fkCustomer
     *
     * @return mixed
     */
    public function findTokenByExternalTokenIdAndFkCustomer(?string $externalTokenId, int $fkCustomer): mixed;
}
