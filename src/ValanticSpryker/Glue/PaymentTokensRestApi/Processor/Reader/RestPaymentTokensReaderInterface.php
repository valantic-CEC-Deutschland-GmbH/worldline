<?php

declare(strict_types = 1);

namespace ValanticSpryker\Glue\PaymentTokensRestApi\Processor\Reader;

use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceInterface;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;
use Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface;

interface RestPaymentTokensReaderInterface
{
 /**
  * @param \Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface $restRequest
  *
  * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface
  */
    public function getPaymentTokensRestResponseByCustomerReference(RestRequestInterface $restRequest): RestResponseInterface;

    /**
     * @param \Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface $restRequest
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceInterface|null
     */
    public function getPaymentTokensRestResourceByCustomerReference(RestRequestInterface $restRequest): ?RestResourceInterface;
}
