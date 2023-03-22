<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\Client;

use Ingenico\Connect\Sdk\DataObject;
use Ingenico\Connect\Sdk\Domain\Capture\CaptureResponse;
use Ingenico\Connect\Sdk\Domain\Hostedcheckout\CreateHostedCheckoutRequest;
use Ingenico\Connect\Sdk\Domain\Hostedcheckout\CreateHostedCheckoutResponse;
use Ingenico\Connect\Sdk\Domain\Hostedcheckout\GetHostedCheckoutResponse;
use Ingenico\Connect\Sdk\Domain\Payment\ApprovePaymentRequest;
use Ingenico\Connect\Sdk\Domain\Payment\CancelPaymentResponse;
use Ingenico\Connect\Sdk\Domain\Payment\CapturePaymentRequest;
use Ingenico\Connect\Sdk\Domain\Payment\PaymentApprovalResponse;
use Ingenico\Connect\Sdk\Domain\Payment\PaymentResponse;
use Ingenico\Connect\Sdk\Domain\Product\PaymentProducts;
use Ingenico\Connect\Sdk\Domain\Refund\RefundRequest;
use Ingenico\Connect\Sdk\Domain\Refund\RefundResponse;
use Ingenico\Connect\Sdk\Merchant\Products\FindProductsParams;

interface WorldlineClientInterface
{
    /**
     * @param \Ingenico\Connect\Sdk\Domain\Hostedcheckout\CreateHostedCheckoutRequest $body
     *
     * @return \Ingenico\Connect\Sdk\Domain\Hostedcheckout\CreateHostedCheckoutResponse
     */
    public function createHostedCheckout(CreateHostedCheckoutRequest $body): CreateHostedCheckoutResponse;

    /**
     * @param string|null $hostedCheckoutId
     *
     * @return \Ingenico\Connect\Sdk\Domain\Hostedcheckout\GetHostedCheckoutResponse
     */
    public function getHostedCheckoutStatus(?string $hostedCheckoutId): GetHostedCheckoutResponse;

    /**
     * @param \Ingenico\Connect\Sdk\Merchant\Products\FindProductsParams $findProductsParams
     *
     * @return \Ingenico\Connect\Sdk\Domain\Product\PaymentProducts
     */
    public function getPaymentProducts(FindProductsParams $findProductsParams): PaymentProducts;

    /**
     * @param string|null $paymentId
     *
     * @return \Ingenico\Connect\Sdk\Domain\Payment\PaymentResponse
     */
    public function getPayment(?string $paymentId): PaymentResponse;

    /**
     * @param string|null $paymentId
     *
     * @return \Ingenico\Connect\Sdk\Domain\Payment\CancelPaymentResponse
     */
    public function cancelPayment(?string $paymentId): CancelPaymentResponse;

    /**
     * @param string|null $paymentId
     * @param \Ingenico\Connect\Sdk\Domain\Payment\CapturePaymentRequest $capturePaymentRequest
     *
     * @return \Ingenico\Connect\Sdk\Domain\Capture\CaptureResponse
     */
    public function capturePayment(?string $paymentId, CapturePaymentRequest $capturePaymentRequest): CaptureResponse;

    /**
     * @param string|null $paymentId
     * @param \Ingenico\Connect\Sdk\Domain\Payment\ApprovePaymentRequest $approvePaymentRequest
     *
     * @return \Ingenico\Connect\Sdk\Domain\Payment\PaymentApprovalResponse
     */
    public function approvePayment(?string $paymentId, ApprovePaymentRequest $approvePaymentRequest): PaymentApprovalResponse;

    /**
     * @param string|null $paymentId
     * @param \Ingenico\Connect\Sdk\Domain\Refund\RefundRequest $refundRequest
     *
     * @return \Ingenico\Connect\Sdk\Domain\Refund\RefundResponse
     */
    public function createRefund(?string $paymentId, RefundRequest $refundRequest): RefundResponse;

    /**
     * @param string|null $refundId
     *
     * @return \Ingenico\Connect\Sdk\Domain\Refund\RefundResponse
     */
    public function getRefund(?string $refundId): RefundResponse;

    /**
     * @param string|null $tokenId
     *
     * @return \Ingenico\Connect\Sdk\DataObject|null
     */
    public function deleteToken(?string $tokenId): ?DataObject;
}
