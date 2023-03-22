<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\Oms\Command\Mapper;

use Generated\Shared\Transfer\HostedCheckoutSpecificInputTransfer;
use Generated\Shared\Transfer\OrderTransfer;
use Generated\Shared\Transfer\PaymentProductFiltersHostedCheckoutTransfer;
use Generated\Shared\Transfer\PaymentWorldlineTransfer;
use Generated\Shared\Transfer\WorldlineCreateHostedCheckoutTransfer;
use Generated\Shared\Transfer\WorldlinePaymentProductFilterTransfer;
use Spryker\Shared\Kernel\Store;
use ValanticSpryker\Zed\Worldline\Persistence\WorldlineQueryContainerInterface;
use ValanticSpryker\Zed\Worldline\WorldlineConfig;

class WorldlineCreateHostedCheckoutHostedCheckoutSpecificInputPartMapper implements WorldlineCreateHostedCheckoutPartMapperInterface
{
    /**
     * @param \ValanticSpryker\Zed\Worldline\WorldlineConfig $worldlineConfig
     * @param \ValanticSpryker\Zed\Worldline\Persistence\WorldlineQueryContainerInterface $worldlineQueryContainer
     */
    public function __construct(private WorldlineConfig $worldlineConfig, private WorldlineQueryContainerInterface $worldlineQueryContainer)
    {
    }

    /**
     * @inheritDoc
     */
    public function map(WorldlineCreateHostedCheckoutTransfer $worldlineCreateHostedCheckoutTransfer, OrderTransfer $orderTransfer, PaymentWorldlineTransfer $paymentWorldlineTransfer): WorldlineCreateHostedCheckoutTransfer
    {
        $worldlineCreateHostedCheckoutTransfer->setHostedCheckoutSpecificInput($this->getHostedCheckoutSpecificInput($orderTransfer, $paymentWorldlineTransfer));

        return $worldlineCreateHostedCheckoutTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     * @param \Generated\Shared\Transfer\PaymentWorldlineTransfer $worldlinePaymentTransfer
     *
     * @return \Generated\Shared\Transfer\HostedCheckoutSpecificInputTransfer
     */
    protected function getHostedCheckoutSpecificInput(
        OrderTransfer $orderTransfer,
        PaymentWorldlineTransfer $worldlinePaymentTransfer
    ): HostedCheckoutSpecificInputTransfer {
        $returnUrl = $worldlinePaymentTransfer->getPaymentHostedCheckout()->getReturnUrl();

        return (new HostedCheckoutSpecificInputTransfer())
            ->setLocale(Store::getInstance()->getCurrentLocale())
            ->setReturnUrl($returnUrl)
            ->setReturnCancelState($this->worldlineConfig->getReturnCancelState())
            ->setValidateShoppingCart(false)
            ->setShowResultPage(false)
            ->setPaymentProductFilters($this->getPaymentProductFilters($orderTransfer))
            ->setTokens($this->worldlineQueryContainer->getExistingTokens($orderTransfer->getCustomer()->getIdCustomer()));
    }

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return \Generated\Shared\Transfer\PaymentProductFiltersHostedCheckoutTransfer
     */
    private function getPaymentProductFilters(OrderTransfer $orderTransfer): PaymentProductFiltersHostedCheckoutTransfer
    {
        $paymentProductFilters = new PaymentProductFiltersHostedCheckoutTransfer();

        foreach ($orderTransfer->getPayments() as $payment) {
            if ($payment->getPaymentProvider() !== WorldlineConfig::PROVIDER_NAME) {
                continue;
            }
            $paymentMethod = $payment->getPaymentMethod();

            $paymentFilter = new WorldlinePaymentProductFilterTransfer();
            $paymentFilter->addProduct($this->worldlineConfig->mapPaymentMethodToPaymentProductId($paymentMethod));
            $paymentProductFilters->setRestrictTo($paymentFilter);
        }

        return $paymentProductFilters;
    }
}
