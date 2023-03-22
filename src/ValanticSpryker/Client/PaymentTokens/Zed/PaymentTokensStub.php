<?php

declare(strict_types = 1);

namespace ValanticSpryker\Client\PaymentTokens\Zed;

use Generated\Shared\Transfer\WorldlineDeleteTokenRequestTransfer;
use Generated\Shared\Transfer\WorldlineDeleteTokenResponseTransfer;
use Generated\Shared\Transfer\WorldlinePaymentTokenRequestTransfer;
use Generated\Shared\Transfer\WorldlinePaymentTokensResponseTransfer;
use Spryker\Client\ZedRequest\ZedRequestClientInterface;

class PaymentTokensStub implements PaymentTokensStubInterface
{
    /**
     * @var \Spryker\Client\ZedRequest\ZedRequestClientInterface
     */
    private ZedRequestClientInterface $zedStub;

    /**
     * @param \Spryker\Client\ZedRequest\ZedRequestClientInterface $zedStub
     */
    public function __construct(ZedRequestClientInterface $zedStub)
    {
        $this->zedStub = $zedStub;
    }

    /**
     * @param \Generated\Shared\Transfer\WorldlineDeleteTokenRequestTransfer $deleteTokenRequestTransfer
     *
     * @return \Generated\Shared\Transfer\WorldlineDeleteTokenResponseTransfer
     */
    public function deletePaymentTokenById(WorldlineDeleteTokenRequestTransfer $deleteTokenRequestTransfer): WorldlineDeleteTokenResponseTransfer
    {
        /** @var \Generated\Shared\Transfer\WorldlineDeleteTokenResponseTransfer $responseTransfer */
        $responseTransfer = $this->zedStub->call('/worldline/gateway/delete-payment-token-by-id', $deleteTokenRequestTransfer);

        return $responseTransfer;
    }

    /**
     * @inheritDoc
     */
    public function getCustomerPaymentTokens(WorldlinePaymentTokenRequestTransfer $worldlinePaymentTokenRequestTransfer): WorldlinePaymentTokensResponseTransfer
    {
        /** @var \Generated\Shared\Transfer\WorldlinePaymentTokensResponseTransfer $worldlinePaymentTokensResponseTransfer */
        $worldlinePaymentTokensResponseTransfer = $this->zedStub->call('/worldline/gateway/get-customer-payment-tokens', $worldlinePaymentTokenRequestTransfer);

        return $worldlinePaymentTokensResponseTransfer;
    }
}
