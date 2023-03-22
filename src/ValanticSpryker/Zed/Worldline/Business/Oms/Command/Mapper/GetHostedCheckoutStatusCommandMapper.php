<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\Oms\Command\Mapper;

use Generated\Shared\Transfer\OrderTransfer;
use Generated\Shared\Transfer\WorldlineGetHostedCheckoutStatusTransfer;
use Spryker\Shared\Kernel\Transfer\TransferInterface;
use ValanticSpryker\Zed\Worldline\Business\Reader\WorldlineReaderInterface;

class GetHostedCheckoutStatusCommandMapper implements WorldlineCommandMapperInterface
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
        $getHostedCheckoutTransfer = new WorldlineGetHostedCheckoutStatusTransfer();

        $paymentWorldline = $this->worldlineReader->getPaymentWorldlineByIdSalesOrder($orderTransfer->getIdSalesOrder());
        if (!$paymentWorldline->getPaymentHostedCheckout()) {
            return $getHostedCheckoutTransfer;
        }
        $getHostedCheckoutTransfer->setFkPaymentWorldline($paymentWorldline->getIdPaymentWorldline());
        $getHostedCheckoutTransfer->setHostedCheckoutId($paymentWorldline->getPaymentHostedCheckout()->getHostedCheckoutId());

        return $getHostedCheckoutTransfer;
    }
}
