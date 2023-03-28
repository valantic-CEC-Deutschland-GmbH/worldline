<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\Oms\Condition;

use Orm\Zed\Sales\Persistence\SpySalesOrderItem;
use ValanticSpryker\Zed\Worldline\Business\Reader\WorldlineReaderInterface;

class HostedCheckoutPaymentWasCreatedChecker implements HostedCheckoutPaymentWasCreatedCheckerInterface
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
    public function isHostedCheckoutPaymentCreated(SpySalesOrderItem $orderItem): bool
    {
        return ($this->worldlineReader->getPaymentWorldlineByIdSalesOrder($orderItem->getFkSalesOrder())->getPaymentId() !== null);
    }
}
