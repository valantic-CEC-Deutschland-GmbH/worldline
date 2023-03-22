<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\Oms\Command\Mapper;

use Generated\Shared\Transfer\OrderTransfer;
use Generated\Shared\Transfer\PaymentWorldlineTransfer;
use Generated\Shared\Transfer\WorldlineCreateHostedCheckoutTransfer;
use Generated\Shared\Transfer\WorldlineFraudFieldsTransfer;
use ValanticSpryker\Zed\Worldline\WorldlineConfig;

class WorldlineCreateHostedCheckoutFraudFieldsPartMapper implements WorldlineCreateHostedCheckoutPartMapperInterface
{
    /**
     * @param \ValanticSpryker\Zed\Worldline\WorldlineConfig $worldlineConfig
     */
    public function __construct(private WorldlineConfig $worldlineConfig)
    {
    }

    /**
     * @inheritDoc
     */
    public function map(WorldlineCreateHostedCheckoutTransfer $worldlineCreateHostedCheckoutTransfer, OrderTransfer $orderTransfer, PaymentWorldlineTransfer $paymentWorldlineTransfer): WorldlineCreateHostedCheckoutTransfer
    {
        $worldlineCreateHostedCheckoutTransfer->setFraudFields($this->getWorldlineFraudFieldsTransfer($orderTransfer, $paymentWorldlineTransfer));

        return $worldlineCreateHostedCheckoutTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     * @param \Generated\Shared\Transfer\PaymentWorldlineTransfer $worldlinePaymentTransfer
     *
     * @return \Generated\Shared\Transfer\WorldlineFraudFieldsTransfer
     */
    private function getWorldlineFraudFieldsTransfer(OrderTransfer $orderTransfer, PaymentWorldlineTransfer $worldlinePaymentTransfer): WorldlineFraudFieldsTransfer
    {
        $worldlineFraudFields = new WorldlineFraudFieldsTransfer();

        $worldlineFraudFields->setCustomerIpAddress($worldlinePaymentTransfer->getPaymentHostedCheckout()->getCustomerIpAddress());
        $worldlineFraudFields->setOrderTimeZone($this->worldlineConfig->getTimeZone());

        $customer = $orderTransfer->getCustomer();
        $worldlineFraudFields->setUserData([$customer->getFirstName(), $customer->getLastName(), $customer->getEmail()]);

        return $worldlineFraudFields;
    }
}
