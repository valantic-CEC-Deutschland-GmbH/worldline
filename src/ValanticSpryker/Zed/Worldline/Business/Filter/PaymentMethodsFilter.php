<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\Filter;

use Generated\Shared\Transfer\PaymentMethodsTransfer;
use Generated\Shared\Transfer\PaymentMethodTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\WorldlineGetPaymentProductsRequestTransfer;
use Spryker\Shared\Kernel\Store;
use ValanticSpryker\Zed\Worldline\Business\ApiCallHandler\ApiCallHandlerInterface;
use ValanticSpryker\Zed\Worldline\Business\Mapper\PaymentProductsMapperInterface;
use ValanticSpryker\Zed\Worldline\WorldlineConfig;

class PaymentMethodsFilter implements PaymentMethodsFilterInterface
{
    /**
     * @param \ValanticSpryker\Zed\Worldline\Business\ApiCallHandler\ApiCallHandlerInterface $apiCallHandler
     * @param \ValanticSpryker\Zed\Worldline\Business\Mapper\PaymentProductsMapperInterface $paymentProductsMapper
     * @param \ValanticSpryker\Zed\Worldline\WorldlineConfig $worldlineConfig
     */
    public function __construct(private ApiCallHandlerInterface $apiCallHandler, private PaymentProductsMapperInterface $paymentProductsMapper, private WorldlineConfig $worldlineConfig)
    {
    }

    /**
     * @inheritDoc
     */
    public function filterPaymentMethods(PaymentMethodsTransfer $paymentMethodsTransfer, QuoteTransfer $quoteTransfer): PaymentMethodsTransfer
    {
        $filteredPaymentMethodsTransfer = new PaymentMethodsTransfer();

        $worldlineGetPaymentProductsRequestTransfer = new WorldlineGetPaymentProductsRequestTransfer();

        $worldlineGetPaymentProductsRequestTransfer
            ->setAmount($quoteTransfer->getTotals()?->getGrandTotal())
            ->setCurrencyCode($quoteTransfer->getCurrency()?->getCode())
            ->setLocale(Store::getInstance()->getCurrentLocale())
            ->setIsRecurring(false)
            ->setHide(null)
            ->setCountryCode($this->worldlineConfig->getCountryIso2Code());

        /** @var \Generated\Shared\Transfer\WorldlineGetPaymentProductsResponseTransfer $worldlineGetPaymentProductsResponseTransfer */
        $worldlineGetPaymentProductsResponseTransfer = $this->apiCallHandler->handleApiCall($worldlineGetPaymentProductsRequestTransfer);

        $this->keepAllNoneWorldlineMethods($paymentMethodsTransfer, $filteredPaymentMethodsTransfer);

        if ($worldlineGetPaymentProductsResponseTransfer->getIsSuccess()) {
            foreach ($worldlineGetPaymentProductsResponseTransfer->getPaymentProducts() as $worldlinePaymentProduct) {
                $paymentMethodTransfer = $this->getMatchingConfiguredPaymentMethods($worldlinePaymentProduct, $paymentMethodsTransfer, $quoteTransfer);
                if ($paymentMethodTransfer) {
                    $filteredPaymentMethodsTransfer->addMethod($paymentMethodTransfer);
                }
            }
        }

        return $filteredPaymentMethodsTransfer;
    }

    /**
     * @param mixed $worldlinePaymentProduct
     * @param \Generated\Shared\Transfer\PaymentMethodsTransfer $paymentMethodsTransfer
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return \Generated\Shared\Transfer\PaymentMethodTransfer|null
     */
    private function getMatchingConfiguredPaymentMethods(mixed $worldlinePaymentProduct, PaymentMethodsTransfer $paymentMethodsTransfer, QuoteTransfer $quoteTransfer): ?PaymentMethodTransfer
    {
        $customerTransfer = $quoteTransfer->getCustomer();

        $availableMethods = $paymentMethodsTransfer->getMethods();

        foreach ($availableMethods as $configuredPaymentMethod) {
            if (!$configuredPaymentMethod->getPaymentProvider()) {
                continue;
            }

            if (
                ($configuredPaymentMethod->getPaymentProvider()->getName() === WorldlineConfig::PROVIDER_NAME)
                && ($this->paymentProductsMapper->getInternalPaymentMethodKeyFromWorldlinePaymentProduct($worldlinePaymentProduct)
                    === $configuredPaymentMethod->getPaymentMethodKey())
            ) {
                return $configuredPaymentMethod;
            }
        }

        return null;
    }

    /**
     * @param \Generated\Shared\Transfer\PaymentMethodsTransfer $paymentMethodsTransfer
     * @param \Generated\Shared\Transfer\PaymentMethodsTransfer $filteredPaymentMethodsTransfer
     *
     * @return void
     */
    protected function keepAllNoneWorldlineMethods(PaymentMethodsTransfer $paymentMethodsTransfer, PaymentMethodsTransfer $filteredPaymentMethodsTransfer): void
    {
        foreach ($paymentMethodsTransfer->getMethods() as $paymentMethodTransfer) {
            if (
                ($paymentMethodTransfer->getPaymentProvider() === null)
                || ($paymentMethodTransfer->getPaymentProvider()->getName() !== WorldlineConfig::PROVIDER_NAME)
            ) {
                $filteredPaymentMethodsTransfer->addMethod($paymentMethodTransfer);
            }
        }
    }
}
