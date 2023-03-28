<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\Reader;

use Generated\Shared\Transfer\PaymentWorldlineTransactionStatusTransfer;
use Generated\Shared\Transfer\PaymentWorldlineTransfer;
use Orm\Zed\Sales\Persistence\SpySalesOrderItem;

interface WorldlineReaderInterface
{
    /**
     * @param int $idSalesOrder
     *
     * @return \Generated\Shared\Transfer\PaymentWorldlineTransfer
     */
    public function getPaymentWorldlineByIdSalesOrder(int $idSalesOrder): PaymentWorldlineTransfer;

    /**
     * @param int $idPaymentWorldline
     *
     * @return \Generated\Shared\Transfer\PaymentWorldlineTransactionStatusTransfer
     */
    public function getLatestPaymentWorldlineTransactionStatusLogByFkPaymentWorldline(int $idPaymentWorldline): PaymentWorldlineTransactionStatusTransfer;

    /**
     * @param \Orm\Zed\Sales\Persistence\SpySalesOrderItem $orderItem
     *
     * @return \Generated\Shared\Transfer\PaymentWorldlineTransactionStatusTransfer
     */
    public function getLatestPaymentWorldlineTransactionStatusLogByOrderItem(SpySalesOrderItem $orderItem): PaymentWorldlineTransactionStatusTransfer;
}
