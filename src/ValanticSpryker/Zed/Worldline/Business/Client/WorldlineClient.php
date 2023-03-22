<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\Client;

use DateTime;
use DateTimeZone;
use Ingenico\Connect\Sdk\Client;
use Ingenico\Connect\Sdk\Communicator;
use Ingenico\Connect\Sdk\CommunicatorConfiguration;
use Ingenico\Connect\Sdk\Connection;
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
use Ingenico\Connect\Sdk\Merchant\Tokens\DeleteTokenParams;
use ValanticSpryker\Zed\Worldline\WorldlineConfig;

class WorldlineClient implements WorldlineClientInterface
{
    /**
     * @var \Ingenico\Connect\Sdk\Client
     */
    private Client $client;

    /**
     * @param \ValanticSpryker\Zed\Worldline\WorldlineConfig $worldlineConfig
     * @param \Ingenico\Connect\Sdk\Connection $connection
     */
    public function __construct(private WorldlineConfig $worldlineConfig, private Connection $connection)
    {
        $this->initialize();
    }

    /**
     * @param \Ingenico\Connect\Sdk\Domain\Hostedcheckout\CreateHostedCheckoutRequest $body
     *
     * @return \Ingenico\Connect\Sdk\Domain\Hostedcheckout\CreateHostedCheckoutResponse
     */
    public function createHostedCheckout(CreateHostedCheckoutRequest $body): CreateHostedCheckoutResponse
    {
        return $this->client->merchant($this->worldlineConfig->getMerchantId())->hostedcheckouts()->create($body);
    }

    /**
     * @param string|null $hostedCheckoutId
     *
     * @return \Ingenico\Connect\Sdk\Domain\Hostedcheckout\GetHostedCheckoutResponse
     */
    public function getHostedCheckoutStatus(?string $hostedCheckoutId): GetHostedCheckoutResponse
    {
        return $this->client->merchant($this->worldlineConfig->getMerchantId())->hostedcheckouts()->get($hostedCheckoutId);
    }

    /**
     * @param \Ingenico\Connect\Sdk\Merchant\Products\FindProductsParams $findProductsParams
     *
     * @return \Ingenico\Connect\Sdk\Domain\Product\PaymentProducts
     */
    public function getPaymentProducts(FindProductsParams $findProductsParams): PaymentProducts
    {
        return $this->client->merchant($this->worldlineConfig->getMerchantId())->products()->find($findProductsParams);
    }

    /**
     * @param string|null $paymentId
     *
     * @return \Ingenico\Connect\Sdk\Domain\Payment\PaymentResponse
     */
    public function getPayment(?string $paymentId): PaymentResponse
    {
        return $this->client->merchant($this->worldlineConfig->getMerchantId())->payments()->get($paymentId);
    }

    /**
     * @inheritDoc
     */
    public function cancelPayment(?string $paymentId): CancelPaymentResponse
    {
        return $this->client->merchant($this->worldlineConfig->getMerchantId())->payments()->cancel($paymentId);
    }

    /**
     * @inheritDoc
     */
    public function capturePayment(?string $paymentId, CapturePaymentRequest $capturePaymentRequest): CaptureResponse
    {
        return $this->client->merchant($this->worldlineConfig->getMerchantId())->payments()->capture($paymentId, $capturePaymentRequest);
    }

    /**
     * @inheritDoc
     */
    public function approvePayment(?string $paymentId, ApprovePaymentRequest $approvePaymentRequest): PaymentApprovalResponse
    {
        return $this->client->merchant($this->worldlineConfig->getMerchantId())->payments()->approve($paymentId, $approvePaymentRequest);
    }

    /**
     * @param string|null $paymentId
     * @param \Ingenico\Connect\Sdk\Domain\Refund\RefundRequest $refundRequest
     *
     * @return \Ingenico\Connect\Sdk\Domain\Refund\RefundResponse
     */
    public function createRefund(?string $paymentId, RefundRequest $refundRequest): RefundResponse
    {
        return $this->client->merchant($this->worldlineConfig->getMerchantId())->payments()->refund($paymentId, $refundRequest);
    }

    /**
     * @inheritDoc
     */
    public function getRefund(?string $refundId): RefundResponse
    {
        return $this->client->merchant($this->worldlineConfig->getMerchantId())->refunds()->get($refundId);
    }

    /**
     * @return void
     */
    private function initialize(): void
    {
        $communicatorConfiguration = new CommunicatorConfiguration(
            $this->worldlineConfig->getApiKey(),
            $this->worldlineConfig->getApiSecret(),
            $this->worldlineConfig->getApiEndpoint(),
            $this->worldlineConfig->getIntegrator(),
            null,
        );
        $communicator = new Communicator($this->connection, $communicatorConfiguration);

        $this->client = new Client($communicator);
    }

    /**
     * @inheritDoc
     */
    public function deleteToken(?string $tokenId): ?DataObject
    {
        $query = new DeleteTokenParams();
        $query->mandateCancelDate = ((new DateTime())->setTimezone(new DateTimeZone('UTC'))->format('Ymd'));

        return $this->client->merchant($this->worldlineConfig->getMerchantId())->tokens()->delete($tokenId, $query);
    }
}
