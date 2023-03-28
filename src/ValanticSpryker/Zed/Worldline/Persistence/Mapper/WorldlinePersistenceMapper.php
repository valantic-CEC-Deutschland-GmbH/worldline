<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Persistence\Mapper;

use Generated\Shared\Transfer\PaymentWorldlineTransfer;
use Generated\Shared\Transfer\WorldlinePaymentHostedCheckoutTransfer;
use Orm\Zed\Worldline\Persistence\VsyPaymentWorldline;

class WorldlinePersistenceMapper implements WorldlinePersistenceMapperInterface
{
    /**
     * @param \Orm\Zed\Worldline\Persistence\VsyPaymentWorldline $paymentWorldlineEntity
     * @param \Generated\Shared\Transfer\PaymentWorldlineTransfer $paymentWorldlineTransfer
     *
     * @return \Generated\Shared\Transfer\PaymentWorldlineTransfer
     */
    public function mapEntityToPaymentWorldlineTransfer(
        VsyPaymentWorldline $paymentWorldlineEntity,
        PaymentWorldlineTransfer $paymentWorldlineTransfer
    ): PaymentWorldlineTransfer {
        $paymentWorldlineTransfer->fromArray($paymentWorldlineEntity->toArray(), true);

        $paymentHostedCheckoutTransfer = new WorldlinePaymentHostedCheckoutTransfer();
        $paymentHostedCheckoutEntity = $paymentWorldlineEntity->getVsyPaymentWorldlineHostedCheckout();
        if ($paymentHostedCheckoutEntity) {
            $paymentHostedCheckoutTransfer->fromArray($paymentHostedCheckoutEntity->toArray(), true);
            $paymentWorldlineTransfer->setPaymentHostedCheckout($paymentHostedCheckoutTransfer);
        }

        return $paymentWorldlineTransfer;
    }
}
