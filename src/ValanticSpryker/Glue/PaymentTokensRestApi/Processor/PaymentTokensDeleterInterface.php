<?php

declare(strict_types = 1);

namespace ValanticSpryker\Glue\PaymentTokensRestApi\Processor;

use Generated\Shared\Transfer\WorldlineDeleteTokenRequestTransfer;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;

interface PaymentTokensDeleterInterface
{
    /**
     * @param \Generated\Shared\Transfer\WorldlineDeleteTokenRequestTransfer $deleteTokenRequestTransfer
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface
     */
    public function deletePaymentTokenById(WorldlineDeleteTokenRequestTransfer $deleteTokenRequestTransfer): RestResponseInterface;
}
