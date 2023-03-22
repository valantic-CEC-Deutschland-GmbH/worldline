<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\Oms\Condition;

use Orm\Zed\Sales\Persistence\SpySalesOrderItem;
use ValanticSpryker\Shared\Worldline\WorldlineConstants;
use ValanticSpryker\Zed\Worldline\Business\Reader\WorldlineReaderInterface;

class PaymentRejectedChecker implements PaymentRejectedCheckerInterface
{
    /**
     * @var int
     */
    private const CODE_TIMEOUT_OCCURRED = 150;

    /**
     * @param \ValanticSpryker\Zed\Worldline\Business\Reader\WorldlineReaderInterface $worldlineReader
     */
    public function __construct(private WorldlineReaderInterface $worldlineReader)
    {
    }

    /**
     * @param \Orm\Zed\Sales\Persistence\SpySalesOrderItem $orderItem
     *
     * @return bool
     */
    public function isPaymentRejected(SpySalesOrderItem $orderItem): bool
    {
        $transactionStatusLogTransfer = $this->worldlineReader->getLatestPaymentWorldlineTransactionStatusLogByOrderItem($orderItem);

        return (
        (($transactionStatusLogTransfer->getStatusCategory() === WorldlineConstants::STATUS_CATEGORY_UNSUCCESSFUL)
            && ($transactionStatusLogTransfer->getStatus() !== WorldlineConstants::STATUS_CANCELLED))
            || ($transactionStatusLogTransfer->getStatusCode() === self::CODE_TIMEOUT_OCCURRED)
        );
    }
}
