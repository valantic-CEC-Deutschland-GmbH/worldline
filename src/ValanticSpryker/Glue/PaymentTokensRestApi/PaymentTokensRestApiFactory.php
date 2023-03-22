<?php

declare(strict_types = 1);

namespace ValanticSpryker\Glue\PaymentTokensRestApi;

use ValanticSpryker\Client\PaymentTokens\PaymentTokensClientInterface;
use ValanticSpryker\Glue\PaymentTokensRestApi\Processor\Mapper\RestPaymentTokensMapper;
use ValanticSpryker\Glue\PaymentTokensRestApi\Processor\Mapper\RestPaymentTokensMapperInterface;
use ValanticSpryker\Glue\PaymentTokensRestApi\Processor\PaymentTokensDeleter;
use ValanticSpryker\Glue\PaymentTokensRestApi\Processor\PaymentTokensDeleterInterface;
use ValanticSpryker\Glue\PaymentTokensRestApi\Processor\Reader\RestPaymentTokensReader;
use ValanticSpryker\Glue\PaymentTokensRestApi\Processor\Reader\RestPaymentTokensReaderInterface;
use ValanticSpryker\Glue\PaymentTokensRestApi\Processor\RelationshipExpander\PaymentTokensByCustomerReferenceRelationshipExpander;
use ValanticSpryker\Glue\PaymentTokensRestApi\Processor\RelationshipExpander\PaymentTokensByCustomerReferenceRelationshipExpanderInterface;
use ValanticSpryker\Glue\PaymentTokensRestApi\Processor\ResponseBuilder\PaymentTokensRestResponseBuilder;
use ValanticSpryker\Glue\PaymentTokensRestApi\Processor\ResponseBuilder\PaymentTokensRestResponseBuilderInterface;
use ValanticSpryker\Glue\PaymentTokensRestApi\Validation\RestApiError;
use ValanticSpryker\Glue\PaymentTokensRestApi\Validation\RestApiErrorInterface;
use Spryker\Glue\Kernel\AbstractFactory;

class PaymentTokensRestApiFactory extends AbstractFactory
{
    /**
     * @return \ValanticSpryker\Glue\PaymentTokensRestApi\Processor\PaymentTokensDeleterInterface
     */
    public function getPaymentTokensDeleter(): PaymentTokensDeleterInterface
    {
        return new PaymentTokensDeleter(
            $this->getPaymentTokensClient(),
            $this->createPaymentTokensRestResponseBuilder(),
        );
    }

    /**
     * @return \ValanticSpryker\Glue\PaymentTokensRestApi\Processor\Reader\RestPaymentTokensReaderInterface
     */
    public function createRestPaymentTokensReader(): RestPaymentTokensReaderInterface
    {
        return new RestPaymentTokensReader(
            $this->getPaymentTokensClient(),
            $this->createPaymentTokensRestResponseBuilder(),
        );
    }

    /**
     * @return \ValanticSpryker\Glue\PaymentTokensRestApi\Processor\ResponseBuilder\PaymentTokensRestResponseBuilderInterface
     */
    private function createPaymentTokensRestResponseBuilder(): PaymentTokensRestResponseBuilderInterface
    {
        return new PaymentTokensRestResponseBuilder(
            $this->getResourceBuilder(),
            $this->createRestApiError(),
            $this->createRestPaymentTokensMapper(),
        );
    }

    /**
     * @return \ValanticSpryker\Client\PaymentTokens\PaymentTokensClientInterface
     */
    private function getPaymentTokensClient(): PaymentTokensClientInterface
    {
        return $this->getProvidedDependency(PaymentTokensRestApiDependencyProvider::CLIENT_PAYMENT_TOKENS);
    }

    /**
     * @return \ValanticSpryker\Glue\PaymentTokensRestApi\Processor\Mapper\RestPaymentTokensMapperInterface
     */
    private function createRestPaymentTokensMapper(): RestPaymentTokensMapperInterface
    {
        return new RestPaymentTokensMapper();
    }

    /**
     * @return \ValanticSpryker\Glue\PaymentTokensRestApi\Validation\RestApiErrorInterface
     */
    private function createRestApiError(): RestApiErrorInterface
    {
        return new RestApiError();
    }

    /**
     * @return \ValanticSpryker\Glue\PaymentTokensRestApi\Processor\RelationshipExpander\PaymentTokensByCustomerReferenceRelationshipExpanderInterface
     */
    public function createPaymentTokensByCustomerReferenceRelationshipExpander(): PaymentTokensByCustomerReferenceRelationshipExpanderInterface
    {
        return new PaymentTokensByCustomerReferenceRelationshipExpander($this->createRestPaymentTokensReader());
    }
}
