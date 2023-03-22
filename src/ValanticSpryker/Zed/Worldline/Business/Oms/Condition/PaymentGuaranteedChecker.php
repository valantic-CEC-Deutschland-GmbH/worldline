<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\Oms\Condition;

use Orm\Zed\Sales\Persistence\SpySalesOrderItem;
use ValanticSpryker\Shared\Worldline\WorldlineConstants;
use ValanticSpryker\Zed\Worldline\Business\Reader\WorldlineReaderInterface;

class PaymentGuaranteedChecker implements PaymentGuaranteedCheckerInterface
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
    public function isPaymentGuaranteed(SpySalesOrderItem $orderItem): bool
    {
        $paymentWorldlineTransacionStatusTransfer = $this->worldlineReader->getLatestPaymentWorldlineTransactionStatusLogByOrderItem($orderItem);

        $paymentTransferStatus = $paymentWorldlineTransacionStatusTransfer->getStatus();

        return ($paymentTransferStatus === WorldlineConstants::STATUS_PAYMENT_PENDING_APPROVAL) && $paymentWorldlineTransacionStatusTransfer->getAuthorized();
    }
}
