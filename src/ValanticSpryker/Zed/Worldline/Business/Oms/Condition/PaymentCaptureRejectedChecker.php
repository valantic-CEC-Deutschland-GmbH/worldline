<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\Oms\Condition;

use Orm\Zed\Sales\Persistence\SpySalesOrderItem;
use ValanticSpryker\Shared\Worldline\WorldlineConstants;
use ValanticSpryker\Zed\Worldline\Business\Reader\WorldlineReaderInterface;

class PaymentCaptureRejectedChecker implements PaymentCaptureRejectedCheckerInterface
{
    /**
     * @param \ValanticSpryker\Zed\Worldline\Business\Reader\WorldlineReaderInterface $worldlineReader
     */
    public function __construct(private WorldlineReaderInterface $worldlineReader)
    {
    }

    /**
     * @inheritDoc
     */
    public function isPaymentCaptureRejected(SpySalesOrderItem $orderItem): bool
    {
        $transactionStatusTransfer = $this->worldlineReader->getLatestPaymentWorldlineTransactionStatusLogByOrderItem($orderItem);

        return (
            ($transactionStatusTransfer->getStatus() === WorldlineConstants::STATUS_REJECTED)
            || ($transactionStatusTransfer->getStatus() === WorldlineConstants::STATUS_REJECTED_CAPTURE)
            || ($transactionStatusTransfer->getStatus() === WorldlineConstants::STATUS_CANCELLED)
        );
    }
}
