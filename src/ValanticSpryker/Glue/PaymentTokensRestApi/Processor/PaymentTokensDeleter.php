<?php

declare(strict_types = 1);

namespace ValanticSpryker\Glue\PaymentTokensRestApi\Processor;

use Generated\Shared\Transfer\WorldlineDeleteTokenRequestTransfer;
use ValanticSpryker\Client\PaymentTokens\PaymentTokensClientInterface;
use ValanticSpryker\Glue\PaymentTokensRestApi\Processor\ResponseBuilder\PaymentTokensRestResponseBuilderInterface;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;

class PaymentTokensDeleter implements PaymentTokensDeleterInterface
{
    /**
     * @param \ValanticSpryker\Client\PaymentTokens\PaymentTokensClientInterface $paymentTokensClient
     * @param \ValanticSpryker\Glue\PaymentTokensRestApi\Processor\ResponseBuilder\PaymentTokensRestResponseBuilderInterface $paymentTokensRestResponseBuilder
     */
    public function __construct(private PaymentTokensClientInterface $paymentTokensClient, private PaymentTokensRestResponseBuilderInterface $paymentTokensRestResponseBuilder)
    {
    }

    /**
     * @param \Generated\Shared\Transfer\WorldlineDeleteTokenRequestTransfer $deleteTokenRequestTransfer
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface
     */
    public function deletePaymentTokenById(WorldlineDeleteTokenRequestTransfer $deleteTokenRequestTransfer): RestResponseInterface
    {
        /** @var \Generated\Shared\Transfer\WorldlineDeleteTokenResponseTransfer $responseTransfer */
        $responseTransfer = $this->paymentTokensClient->deletePaymentTokenById($deleteTokenRequestTransfer);

        if (!$responseTransfer->getIsSuccess()) {
            return $this->paymentTokensRestResponseBuilder->createErrorResponseForDeleteToken($responseTransfer);
        }

        return $this->paymentTokensRestResponseBuilder->createRestResponseForDeletingToken($responseTransfer);
    }
}
