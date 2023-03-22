<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\Oms\Command\Saver;

use Generated\Shared\Transfer\OrderTransfer;
use Spryker\Shared\Kernel\Transfer\TransferInterface;

class ApprovePaymentCommandSaver implements WorldlineCommandSaverInterface
{
    /**
     * @param \Spryker\Shared\Kernel\Transfer\TransferInterface $responseTransfer
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return void
     */
    public function save(TransferInterface $responseTransfer, OrderTransfer $orderTransfer): void
    {
        // everything should be donn in the Logger
    }
}
