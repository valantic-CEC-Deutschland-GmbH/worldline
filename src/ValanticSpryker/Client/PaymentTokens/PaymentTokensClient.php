<?php

declare(strict_types = 1);

namespace ValanticSpryker\Client\PaymentTokens;

use Generated\Shared\Transfer\WorldlineDeleteTokenRequestTransfer;
use Generated\Shared\Transfer\WorldlinePaymentTokenRequestTransfer;
use Generated\Shared\Transfer\WorldlinePaymentTokensResponseTransfer;
use Spryker\Client\Kernel\AbstractClient;
use Spryker\Shared\Kernel\Transfer\TransferInterface;

/**
 * @method \ValanticSpryker\Client\PaymentTokens\PaymentTokensFactory getFactory()
 */
class PaymentTokensClient extends AbstractClient implements PaymentTokensClientInterface
{
    /**
     * @param \Generated\Shared\Transfer\WorldlinePaymentTokenRequestTransfer $worldlinePaymentTokenRequestTransfer
     *
     * @return \Generated\Shared\Transfer\WorldlinePaymentTokensResponseTransfer
     */
    public function getCustomerPaymentTokens(WorldlinePaymentTokenRequestTransfer $worldlinePaymentTokenRequestTransfer): WorldlinePaymentTokensResponseTransfer
    {
        return $this->getFactory()->createPaymentTokensStub()->getCustomerPaymentTokens($worldlinePaymentTokenRequestTransfer);
    }

    /**
     * @inheritDoc
     */
    public function deletePaymentTokenById(WorldlineDeleteTokenRequestTransfer $tokenTransfer): TransferInterface
    {
        return $this->getFactory()->createPaymentTokensStub()->deletePaymentTokenById($tokenTransfer);
    }
}
