<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Persistence\Mapper;

use Generated\Shared\Transfer\PaymentWorldlineTransfer;
use Orm\Zed\Worldline\Persistence\VsyPaymentWorldline;

interface WorldlinePersistenceMapperInterface
{
    /**
     * @param \Orm\Zed\Worldline\Persistence\VsyPaymentWorldline $paymentWorldlineEntity
     * @param \Generated\Shared\Transfer\PaymentWorldlineTransfer $paymentWorldlineTransfer
     *
     * @return \Generated\Shared\Transfer\PaymentWorldlineTransfer
     */
    public function mapEntityToPaymentWorldlineTransfer(VsyPaymentWorldline $paymentWorldlineEntity, PaymentWorldlineTransfer $paymentWorldlineTransfer): PaymentWorldlineTransfer;
}
