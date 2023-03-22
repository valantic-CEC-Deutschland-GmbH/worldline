<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\Order;

use Generated\Shared\Transfer\PaymentTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\SaveOrderTransfer;
use Generated\Shared\Transfer\WorldlinePaymentHostedCheckoutTransfer;
use Orm\Zed\Worldline\Persistence\VsyPaymentWorldline;
use Orm\Zed\Worldline\Persistence\VsyPaymentWorldlineHostedCheckout;
use Spryker\Zed\Kernel\Persistence\EntityManager\TransactionTrait;
use ValanticSpryker\Shared\Worldline\WorldlineConstants;
use ValanticSpryker\Zed\Worldline\WorldlineConfig;

class OrderManager implements OrderManagerInterface
{
    use TransactionTrait;

    /**
     * @param \ValanticSpryker\Zed\Worldline\WorldlineConfig $worldlineConfig
     */
    public function __construct(private WorldlineConfig $worldlineConfig)
    {
    }

    /**
     * @inheritDoc
     */
    public function saveOrderPayment(QuoteTransfer $quoteTransfer, SaveOrderTransfer $saveOrderTransfer): void
    {
        if ($quoteTransfer->getPayment()) {
            $payment = $quoteTransfer->getPayment();
            if ($payment->getPaymentProvider() !== WorldlineConfig::PROVIDER_NAME) {
                return;
            }
            $this->doSaveOrderPayment($payment, $saveOrderTransfer);

            return;
        }

        foreach ($quoteTransfer->getPayments() as $payment) {
            if ($payment->getPaymentProvider() !== WorldlineConfig::PROVIDER_NAME) {
                continue;
            }

            $this->doSaveOrderPayment($payment, $saveOrderTransfer);
        }
    }

    /**
     * @param \Generated\Shared\Transfer\PaymentTransfer $paymentTransfer
     *
     * @return \Orm\Zed\Worldline\Persistence\VsyPaymentWorldline
     */
    private function savePayment(PaymentTransfer $paymentTransfer): VsyPaymentWorldline
    {
        $paymentTransfer->requirePaymentWorldline();
        $payment = new VsyPaymentWorldline();

        $payment->fromArray(($paymentTransfer->getPaymentWorldline()->toArray()));

        if ($payment->getMerchantReference() === null) {
            $orderEntity = $payment->getSpySalesOrder();
            $payment->setMerchantReference($this->worldlineConfig->generateWorldlineReference($paymentTransfer, $orderEntity));
        }

        $payment->save();

        return $payment;
    }

    /**
     * @param \Orm\Zed\Worldline\Persistence\VsyPaymentWorldline $paymentWorldlineEntity
     * @param \Generated\Shared\Transfer\WorldlinePaymentHostedCheckoutTransfer $paymentHostedCheckoutTransfer
     *
     * @return void
     */
    private function savePaymentHostedCheckout(VsyPaymentWorldline $paymentWorldlineEntity, WorldlinePaymentHostedCheckoutTransfer $paymentHostedCheckoutTransfer): void
    {
        $vsyPaymentWorldlineHostedCheckout = new VsyPaymentWorldlineHostedCheckout();
        $vsyPaymentWorldlineHostedCheckout->fromArray($paymentHostedCheckoutTransfer->modifiedToArray());

        $returnUrl = $paymentHostedCheckoutTransfer->getReturnUrl();
        $vsyPaymentWorldlineHostedCheckout->setReturnUrl($this->addOrderReferenceAsQueryParameter($returnUrl, $paymentWorldlineEntity->getMerchantReference()));

        $vsyPaymentWorldlineHostedCheckout->setVsyPaymentWorldline($paymentWorldlineEntity);

        $vsyPaymentWorldlineHostedCheckout->save();
    }

    /**
     * @param mixed $payment
     * @param \Generated\Shared\Transfer\SaveOrderTransfer $saveOrderTransfer
     *
     * @return void
     */
    protected function doSaveOrderPayment(mixed $payment, SaveOrderTransfer $saveOrderTransfer): void
    {
        $this->getTransactionHandler()->handleTransaction(function () use ($payment, $saveOrderTransfer): void {
            /** @var \Generated\Shared\Transfer\PaymentTransfer $paymentTransfer */
            $paymentTransfer = $payment;
            $paymentTransfer->getPaymentWorldline()->setFkSalesOrder($saveOrderTransfer->getIdSalesOrder());
            $paymentTransfer->getPaymentWorldline()->setPaymentMethod($paymentTransfer->getPaymentSelection());
            $paymentTransfer->getPaymentWorldline()->setType(WorldlineConstants::WORLDLINE_PAYMENT_TYPE_HOSTED_CHECKOUT);
            $paymentWorldlineEntity = $this->savePayment($paymentTransfer);

            $paymentHostedCheckout = $paymentTransfer->getPaymentWorldline()->getPaymentHostedCheckout();

            $this->savePaymentHostedCheckout($paymentWorldlineEntity, $paymentHostedCheckout);
        });
    }

    /**
     * @param string $returnUrl
     * @param string $orderReference
     *
     * @return string
     */
    private function addOrderReferenceAsQueryParameter(string $returnUrl, string $orderReference): string
    {
        $parsedUrl = parse_url($returnUrl);

        $queryObject = [];
        if (array_key_exists('query', $parsedUrl)) {
            parse_str($parsedUrl['query'], $queryObject);
        }
        $queryObject['orderReference'] = $orderReference;

        $resultUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];

        if (array_key_exists('port', $parsedUrl)) {
            $resultUrl .= $parsedUrl['port'];
        }

        if (array_key_exists('path', $parsedUrl)) {
            $resultUrl .= $parsedUrl['path'];
        }

        $resultUrl .= '?' . http_build_query($queryObject);

        return $resultUrl;
    }
}
