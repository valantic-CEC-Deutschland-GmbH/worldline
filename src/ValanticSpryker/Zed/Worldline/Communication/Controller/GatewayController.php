<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Communication\Controller;

use Generated\Shared\Transfer\WorldlineDeleteTokenRequestTransfer;
use Generated\Shared\Transfer\WorldlineDeleteTokenResponseTransfer;
use Generated\Shared\Transfer\WorldlinePaymentTokenRequestTransfer;
use Generated\Shared\Transfer\WorldlinePaymentTokensResponseTransfer;
use Spryker\Zed\Kernel\Communication\Controller\AbstractGatewayController;

/**
 * @method \ValanticSpryker\Zed\Worldline\Business\WorldlineFacadeInterface getFacade()()
 * @method \ValanticSpryker\Zed\Worldline\Communication\WorldlineCommunicationFactory getFactory()
 * @method \ValanticSpryker\Zed\Worldline\Persistence\WorldlineQueryContainerInterface getQueryContainer()
 * @method \ValanticSpryker\Zed\Worldline\Persistence\WorldlineRepositoryInterface getRepository()
 */
class GatewayController extends AbstractGatewayController
{
    /**
     * @param \Generated\Shared\Transfer\WorldlinePaymentTokenRequestTransfer $worldlinePaymentTokenRequestTransfer
     *
     * @return \Generated\Shared\Transfer\WorldlinePaymentTokensResponseTransfer
     */
    public function getCustomerPaymentTokensAction(WorldlinePaymentTokenRequestTransfer $worldlinePaymentTokenRequestTransfer): WorldlinePaymentTokensResponseTransfer
    {
        return $this->getFacade()
            ->getPaymentTokens(
                $worldlinePaymentTokenRequestTransfer,
            );
    }

     /**
      * @param \Generated\Shared\Transfer\WorldlineDeleteTokenRequestTransfer $tokenTransfer
      *
      * @return \Generated\Shared\Transfer\WorldlineDeleteTokenResponseTransfer
      */
    public function deletePaymentTokenByIdAction(WorldlineDeleteTokenRequestTransfer $tokenTransfer): WorldlineDeleteTokenResponseTransfer
    {
        return $this->getFacade()->deletePaymentTokenById($tokenTransfer);
    }
}
