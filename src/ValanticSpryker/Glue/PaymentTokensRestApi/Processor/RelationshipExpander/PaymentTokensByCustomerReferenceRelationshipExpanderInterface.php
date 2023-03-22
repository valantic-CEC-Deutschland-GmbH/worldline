<?php

declare(strict_types = 1);

namespace ValanticSpryker\Glue\PaymentTokensRestApi\Processor\RelationshipExpander;

use Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface;

interface PaymentTokensByCustomerReferenceRelationshipExpanderInterface
{
    /**
     * @param array $resources
     * @param \Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface $restRequest
     *
     * @return void
     */
    public function addResourceRelationships(array $resources, RestRequestInterface $restRequest): void;
}
