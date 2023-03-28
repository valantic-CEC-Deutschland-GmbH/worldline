<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\Payment\Hook;

use Generated\Shared\Transfer\CheckoutResponseTransfer;
use Generated\Shared\Transfer\OrderTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use ValanticSpryker\Shared\Worldline\WorldlineConstants;
use ValanticSpryker\Zed\Worldline\Business\Reader\WorldlineReaderInterface;
use ValanticSpryker\Zed\Worldline\WorldlineConfig;

class PostSaveHook implements PostSaveHookInterface
{
    /**
     * @param \ValanticSpryker\Zed\Worldline\Business\Reader\WorldlineReaderInterface $worldlineReader
     */
    public function __construct(private WorldlineReaderInterface $worldlineReader)
    {
    }

    /**
     * @inheritDoc
     */
    public function executePostSaveHook(QuoteTransfer $quoteTransfer, CheckoutResponseTransfer $checkoutResponseTransfer): CheckoutResponseTransfer
    {
        if ($quoteTransfer->getPayment() && $quoteTransfer->getPayment()->getPaymentProvider() !== WorldlineConfig::PROVIDER_NAME) {
            return $checkoutResponseTransfer;
        }

        $orderTransfer = new OrderTransfer();

        if ($quoteTransfer->getPayment()) {
            $orderTransfer->setIdSalesOrder($quoteTransfer->getPayment()->getPaymentWorldline()->getFkSalesOrder());
        } else {
            foreach ($quoteTransfer->getPayments() as $payment) {
                if ($payment->getPaymentProvider() !== WorldlineConfig::PROVIDER_NAME) {
                    continue;
                }
                $orderTransfer->setIdSalesOrder($payment->getPaymentWorldline()->getFkSalesOrder());
            }
        }

        $paymentWorldlineTransfer = $this->worldlineReader->getPaymentWorldlineByIdSalesOrder($orderTransfer->getIdSalesOrder());
        $checkoutResponseTransfer->setIsSuccess(false);
        if (!$paymentWorldlineTransfer->getPaymentHostedCheckout()) {
            return $checkoutResponseTransfer;
        }

        $paymentWorldlineTransactionStatusTransfer = $this->worldlineReader->getLatestPaymentWorldlineTransactionStatusLogByFkPaymentWorldline($paymentWorldlineTransfer->getIdPaymentWorldline());
        if ($paymentWorldlineTransactionStatusTransfer->getStatus() === WorldlineConstants::STATUS_HOSTED_CHECKOUT_CREATED) {
            $checkoutResponseTransfer->setIsSuccess(true);
            $checkoutResponseTransfer
                ->setHostedCheckoutId($paymentWorldlineTransfer->getPaymentHostedCheckout()->getHostedCheckoutId())
                ->setReturnmac($paymentWorldlineTransfer->getPaymentHostedCheckout()->getReturnmac())
                ->setMerchantReference($paymentWorldlineTransfer->getMerchantReference())
                ->setPartialRedirectUrl($paymentWorldlineTransfer->getPaymentHostedCheckout()->getPartialRedirectUrl());
        }

        return $checkoutResponseTransfer;
    }
}
