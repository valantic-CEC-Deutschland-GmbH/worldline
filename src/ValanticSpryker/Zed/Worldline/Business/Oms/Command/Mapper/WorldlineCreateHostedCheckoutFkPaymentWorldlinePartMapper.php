<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\Oms\Command\Mapper;

use Generated\Shared\Transfer\OrderTransfer;
use Generated\Shared\Transfer\PaymentWorldlineTransfer;
use Generated\Shared\Transfer\WorldlineCreateHostedCheckoutTransfer;

class WorldlineCreateHostedCheckoutFkPaymentWorldlinePartMapper implements WorldlineCreateHostedCheckoutPartMapperInterface
{
    /**
     * @inheritDoc
     */
    public function map(WorldlineCreateHostedCheckoutTransfer $worldlineCreateHostedCheckoutTransfer, OrderTransfer $orderTransfer, PaymentWorldlineTransfer $paymentWorldlineTransfer): WorldlineCreateHostedCheckoutTransfer
    {
        $worldlineCreateHostedCheckoutTransfer->setFkPaymentWorldline($paymentWorldlineTransfer->getIdPaymentWorldline());

        return $worldlineCreateHostedCheckoutTransfer;
    }
}
