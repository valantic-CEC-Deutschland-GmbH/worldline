<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\Oms\Command\Mapper;

use Generated\Shared\Transfer\OrderTransfer;
use Generated\Shared\Transfer\PaymentWorldlineTransfer;
use Generated\Shared\Transfer\WorldlineCardPaymentMethodSpecificInputTransfer;
use Generated\Shared\Transfer\WorldlineCreateHostedCheckoutTransfer;
use ValanticSpryker\Zed\Worldline\WorldlineConfig;

class WorldlineCreateHostedCheckoutCardPaymentSpecificInputPartMapper implements WorldlineCreateHostedCheckoutPartMapperInterface
{
    /**
     * @param \ValanticSpryker\Zed\Worldline\WorldlineConfig $worldlineConfig
     */
    public function __construct(private WorldlineConfig $worldlineConfig)
    {
    }

    /**
     * @param \Generated\Shared\Transfer\WorldlineCreateHostedCheckoutTransfer $worldlineCreateHostedCheckoutTransfer
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     * @param \Generated\Shared\Transfer\PaymentWorldlineTransfer $paymentWorldlineTransfer
     *
     * @return \Generated\Shared\Transfer\WorldlineCreateHostedCheckoutTransfer
     */
    public function map(
        WorldlineCreateHostedCheckoutTransfer $worldlineCreateHostedCheckoutTransfer,
        OrderTransfer $orderTransfer,
        PaymentWorldlineTransfer $paymentWorldlineTransfer
    ): WorldlineCreateHostedCheckoutTransfer {
        $worldlineCreateHostedCheckoutTransfer->setCardPaymentMethodSpecificInput($this->getCardPaymentMethodSpecificInputTransfer($orderTransfer));

        return $worldlineCreateHostedCheckoutTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return \Generated\Shared\Transfer\WorldlineCardPaymentMethodSpecificInputTransfer
     */
    protected function getCardPaymentMethodSpecificInputTransfer(OrderTransfer $orderTransfer): WorldlineCardPaymentMethodSpecificInputTransfer
    {
        return (new WorldlineCardPaymentMethodSpecificInputTransfer())
            ->setCustomerReference($orderTransfer->getCustomerReference())
            ->setAuthorizationMode($this->worldlineConfig->getAuthorizationMode())
            ->setTokenize($this->worldlineConfig->getIsTokenizationEnabled())
            ->setTransactionChannel($this->worldlineConfig->getTransactionChannel())
            ->setRequiresApproval(true);
    }
}
