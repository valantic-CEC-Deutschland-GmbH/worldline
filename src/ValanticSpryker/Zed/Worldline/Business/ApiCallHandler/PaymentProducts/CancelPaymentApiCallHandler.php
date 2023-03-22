<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\ApiCallHandler\PaymentProducts;

use Exception;
use Generated\Shared\Transfer\WorldlineCancelPaymentResponseTransfer;
use Generated\Shared\Transfer\WorldlineCancelPaymentTransfer;
use ValanticSpryker\Zed\Worldline\Business\ApiCallHandler\AbstractApiCallHandler;
use ValanticSpryker\Zed\Worldline\Business\Client\WorldlineClientInterface;
use ValanticSpryker\Zed\Worldline\Business\Logger\WorldlineApiLoggerInterface;
use ValanticSpryker\Zed\Worldline\Business\Mapper\PaymentProductsMapperInterface;
use Spryker\Shared\Kernel\Transfer\TransferInterface;

class CancelPaymentApiCallHandler extends AbstractApiCallHandler
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
        /** @var \Generated\Shared\Transfer\WorldlineCancelPaymentTransfer $cancelPaymentTransfer */
        $cancelPaymentTransfer = $transfer;

        return $this->cancelPayment($cancelPaymentTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\WorldlineCancelPaymentTransfer $cancelPaymentTransfer
     *
     * @return \Generated\Shared\Transfer\WorldlineCancelPaymentResponseTransfer
     */
    protected function cancelPayment(WorldlineCancelPaymentTransfer $cancelPaymentTransfer): WorldlineCancelPaymentResponseTransfer
    {
        $paymentId = $cancelPaymentTransfer->getPaymentId();

        $responseTransfer = (new WorldlineCancelPaymentResponseTransfer())->setIsSuccess(false);

        try {
            $response = $this->worldlineClient->cancelPayment($paymentId);
            $responseTransfer->setIsSuccess(true);

            $responseTransfer = $this->paymentProductsMapper->mapWorldlineCancelPaymentResponseToWorldlineCancelPaymentResponseTransfer(
                $response,
                $responseTransfer,
            );
        } catch (Exception $exception) {
            $this->handleException($responseTransfer, $exception, $this->paymentProductsMapper);
        }

        return $responseTransfer;
    }
}
