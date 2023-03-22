<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\Oms\Command\Mapper;

use Generated\Shared\Transfer\OrderTransfer;
use Generated\Shared\Transfer\PaymentWorldlineTransfer;
use Generated\Shared\Transfer\WorldlineCreateHostedCheckoutTransfer;

interface WorldlineCreateHostedCheckoutPartMapperInterface
{
    /**
     * @param \Generated\Shared\Transfer\WorldlineCreateHostedCheckoutTransfer $worldlineCreateHostedCheckoutTransfer
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     * @param \Generated\Shared\Transfer\PaymentWorldlineTransfer $paymentWorldlineTransfer
     *
     * @return \Generated\Shared\Transfer\WorldlineCreateHostedCheckoutTransfer
     */
    public function map(
        WorldlineCreateHostedCheckoutTransfer $worldlineCreateHostedCheckoutTransfer,
        OrderTransfer $orderTransfer,
        PaymentWorldlineTransfer $paymentWorldlineTransfer
    ): WorldlineCreateHostedCheckoutTransfer;
}
