<?php

declare(strict_types = 1);

namespace ValanticSpryker\Glue\PaymentTokensRestApi\Validation;

use Generated\Shared\Transfer\RestErrorMessageTransfer;
use Generated\Shared\Transfer\WorldlineDeleteTokenResponseTransfer;
use Generated\Shared\Transfer\WorldlinePaymentTokensResponseTransfer;
use ValanticSpryker\Glue\PaymentTokensRestApi\PaymentTokensRestApiConfig;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;
use Symfony\Component\HttpFoundation\Response;

class RestApiError implements RestApiErrorInterface
{
    /**
     * @inheritDoc
     */
    public function addErrorsFromPaymentTokensResponseTransfer(RestResponseInterface $restResponse, WorldlinePaymentTokensResponseTransfer $worldlinePaymentTokensResponseTransfer): RestResponseInterface
    {
        $httpStatus = Response::HTTP_BAD_REQUEST;
        $restResponse->setStatus($httpStatus);

        /** @var \Generated\Shared\Transfer\WorldlinePaymentTokenErrorItemTransfer $error */
        foreach ($worldlinePaymentTokensResponseTransfer->getErrors() as $error) {
            $restResponse->addError(
                (new RestErrorMessageTransfer())
                    ->setCode($error->getMessagePropertyKey())
                    ->setDetail($error->getMessage())
                    ->setStatus($httpStatus),
            );
        }

        return $restResponse;
    }

    /**
     * @inheritDoc
     */
    public function addCustomerReferenceMissingError(RestResponseInterface $restResponse): RestResponseInterface
    {
        $httpStatus = Response::HTTP_BAD_REQUEST;
        $restResponse->setStatus($httpStatus);

        $restErrorMessageTransfer = (new RestErrorMessageTransfer())
            ->setCode(PaymentTokensRestApiConfig::RESPONSE_CODE_CUSTOMER_REFERENCE_MISSING)
            ->setStatus($httpStatus)
            ->setDetail(PaymentTokensRestApiConfig::RESPONSE_DETAILS_CUSTOMER_REFERENCE_MISSING);

        return $restResponse->addError($restErrorMessageTransfer);
    }

    /**
     * @inheritDoc
     */
    public function addErrorsFromDeleteTokensResponseTransfer(RestResponseInterface $restResponse, WorldlineDeleteTokenResponseTransfer $responseTransfer): RestResponseInterface
    {
        $restResponse->setStatus($responseTransfer->getHttpStatusCode());
        $errors = $responseTransfer->getErrors();

        foreach ($errors as $worldlineErrorItemTransfer) {
            $restResponse->addError(
                (new RestErrorMessageTransfer())
                    ->setCode($worldlineErrorItemTransfer->getCode())
                    ->setDetail($worldlineErrorItemTransfer->getMessage())
                    ->setStatus($worldlineErrorItemTransfer->getHttpStatusCode()),
            );
        }

        return $restResponse;
    }
}
