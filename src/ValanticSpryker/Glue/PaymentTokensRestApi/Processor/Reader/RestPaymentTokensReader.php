<?php

declare(strict_types = 1);

namespace ValanticSpryker\Glue\PaymentTokensRestApi\Processor\Reader;

use Generated\Shared\Transfer\WorldlinePaymentTokenRequestTransfer;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceInterface;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;
use Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface;
use Spryker\Zed\Customer\Business\Exception\CustomerNotFoundException;
use ValanticSpryker\Client\PaymentTokens\PaymentTokensClientInterface;
use ValanticSpryker\Glue\PaymentTokensRestApi\Processor\ResponseBuilder\PaymentTokensRestResponseBuilderInterface;

class RestPaymentTokensReader implements RestPaymentTokensReaderInterface
{
    /**
     * @var \ValanticSpryker\Client\PaymentTokens\PaymentTokensClientInterface
     */
    private PaymentTokensClientInterface $paymentTokenClientInterface;

    /**
     * @var \ValanticSpryker\Glue\PaymentTokensRestApi\Processor\ResponseBuilder\PaymentTokensRestResponseBuilderInterface
     */
    private PaymentTokensRestResponseBuilderInterface $restResponseBuilder;

    /**
     * @param \ValanticSpryker\Client\PaymentTokens\PaymentTokensClientInterface $paymentTokenClientInterface
     * @param \ValanticSpryker\Glue\PaymentTokensRestApi\Processor\ResponseBuilder\PaymentTokensRestResponseBuilderInterface $restResponseBuilder
     */
    public function __construct(PaymentTokensClientInterface $paymentTokenClientInterface, PaymentTokensRestResponseBuilderInterface $restResponseBuilder)
    {
        $this->paymentTokenClientInterface = $paymentTokenClientInterface;
        $this->restResponseBuilder = $restResponseBuilder;
    }

    /**
     * @inheritDoc
     */
    public function getPaymentTokensRestResponseByCustomerReference(RestRequestInterface $restRequest): RestResponseInterface
    {
        try {
            $customerReference = $this->extractCustomerReference($restRequest);
        } catch (CustomerNotFoundException $e) {
            return $this->restResponseBuilder->createErrorResponseForCustomerReferenceNotFound();
        }

        $worldlinePaymentTokensResponseTransfer = $this->paymentTokenClientInterface->getCustomerPaymentTokens(
            (new WorldlinePaymentTokenRequestTransfer())->setCustomerReference($customerReference),
        );
        if ($worldlinePaymentTokensResponseTransfer->getIsSuccessful() === false) {
            return $this->restResponseBuilder->createErrorResponseForPaymentTokens($worldlinePaymentTokensResponseTransfer);
        }

        return $this->restResponseBuilder->createRestResponseForGettingTokens($worldlinePaymentTokensResponseTransfer);
    }

    /**
     * @param \Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface $restRequest
     *
     * @throws \Spryker\Zed\Customer\Business\Exception\CustomerNotFoundException
     *
     * @return string
     */
    private function extractCustomerReference(RestRequestInterface $restRequest): string
    {
        $restUser = $restRequest->getRestUser();
        if (
            $restUser !== null
            && $restUser->getNaturalIdentifier() // With customer account
        ) {
            return $restUser->getNaturalIdentifier();
        }

        $customerReference = $restRequest->getResource()->getId();
        if ($customerReference) {
            return $customerReference;
        }

        $parentResources = $restRequest->getParentResources();
        if (
            $parentResources
            && array_key_exists('customers', $parentResources)
            && $parentResources['customers']->getId()
        ) {
            return $parentResources['customers']->getId();
        }

        throw new CustomerNotFoundException();
    }

    /**
     * @param \Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface $restRequest
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceInterface|null
     */
    public function getPaymentTokensRestResourceByCustomerReference(RestRequestInterface $restRequest): ?RestResourceInterface
    {
        try {
            $customerReference = $this->extractCustomerReference($restRequest);
        } catch (CustomerNotFoundException $e) {
            return null;
        }

        $worldlinePaymentTokensResponseTransfer = $this->paymentTokenClientInterface->getCustomerPaymentTokens(
            (new WorldlinePaymentTokenRequestTransfer())->setCustomerReference($customerReference),
        );

        return $this->restResponseBuilder->createPaymentTokensRestResource($worldlinePaymentTokensResponseTransfer);
    }
}
