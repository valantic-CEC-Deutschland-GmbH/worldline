<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\Order;

use Generated\Shared\Transfer\OrderTransfer;

interface OrderHydratorInterface
{
    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return \Generated\Shared\Transfer\OrderTransfer
     */
    public function hydrateOrderTransfer(OrderTransfer $orderTransfer): OrderTransfer;
}
