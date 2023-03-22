<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\ApiCallHandler\PaymentProducts;

use Exception;
use Generated\Shared\Transfer\WorldlineCapturePaymentRequestTransfer;
use Generated\Shared\Transfer\WorldlineCaptureResponseTransfer;
use ValanticSpryker\Zed\Worldline\Business\ApiCallHandler\AbstractApiCallHandler;
use ValanticSpryker\Zed\Worldline\Business\Client\WorldlineClientInterface;
use ValanticSpryker\Zed\Worldline\Business\Logger\WorldlineApiLoggerInterface;
use ValanticSpryker\Zed\Worldline\Business\Mapper\PaymentProductsMapperInterface;
use Spryker\Shared\Kernel\Transfer\TransferInterface;

class CapturePaymentApiCallHandler extends AbstractApiCallHandler
{
    /**
     * @param \ValanticSpryker\Zed\Worldline\Business\Client\WorldlineClientInterface $worldlineClient
     * @param \ValanticSpryker\Zed\Worldline\Business\Mapper\PaymentProductsMapperInterface $paymentProductsMapper
     * @param \ValanticSpryker\Zed\Worldline\Business\Logger\WorldlineApiLoggerInterface $apiLogger
     */
    public function __construct(
        private WorldlineClientInterface $worldlineClient,
        private PaymentProductsMapperInterface $paymentProductsMapper,
        WorldlineApiLoggerInterface $apiLogger
    ) {
        parent::__construct($apiLogger);
    }

    /**
     * @inheritDoc
     */
    protected function handleApiCallImpl(TransferInterface $transfer): TransferInterface
    {
        /** @var \Generated\Shared\Transfer\WorldlineCapturePaymentRequestTransfer $capturePaymentTransfer */
        $capturePaymentTransfer = $transfer;

        return $this->capturePayment($capturePaymentTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\WorldlineCapturePaymentRequestTransfer $capturePaymentTransfer
     *
     * @return \Generated\Shared\Transfer\WorldlineCaptureResponseTransfer
     */
    protected function capturePayment(WorldlineCapturePaymentRequestTransfer $capturePaymentTransfer): WorldlineCaptureResponseTransfer
    {
        $paymentId = $capturePaymentTransfer->getPaymentId();

        $capturePaymentRequest = $this->paymentProductsMapper->mapWorldlineCapturePaymentRequestTransferToCapturePaymentRequest($capturePaymentTransfer);

        $responseTransfer = (new WorldlineCaptureResponseTransfer())->setIsSuccess(false);

        try {
            $response = $this->worldlineClient->capturePayment($paymentId, $capturePaymentRequest);
            $responseTransfer->setIsSuccess(true);

            $responseTransfer = $this->paymentProductsMapper->mapWorldlineCaptureResponseToWorldlineCaptureResponseTransfer(
                $response,
                $responseTransfer,
            );
        } catch (Exception $exception) {
            $this->handleException($responseTransfer, $exception, $this->paymentProductsMapper);
        }

        return $responseTransfer;
    }
}
