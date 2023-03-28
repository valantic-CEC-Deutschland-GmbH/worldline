<?php

declare(strict_types = 1);

namespace ValanticSpryker\Client\PaymentTokens;

use Generated\Shared\Transfer\WorldlineDeleteTokenRequestTransfer;
use Generated\Shared\Transfer\WorldlinePaymentTokenRequestTransfer;
use Generated\Shared\Transfer\WorldlinePaymentTokensResponseTransfer;
use Spryker\Shared\Kernel\Transfer\TransferInterface;

interface PaymentTokensClientInterface
{
    /**
     * Retrieves payment tokens for the specified customer
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\WorldlinePaymentTokenRequestTransfer $worldlinePaymentTokenRequestTransfer
     *
     * @return \Generated\Shared\Transfer\WorldlinePaymentTokensResponseTransfer
     */
    public function getCustomerPaymentTokens(WorldlinePaymentTokenRequestTransfer $worldlinePaymentTokenRequestTransfer): WorldlinePaymentTokensResponseTransfer;

    /**
     * deletes a payment token that is saved in the shop
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\WorldlineDeleteTokenRequestTransfer $tokenTransfer
     *
     * @return \Spryker\Shared\Kernel\Transfer\TransferInterface
     */
    public function deletePaymentTokenById(WorldlineDeleteTokenRequestTransfer $tokenTransfer): TransferInterface;
}
