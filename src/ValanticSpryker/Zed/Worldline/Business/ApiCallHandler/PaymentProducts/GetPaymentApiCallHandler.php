<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\ApiCallHandler\PaymentProducts;

use Exception;
use Generated\Shared\Transfer\WorldlineGetPaymentRequestTransfer;
use Generated\Shared\Transfer\WorldlineGetPaymentResponseTransfer;
use ValanticSpryker\Zed\Worldline\Business\ApiCallHandler\AbstractApiCallHandler;
use ValanticSpryker\Zed\Worldline\Business\Client\WorldlineClientInterface;
use ValanticSpryker\Zed\Worldline\Business\Logger\WorldlineApiLoggerInterface;
use ValanticSpryker\Zed\Worldline\Business\Mapper\PaymentProductsMapperInterface;
use Spryker\Shared\Kernel\Transfer\TransferInterface;

class GetPaymentApiCallHandler extends AbstractApiCallHandler
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
     * @param \Spryker\Shared\Kernel\Transfer\TransferInterface $transfer
     *
     * @return \Spryker\Shared\Kernel\Transfer\TransferInterface
     */
    protected function handleApiCallImpl(TransferInterface $transfer): TransferInterface
    {
        /** @var \Generated\Shared\Transfer\WorldlineGetPaymentRequestTransfer $getPaymentTransfer */
        $getPaymentTransfer = $transfer;

        return $this->getPayment($getPaymentTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\WorldlineGetPaymentRequestTransfer $getPaymentTransfer
     *
     * @return \Generated\Shared\Transfer\WorldlineGetPaymentResponseTransfer
     */
    protected function getPayment(WorldlineGetPaymentRequestTransfer $getPaymentTransfer): WorldlineGetPaymentResponseTransfer
    {
        $paymentId = $getPaymentTransfer->getPaymentId();

        $responseTransfer = (new WorldlineGetPaymentResponseTransfer())->setIsSuccess(false);

        try {
            $response = $this->worldlineClient->getPayment($paymentId);
            $responseTransfer->setIsSuccess(true);

            $responseTransfer = $this->paymentProductsMapper->mapWorldlineGetPaymentResponseToWorldlineGetPaymentResponseTransfer(
                $response,
                $responseTransfer,
            );
        } catch (Exception $exception) {
            $this->handleException($responseTransfer, $exception, $this->paymentProductsMapper);
        }

        return $responseTransfer;
    }
}
