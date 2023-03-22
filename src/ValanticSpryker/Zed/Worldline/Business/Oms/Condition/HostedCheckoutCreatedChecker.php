<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\Oms\Condition;

use Orm\Zed\Sales\Persistence\SpySalesOrderItem;
use ValanticSpryker\Zed\Worldline\Business\Reader\WorldlineReaderInterface;

class HostedCheckoutCreatedChecker implements HostedCheckoutCreatedCheckerInterface
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
    public function isHostedCheckoutCreated(SpySalesOrderItem $orderItem): bool
    {
        $paymentWorldlineTransfer = $this->worldlineReader->getPaymentWorldlineByIdSalesOrder($orderItem->getFkSalesOrder());

        return (
            $paymentWorldlineTransfer->getPaymentHostedCheckout()
            &&
            $paymentWorldlineTransfer->getPaymentHostedCheckout()->getHostedCheckoutId()
        );
    }
}
