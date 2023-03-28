<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Communication\Plugin\PaymentMethod;

use Generated\Shared\Transfer\PaymentMethodsTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Spryker\Zed\PaymentExtension\Dependency\Plugin\PaymentMethodFilterPluginInterface;

/**
 * @method \ValanticSpryker\Zed\Worldline\Business\WorldlineFacadeInterface getFacade()
 * @method \ValanticSpryker\Zed\Worldline\Communication\WorldlineCommunicationFactory getFactory()
 * @method \ValanticSpryker\Zed\Worldline\Persistence\WorldlineQueryContainerInterface getQueryContainer()
 * @method \ValanticSpryker\Zed\Worldline\WorldlineConfig getConfig()
 */
class WorldlinePaymentMethodFilterPlugin extends AbstractPlugin implements PaymentMethodFilterPluginInterface
{
    /**
     * @inheritDoc
     */
    public function filterPaymentMethods(PaymentMethodsTransfer $paymentMethodsTransfer, QuoteTransfer $quoteTransfer): PaymentMethodsTransfer
    {
        return $this->getFacade()->filterPaymentMethods($paymentMethodsTransfer, $quoteTransfer);
    }
}
