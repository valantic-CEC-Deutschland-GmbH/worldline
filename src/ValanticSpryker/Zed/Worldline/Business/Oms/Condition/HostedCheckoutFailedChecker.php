<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\Oms\Condition;

use Orm\Zed\Sales\Persistence\SpySalesOrderItem;
use ValanticSpryker\Shared\Worldline\WorldlineConstants;
use ValanticSpryker\Zed\Worldline\Business\Reader\WorldlineReaderInterface;

class HostedCheckoutFailedChecker implements HostedCheckoutFailedCheckerInterface
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
    public function isHostedCheckoutFailed(SpySalesOrderItem $orderItem): bool
    {
        $paymentWorldlineTransactionStatusTransfer = $this->worldlineReader->getLatestPaymentWorldlineTransactionStatusLogByOrderItem($orderItem);

        return (
            $paymentWorldlineTransactionStatusTransfer->getStatus()
            ===
            WorldlineConstants::STATUS_HOSTED_CHECKOUT_FAILED
        );
    }
}
