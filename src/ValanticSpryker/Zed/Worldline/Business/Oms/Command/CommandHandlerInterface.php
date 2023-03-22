<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\Oms\Command;

use Generated\Shared\Transfer\OrderTransfer;

interface CommandHandlerInterface
{
    /**
     * @param array $orderItems
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return void
     */
    public function handle(
        array $orderItems,
        OrderTransfer $orderTransfer
    ): void;
}
