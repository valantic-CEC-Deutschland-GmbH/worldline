<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Persistence;

use Generated\Shared\Transfer\PaymentWorldlineTransactionStatusTransfer;
use Generated\Shared\Transfer\PaymentWorldlineTransfer;
use Generated\Shared\Transfer\WorldlineCreditCardTokenTransfer;
use Generated\Shared\Transfer\WorldlinePaymentTokensResponseTransfer;

interface WorldlineRepositoryInterface
{
    /**
     * @param int|null $fkSalesOrder
     *
     * @return \Generated\Shared\Transfer\PaymentWorldlineTransfer
     */
    public function getPaymentWorldlineTransferByFkSalesOrder(?int $fkSalesOrder): PaymentWorldlineTransfer;

    /**
     * @param int $idPaymentWorldline
     *
     * @return \Generated\Shared\Transfer\PaymentWorldlineTransactionStatusTransfer
     */
    public function getLatestPaymentWorldlineTransactionStatusLogByFkPaymentWorldline(int $idPaymentWorldline): PaymentWorldlineTransactionStatusTransfer;

    /**
     * @param int $customerId
     *
     * @return \Generated\Shared\Transfer\WorldlinePaymentTokensResponseTransfer
     */
    public function getPaymentTokensByCustomerId(int $customerId): WorldlinePaymentTokensResponseTransfer;

    /**
     * @param int $idToken
     *
     * @return \Generated\Shared\Transfer\WorldlineCreditCardTokenTransfer
     */
    public function findPaymentTokenById(int $idToken): WorldlineCreditCardTokenTransfer;
}
