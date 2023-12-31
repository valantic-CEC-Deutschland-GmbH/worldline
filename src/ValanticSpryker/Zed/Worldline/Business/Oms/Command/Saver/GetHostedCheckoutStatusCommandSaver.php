<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\Oms\Command\Saver;

use Generated\Shared\Transfer\OrderTransfer;
use Generated\Shared\Transfer\WorldlineGetHostedCheckoutStatusResponseTransfer;
use Spryker\Shared\Kernel\Transfer\TransferInterface;
use ValanticSpryker\Zed\Worldline\Business\Writer\WorldlineWriterInterface;

class GetHostedCheckoutStatusCommandSaver implements WorldlineCommandSaverInterface
{
    /**
     * @param \ValanticSpryker\Zed\Worldline\Business\Writer\WorldlineWriterInterface $worldlineWriter
     */
    public function __construct(private WorldlineWriterInterface $worldlineWriter)
    {
    }

    /**
     * @param \Spryker\Shared\Kernel\Transfer\TransferInterface $responseTransfer
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return void
     */
    public function save(TransferInterface $responseTransfer, OrderTransfer $orderTransfer): void
    {
        if (!$responseTransfer instanceof WorldlineGetHostedCheckoutStatusResponseTransfer) {
            return;
        }

        $this->worldlineWriter->saveGetHostedCheckoutStatusResponse($responseTransfer, $orderTransfer);
    }
}
