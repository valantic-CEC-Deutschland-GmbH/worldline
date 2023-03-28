<?php

declare(strict_types = 1);

namespace ValanticSpryker\Glue\PaymentTokensRestApi\Validation;

use Generated\Shared\Transfer\WorldlineDeleteTokenResponseTransfer;
use Generated\Shared\Transfer\WorldlinePaymentTokensResponseTransfer;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;

interface RestApiErrorInterface
{
    /**
     * @param \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface $restResponse
     * @param \Generated\Shared\Transfer\WorldlinePaymentTokensResponseTransfer $worldlinePaymentTokensResponseTransfer
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface
     */
    public function addErrorsFromPaymentTokensResponseTransfer(RestResponseInterface $restResponse, WorldlinePaymentTokensResponseTransfer $worldlinePaymentTokensResponseTransfer): RestResponseInterface;

    /**
     * @param \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface $restResponse
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface
     */
    public function addCustomerReferenceMissingError(RestResponseInterface $restResponse): RestResponseInterface;

    /**
     * @param \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface $restResponse
     * @param \Generated\Shared\Transfer\WorldlineDeleteTokenResponseTransfer $responseTransfer
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface
     */
    public function addErrorsFromDeleteTokensResponseTransfer(RestResponseInterface $restResponse, WorldlineDeleteTokenResponseTransfer $responseTransfer): RestResponseInterface;
}
