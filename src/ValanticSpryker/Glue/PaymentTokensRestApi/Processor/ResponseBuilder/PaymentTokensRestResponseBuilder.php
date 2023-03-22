<?php

declare(strict_types = 1);

namespace ValanticSpryker\Glue\PaymentTokensRestApi\Processor\ResponseBuilder;

use Generated\Shared\Transfer\RestPaymentTokensAttributesTransfer;
use Generated\Shared\Transfer\WorldlineDeleteTokenResponseTransfer;
use Generated\Shared\Transfer\WorldlinePaymentTokensResponseTransfer;
use ValanticSpryker\Glue\PaymentTokensRestApi\PaymentTokensRestApiConfig;
use ValanticSpryker\Glue\PaymentTokensRestApi\Processor\Mapper\RestPaymentTokensMapperInterface;
use ValanticSpryker\Glue\PaymentTokensRestApi\Validation\RestApiErrorInterface;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceBuilderInterface;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceInterface;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;
use Symfony\Component\HttpFoundation\Response;

class PaymentTokensRestResponseBuilder implements PaymentTokensRestResponseBuilderInterface
{
    private const STATUS_NO_CONTENT = 204;

    /**
     * @var \ValanticSpryker\Glue\PaymentTokensRestApi\Validation\RestApiErrorInterface
     */
    private RestApiErrorInterface $restApiError;

    /**
     * @var \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceBuilderInterface
     */
    private RestResourceBuilderInterface $restResourceBuilder;

    /**
     * @var \ValanticSpryker\Glue\PaymentTokensRestApi\Processor\Mapper\RestPaymentTokensMapperInterface
     */
    private RestPaymentTokensMapperInterface $restPaymentTokensMapper;

    /**
     * @param \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceBuilderInterface $restResourceBuilder
     * @param \ValanticSpryker\Glue\PaymentTokensRestApi\Validation\RestApiErrorInterface $restApiError
     * @param \ValanticSpryker\Glue\PaymentTokensRestApi\Processor\Mapper\RestPaymentTokensMapperInterface $restPaymentTokensMapper
     */
    public function __construct(RestResourceBuilderInterface $restResourceBuilder, RestApiErrorInterface $restApiError, RestPaymentTokensMapperInterface $restPaymentTokensMapper)
    {
        $this->restResourceBuilder = $restResourceBuilder;
        $this->restApiError = $restApiError;
        $this->restPaymentTokensMapper = $restPaymentTokensMapper;
    }

    /**
     * @inheritDoc
     */
    public function createRestResponseForDeletingToken(WorldlineDeleteTokenResponseTransfer $responseTransfer): RestResponseInterface
    {
        $restResponse = $this->restResourceBuilder->createRestResponse();

        $restResponse->setStatus(self::STATUS_NO_CONTENT);

        return $restResponse;
    }

    /**
     * @inheritDoc
     */
    public function createErrorResponseForDeleteToken(WorldlineDeleteTokenResponseTransfer $responseTransfer): RestResponseInterface
    {
        $restResponse = $this->restResourceBuilder->createRestResponse();

        $this->restApiError->addErrorsFromDeleteTokensResponseTransfer($restResponse, $responseTransfer);

        return $restResponse;
    }

    /**
     * @inheritDoc
     */
    public function createRestResponseForGettingTokens(WorldlinePaymentTokensResponseTransfer $worldlinePaymentTokensResponseTransfer): RestResponseInterface
    {
        $restResponse = $this->restResourceBuilder->createRestResponse();

        $resource = $this->createPaymentTokensRestResource($worldlinePaymentTokensResponseTransfer);

        $restResponse->addResource($resource);
        $restResponse->setStatus(Response::HTTP_OK);

        return $restResponse;
    }

    /**
     * @inheritDoc
     */
    public function createErrorResponseForCustomerReferenceNotFound(): RestResponseInterface
    {
        $restResponse = $this->restResourceBuilder->createRestResponse();

        $this->restApiError->addCustomerReferenceMissingError($restResponse);

        return $restResponse;
    }

    /**
     * @inheritDoc
     */
    public function createErrorResponseForPaymentTokens(WorldlinePaymentTokensResponseTransfer $responseTransfer): RestResponseInterface
    {
        $restResponse = $this->restResourceBuilder->createRestResponse();

        $this->restApiError->addErrorsFromPaymentTokensResponseTransfer($restResponse, $responseTransfer);

        return $restResponse;
    }

    /**
     * @param \Generated\Shared\Transfer\WorldlinePaymentTokensResponseTransfer $worldlinePaymentTokensResponseTransfer
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceInterface|null
     */
    public function createPaymentTokensRestResource(
        WorldlinePaymentTokensResponseTransfer $worldlinePaymentTokensResponseTransfer
    ): ?RestResourceInterface {
        $resource = null;
        if ($worldlinePaymentTokensResponseTransfer->getIsSuccessful()) {
            $restPaymentTokensAttributesTransfer = new RestPaymentTokensAttributesTransfer();
            /** @var \Generated\Shared\Transfer\WorldlineCreditCardTokenTransfer $worldlineCreditCardTokenTransfer */
            foreach ($worldlinePaymentTokensResponseTransfer->getTokens() as $worldlineCreditCardTokenTransfer) {
                $restPaymentTokensAttributesTransfer->addToken(
                    $this->restPaymentTokensMapper->map($worldlineCreditCardTokenTransfer),
                );
            }

            $resource = $this->restResourceBuilder->createRestResource(
                PaymentTokensRestApiConfig::RESOURCE_PAYMENT_TOKENS,
                null,
                $restPaymentTokensAttributesTransfer,
            );
        }

        return $resource;
    }
}
