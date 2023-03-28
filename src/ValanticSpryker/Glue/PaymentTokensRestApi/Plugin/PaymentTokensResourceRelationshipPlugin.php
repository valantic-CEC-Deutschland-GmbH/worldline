<?php

declare(strict_types = 1);

namespace ValanticSpryker\Glue\PaymentTokensRestApi\Plugin;

use Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface;
use Spryker\Glue\GlueApplicationExtension\Dependency\Plugin\ResourceRelationshipPluginInterface;
use Spryker\Glue\Kernel\AbstractPlugin;
use ValanticSpryker\Glue\PaymentTokensRestApi\PaymentTokensRestApiConfig;

/**
 * @method \ValanticSpryker\Glue\PaymentTokensRestApi\PaymentTokensRestApiFactory getFactory()
 */
class PaymentTokensResourceRelationshipPlugin extends AbstractPlugin implements ResourceRelationshipPluginInterface
{
    /**
     * @inheritDoc
     */
    public function addResourceRelationships(array $resources, RestRequestInterface $restRequest): void
    {
        $this->getFactory()
            ->createPaymentTokensByCustomerReferenceRelationshipExpander()
            ->addResourceRelationships($resources, $restRequest);
    }

    /**
     * @inheritDoc
     */
    public function getRelationshipResourceType(): string
    {
        return PaymentTokensRestApiConfig::RESOURCE_PAYMENT_TOKENS;
    }
}
