<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\Oms\Command\Mapper;

use Generated\Shared\Transfer\OrderTransfer;
use Generated\Shared\Transfer\WorldlineApprovePaymentRequestTransfer;
use ValanticSpryker\Zed\Worldline\Business\Reader\WorldlineReaderInterface;
use Spryker\Shared\Kernel\Transfer\TransferInterface;

class ApprovePaymentCommandMapper implements WorldlineCommandMapperInterface
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
        $approvePaymentRequestTransfer = new WorldlineApprovePaymentRequestTransfer();

        $worldlinePaymentTransfer = $this->worldlineReader->getPaymentWorldlineByIdSalesOrder($orderTransfer->getIdSalesOrder());

        if (!$worldlinePaymentTransfer->getPaymentId()) {
            return $approvePaymentRequestTransfer;
        }
        $approvePaymentRequestTransfer->setFkPaymentWorldline($worldlinePaymentTransfer->getIdPaymentWorldline());
        $approvePaymentRequestTransfer->setPaymentId($worldlinePaymentTransfer->getPaymentId());

        return $approvePaymentRequestTransfer;
    }
}
