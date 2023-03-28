<?php

declare(strict_types = 1);

namespace ValanticSpryker\Glue\PaymentTokensRestApi\Controller;

use Generated\Shared\Transfer\WorldlineDeleteTokenRequestTransfer;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;
use Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface;
use Spryker\Glue\Kernel\Controller\AbstractController;

/**
 * @method \ValanticSpryker\Glue\PaymentTokensRestApi\PaymentTokensRestApiFactory getFactory()
 */
class PaymentTokensRestApiResourceController extends AbstractController
{
    /**
     * @Glue({
     *     "getCollection": {
     *          "summary": [
     *              "Retrieves payment tokens for the given customer"
     *          ],
     *          "parameters": [{
     *              "ref": "acceptLanguage"
     *          }],
     *          "responses": {
     *              "403": "Unauthorized request.",
     *              "404": "Customer not found."
     *          }
     *     }
     * })
     *
     * @param \Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface $restRequest
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface
     */
    public function getAction(RestRequestInterface $restRequest): RestResponseInterface
    {
        return $this->getFactory()
            ->createRestPaymentTokensReader()
            ->getPaymentTokensRestResponseByCustomerReference($restRequest);
    }

    /**
     * @Glue ({
     *     "delete": {
     *         "summary": [
     *             "deletes payment tokens."
     *         ],
     *         "parameters": [{
     *             "ref": "acceptLanguage"
     *         }],
     *         "responses": {
     *             "403": "Unauthorized request.",
     *             "404": "Token not found."
     *         }
     *     }
     * })
     *
     * @param \Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface $restRequest
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface
     */
    public function deleteAction(RestRequestInterface $restRequest): RestResponseInterface
    {
        $id = $restRequest->getResource()->getId();

        $deleteTokenRequestTransfer = new WorldlineDeleteTokenRequestTransfer();
        $deleteTokenRequestTransfer->setToken($id);
        $deleteTokenRequestTransfer->setCustomerReference($restRequest->getRestUser()->getNaturalIdentifier());

        return $this->getFactory()->getPaymentTokensDeleter()->deletePaymentTokenById($deleteTokenRequestTransfer);
    }
}
