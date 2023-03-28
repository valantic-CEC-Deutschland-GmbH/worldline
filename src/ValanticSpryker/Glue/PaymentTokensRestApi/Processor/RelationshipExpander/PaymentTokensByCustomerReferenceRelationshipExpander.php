<?php

declare(strict_types = 1);

namespace ValanticSpryker\Glue\PaymentTokensRestApi\Processor\RelationshipExpander;

use Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface;
use ValanticSpryker\Glue\PaymentTokensRestApi\Processor\Reader\RestPaymentTokensReaderInterface;

class PaymentTokensByCustomerReferenceRelationshipExpander implements PaymentTokensByCustomerReferenceRelationshipExpanderInterface
{
    /**
     * @param \ValanticSpryker\Glue\PaymentTokensRestApi\Processor\Reader\RestPaymentTokensReaderInterface $restPaymentTokensReader
     */
    public function __construct(private RestPaymentTokensReaderInterface $restPaymentTokensReader)
    {
    }

    /**
     * @param array $restResources
     * @param \Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface $restRequest
     *
     * @return void
     */
    public function addResourceRelationships(array $restResources, RestRequestInterface $restRequest): void
    {
        /** @var \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceInterface $restResource */
        foreach ($restResources as $restResource) {
            $paymentTokensRestResource = $this->restPaymentTokensReader->getPaymentTokensRestResourceByCustomerReference($restRequest);
            $restResource->addRelationship($paymentTokensRestResource);
        }
    }
}
