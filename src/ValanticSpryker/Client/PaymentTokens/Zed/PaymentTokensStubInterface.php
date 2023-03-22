<?php

declare(strict_types = 1);

namespace ValanticSpryker\Client\PaymentTokens\Zed;

use Generated\Shared\Transfer\WorldlineDeleteTokenRequestTransfer;
use Generated\Shared\Transfer\WorldlineDeleteTokenResponseTransfer;
use Generated\Shared\Transfer\WorldlinePaymentTokenRequestTransfer;
use Generated\Shared\Transfer\WorldlinePaymentTokensResponseTransfer;

interface PaymentTokensStubInterface
{
    /**
     * @param \Generated\Shared\Transfer\WorldlinePaymentTokenRequestTransfer $worldlinePaymentTokenRequestTransfer
     *
     * @return \Generated\Shared\Transfer\WorldlinePaymentTokensResponseTransfer
     */
    public function getCustomerPaymentTokens(WorldlinePaymentTokenRequestTransfer $worldlinePaymentTokenRequestTransfer): WorldlinePaymentTokensResponseTransfer;

    /**
     * @param \Generated\Shared\Transfer\WorldlineDeleteTokenRequestTransfer $deleteTokenRequestTransfer
     *
     * @return \Generated\Shared\Transfer\WorldlineDeleteTokenResponseTransfer
     */
    public function deletePaymentTokenById(WorldlineDeleteTokenRequestTransfer $deleteTokenRequestTransfer): WorldlineDeleteTokenResponseTransfer;
}
