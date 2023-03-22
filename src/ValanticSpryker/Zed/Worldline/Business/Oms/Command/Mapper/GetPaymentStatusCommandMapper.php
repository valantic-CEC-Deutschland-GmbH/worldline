<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\Oms\Command\Mapper;

use Generated\Shared\Transfer\OrderTransfer;
use Generated\Shared\Transfer\WorldlineGetPaymentRequestTransfer;
use Spryker\Shared\Kernel\Transfer\TransferInterface;
use ValanticSpryker\Zed\Worldline\Business\Reader\WorldlineReaderInterface;

class GetPaymentStatusCommandMapper implements WorldlineCommandMapperInterface
{
    /**
     * @param \ValanticSpryker\Zed\Worldline\Business\Reader\WorldlineReaderInterface $worldlineReader
     */
    public function __construct(private WorldlineReaderInterface $worldlineReader)
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
        $getPaymentRequestTransfer = new WorldlineGetPaymentRequestTransfer();

        $worldlinePaymentTransfer = $this->worldlineReader->getPaymentWorldlineByIdSalesOrder($orderTransfer->getIdSalesOrder());

        if (!$worldlinePaymentTransfer->getPaymentId()) {
            return $getPaymentRequestTransfer;
        }
        $getPaymentRequestTransfer->setFkPaymentWorldline($worldlinePaymentTransfer->getIdPaymentWorldline());
        $getPaymentRequestTransfer->setPaymentId($worldlinePaymentTransfer->getPaymentId());

        return $getPaymentRequestTransfer;
    }
}
