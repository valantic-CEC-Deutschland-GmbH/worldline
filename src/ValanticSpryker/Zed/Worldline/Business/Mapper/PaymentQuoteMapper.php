<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\Mapper;

use Generated\Shared\Transfer\PaymentWorldlineTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\RestCheckoutRequestAttributesTransfer;
use Generated\Shared\Transfer\WorldlinePaymentHostedCheckoutTransfer;
use ValanticSpryker\Zed\Worldline\WorldlineConfig;

class PaymentQuoteMapper implements PaymentQuoteMapperInterface
{
    /**
     * @param \Generated\Shared\Transfer\RestCheckoutRequestAttributesTransfer $restCheckoutRequestAttributesTransfer
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return \Generated\Shared\Transfer\QuoteTransfer
     */
    public function mapPaymentsToQuote(
        RestCheckoutRequestAttributesTransfer $restCheckoutRequestAttributesTransfer,
        QuoteTransfer $quoteTransfer
    ): QuoteTransfer {
        $restPaymentTransfers = $restCheckoutRequestAttributesTransfer->getPayments();

        if ($restPaymentTransfers->count() === 0) {
            return $quoteTransfer;
        }

        $paymentTransfer = $quoteTransfer->getPayment();

        if (!$paymentTransfer || $paymentTransfer->getPaymentProvider() !== WorldlineConfig::PROVIDER_NAME) {
            return $quoteTransfer;
        }

        $restPaymentTransfer = null;
        if ($restPaymentTransfers->offsetExists(0)) {
            /** @var \Generated\Shared\Transfer\RestPaymentTransfer $restPaymentTransfer */
            $restPaymentTransfer = $restPaymentTransfers->offsetGet(0);
        }

        if (!$restPaymentTransfer || !$restPaymentTransfer->getPaymentHostedCheckout()) {
            return $quoteTransfer;
        }

        $paymentTransfer->setPaymentWorldline(
            (new PaymentWorldlineTransfer())->setPaymentHostedCheckout(
                (new WorldlinePaymentHostedCheckoutTransfer())->fromArray($restPaymentTransfer->getPaymentHostedCheckout()->toArray()),
            )
                ->setPaymentMethod($restPaymentTransfer->getPaymentSelection()),
        );

        return $quoteTransfer;
    }
}
