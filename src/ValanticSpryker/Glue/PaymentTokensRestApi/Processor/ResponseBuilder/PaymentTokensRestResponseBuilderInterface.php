<?php

declare(strict_types = 1);

namespace ValanticSpryker\Glue\PaymentTokensRestApi\Processor\ResponseBuilder;

use Generated\Shared\Transfer\WorldlineDeleteTokenResponseTransfer;
use Generated\Shared\Transfer\WorldlinePaymentTokensResponseTransfer;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceInterface;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;

interface PaymentTokensRestResponseBuilderInterface
{
    /**
     * @param \Generated\Shared\Transfer\WorldlinePaymentTokensResponseTransfer $worldlinePaymentTokensResponseTransfer
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface
     */
    public function createRestResponseForGettingTokens(WorldlinePaymentTokensResponseTransfer $worldlinePaymentTokensResponseTransfer): RestResponseInterface;

    /**
     * @param \Generated\Shared\Transfer\WorldlineDeleteTokenResponseTransfer $responseTransfer
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface
     */
    public function createRestResponseForDeletingToken(WorldlineDeleteTokenResponseTransfer $responseTransfer): RestResponseInterface;

    /**
     * @param \Generated\Shared\Transfer\WorldlineDeleteTokenResponseTransfer $responseTransfer
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface
     */
    public function createErrorResponseForDeleteToken(WorldlineDeleteTokenResponseTransfer $responseTransfer): RestResponseInterface;

    /**
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface
     */
    public function createErrorResponseForCustomerReferenceNotFound(): RestResponseInterface;

    /**
     * @param \Generated\Shared\Transfer\WorldlinePaymentTokensResponseTransfer $responseTransfer
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface
     */
    public function createErrorResponseForPaymentTokens(WorldlinePaymentTokensResponseTransfer $responseTransfer): RestResponseInterface;

    /**
     * @param \Generated\Shared\Transfer\WorldlinePaymentTokensResponseTransfer $worldlinePaymentTokensResponseTransfer
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceInterface|null
     */
    public function createPaymentTokensRestResource(
        WorldlinePaymentTokensResponseTransfer $worldlinePaymentTokensResponseTransfer
    ): ?RestResourceInterface;
}
