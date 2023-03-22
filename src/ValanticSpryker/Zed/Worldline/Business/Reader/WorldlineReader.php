<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\Reader;

use Generated\Shared\Transfer\PaymentWorldlineTransactionStatusTransfer;
use Generated\Shared\Transfer\PaymentWorldlineTransfer;
use Orm\Zed\Sales\Persistence\SpySalesOrderItem;
use ValanticSpryker\Zed\Worldline\Persistence\WorldlineRepositoryInterface;

class WorldlineReader implements WorldlineReaderInterface
{
    /**
     * @param \ValanticSpryker\Zed\Worldline\Persistence\WorldlineRepositoryInterface $worldlineRepository
     */
    public function __construct(private WorldlineRepositoryInterface $worldlineRepository)
    {
    }

    /**
     * @inheritDoc
     */
    public function getPaymentWorldlineByIdSalesOrder(int $idSalesOrder): PaymentWorldlineTransfer
    {
        return $this->worldlineRepository->getPaymentWorldlineTransferByFkSalesOrder($idSalesOrder);
    }

    /**
     * @inheritDoc
     */
    public function getLatestPaymentWorldlineTransactionStatusLogByFkPaymentWorldline(
        int $idPaymentWorldline
    ): PaymentWorldlineTransactionStatusTransfer {
        return $this->worldlineRepository->getLatestPaymentWorldlineTransactionStatusLogByFkPaymentWorldline($idPaymentWorldline);
    }

    /**
     * @param \Orm\Zed\Sales\Persistence\SpySalesOrderItem $orderItem
     *
     * @return \Generated\Shared\Transfer\PaymentWorldlineTransactionStatusTransfer
     */
    public function getLatestPaymentWorldlineTransactionStatusLogByOrderItem(SpySalesOrderItem $orderItem): PaymentWorldlineTransactionStatusTransfer
    {
        $paymentTransfer = $this->worldlineRepository->getPaymentWorldlineTransferByFkSalesOrder($orderItem->getFkSalesOrder());
        $idPaymentWorldline = $paymentTransfer->getIdPaymentWorldline();

        return $this->worldlineRepository->getLatestPaymentWorldlineTransactionStatusLogByFkPaymentWorldline($idPaymentWorldline);
    }
}
