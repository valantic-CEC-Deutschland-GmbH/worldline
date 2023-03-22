<?php

declare(strict_types = 1);

namespace ValanticSpryker\Glue\PaymentTokensRestApi\Processor\Mapper;

use Generated\Shared\Transfer\RestPaymentTokenAttributesTransfer;
use Generated\Shared\Transfer\WorldlineCreditCardTokenTransfer;

interface RestPaymentTokensMapperInterface
{
 /**
  * @param \Generated\Shared\Transfer\WorldlineCreditCardTokenTransfer $worldlineCreditCardTokenTransfer
  *
  * @return \Generated\Shared\Transfer\RestPaymentTokenAttributesTransfer
  */
    public function map(WorldlineCreditCardTokenTransfer $worldlineCreditCardTokenTransfer): RestPaymentTokenAttributesTransfer;
}
