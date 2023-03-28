<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\Oms\Command\Mapper;

use Generated\Shared\Transfer\OrderTransfer;
use Spryker\Shared\Kernel\Transfer\TransferInterface;

interface WorldlineCommandMapperInterface
{
    /**
     * @param array $orderItems
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return \Spryker\Shared\Kernel\Transfer\TransferInterface
     */
    public function buildRequestTransfer(
        array $orderItems,
        OrderTransfer $orderTransfer
    ): TransferInterface;
}
