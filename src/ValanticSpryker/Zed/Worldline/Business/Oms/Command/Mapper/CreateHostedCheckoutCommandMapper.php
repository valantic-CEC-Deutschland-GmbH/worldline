<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\Oms\Command\Mapper;

use Generated\Shared\Transfer\OrderTransfer;
use Generated\Shared\Transfer\WorldlineCreateHostedCheckoutTransfer;
use ValanticSpryker\Zed\Worldline\Business\Reader\WorldlineReaderInterface;
use Spryker\Shared\Kernel\Transfer\TransferInterface;

class CreateHostedCheckoutCommandMapper implements WorldlineCommandMapperInterface
{
    /**
     * @param \ValanticSpryker\Zed\Worldline\Business\Reader\WorldlineReaderInterface $worldlineReader
     * @param array<\ValanticSpryker\Zed\Worldline\Business\Oms\Command\Mapper\WorldlineCreateHostedCheckoutPartMapperInterface> $createHostedCheckoutPartMappers
     */
    public function __construct(private WorldlineReaderInterface $worldlineReader, private array $createHostedCheckoutPartMappers)
    {
    }

    /**
     * @param array $orderItems
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return \Spryker\Shared\Kernel\Transfer\TransferInterface
     */
    public function buildRequestTransfer(array $orderItems, OrderTransfer $orderTransfer): TransferInterface
    {
        $worldlineCreateHostedCheckoutTransfer = new WorldlineCreateHostedCheckoutTransfer();

        $worldlinePaymentTransfer = $this->worldlineReader->getPaymentWorldlineByIdSalesOrder($orderTransfer->getIdSalesOrder());

        foreach ($this->createHostedCheckoutPartMappers as $partMapper) {
            $worldlineCreateHostedCheckoutTransfer = $partMapper->map($worldlineCreateHostedCheckoutTransfer, $orderTransfer, $worldlinePaymentTransfer);
        }

        return $worldlineCreateHostedCheckoutTransfer;
    }
}
