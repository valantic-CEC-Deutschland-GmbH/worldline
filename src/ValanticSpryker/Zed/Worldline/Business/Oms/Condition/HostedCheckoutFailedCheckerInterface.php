<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\Oms\Condition;

use Orm\Zed\Sales\Persistence\SpySalesOrderItem;

interface HostedCheckoutFailedCheckerInterface
{
    /**
     * @param \Orm\Zed\Sales\Persistence\SpySalesOrderItem $orderItem
     *
     * @return bool
     */
    public function isHostedCheckoutFailed(SpySalesOrderItem $orderItem): bool;
}
