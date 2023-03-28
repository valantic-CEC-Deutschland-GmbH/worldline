<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Communication\Plugin\Checkout;

use Generated\Shared\Transfer\CheckoutResponseTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Spryker\Zed\CheckoutExtension\Dependency\Plugin\CheckoutPostSaveInterface;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;

/**
 * @method \ValanticSpryker\Zed\Worldline\Business\WorldlineFacadeInterface getFacade()
 * @method \ValanticSpryker\Zed\Worldline\Communication\WorldlineCommunicationFactory getFactory()
 * @method \ValanticSpryker\Zed\Worldline\Persistence\WorldlineQueryContainerInterface getQueryContainer()
 * @method \ValanticSpryker\Zed\Worldline\WorldlineConfig getConfig()
 */
class WorldlineCheckoutPostSavePlugin extends AbstractPlugin implements CheckoutPostSaveInterface
{
    /**
     * {@inheritDoc}
     * - If the payment provider is 'Worldline' it handles redirects and errors after order placement otherwise doesn't do anything.
     * - Executes `createHostedCheckout` API call.
     * - Updates `CheckoutResponseTransfer` with errors or/and redirect url accordingly to API response.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     * @param \Generated\Shared\Transfer\CheckoutResponseTransfer $checkoutResponseTransfer
     *
     * @return void
     */
    public function executeHook(QuoteTransfer $quoteTransfer, CheckoutResponseTransfer $checkoutResponseTransfer): void
    {
        $this->getFacade()->orderPostSave($quoteTransfer, $checkoutResponseTransfer);
    }
}
