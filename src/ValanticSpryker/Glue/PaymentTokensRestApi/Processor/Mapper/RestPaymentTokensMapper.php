<?php

declare(strict_types = 1);

namespace ValanticSpryker\Glue\PaymentTokensRestApi\Processor\Mapper;

use Generated\Shared\Transfer\RestPaymentTokenAttributesTransfer;
use Generated\Shared\Transfer\WorldlineCreditCardTokenTransfer;

class RestPaymentTokensMapper implements RestPaymentTokensMapperInterface
{
 /**
  * @inheritDoc
  */
    public function map(WorldlineCreditCardTokenTransfer $worldlineCreditCardTokenTransfer): RestPaymentTokenAttributesTransfer
    {
        $restPaymentTokenAttributesTransfer = (new RestPaymentTokenAttributesTransfer())->fromArray(
            $worldlineCreditCardTokenTransfer->toArray(),
            true,
        );
        $restPaymentTokenAttributesTransfer->setPaymentMethod($worldlineCreditCardTokenTransfer->getPaymentMethodName());

        return $restPaymentTokenAttributesTransfer;
    }
}
