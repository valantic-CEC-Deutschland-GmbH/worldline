<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\ApiCallHandler\Refund;

use Exception;
use Generated\Shared\Transfer\WorldlineRefundRequestTransfer;
use Generated\Shared\Transfer\WorldlineRefundResponseTransfer;
use Spryker\Shared\Kernel\Transfer\TransferInterface;
use ValanticSpryker\Zed\Worldline\Business\ApiCallHandler\AbstractApiCallHandler;
use ValanticSpryker\Zed\Worldline\Business\Client\WorldlineClientInterface;
use ValanticSpryker\Zed\Worldline\Business\Logger\WorldlineApiLoggerInterface;
use ValanticSpryker\Zed\Worldline\Business\Mapper\RefundsMapperInterface;

class CreateRefundApiCallHandler extends AbstractApiCallHandler
{
    /**
     * @param \ValanticSpryker\Zed\Worldline\Business\Client\WorldlineClientInterface $worldlineClient
     * @param \ValanticSpryker\Zed\Worldline\Business\Mapper\RefundsMapperInterface $refundsMapper
     * @param \ValanticSpryker\Zed\Worldline\Business\Logger\WorldlineApiLoggerInterface $apiLogger
     */
    public function __construct(
        private WorldlineClientInterface $worldlineClient,
        private RefundsMapperInterface $refundsMapper,
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
        /** @var \Generated\Shared\Transfer\WorldlineRefundRequestTransfer $refundRequestTransfer */
        $refundRequestTransfer = $transfer;

        return $this->createRefund($refundRequestTransfer);
    }

    /**
     * @inheritDoc
     */
    protected function createRefund(WorldlineRefundRequestTransfer $refundRequestTransfer): WorldlineRefundResponseTransfer
    {
        $refundRequest = $this->refundsMapper->mapWorldlineRefundRequestTransferToRefundRequest($refundRequestTransfer);

        $paymentId = $refundRequestTransfer->getPaymentId();

        $responseTransfer = (new WorldlineRefundResponseTransfer())->setIsSuccess(false);
        try {
            $response = $this->worldlineClient->createRefund($paymentId, $refundRequest);
            $responseTransfer->setIsSuccess(true);

            $responseTransfer = $this->refundsMapper->mapWorldlineRefundResponseToWorldlineRefundResponseTransfer(
                $response,
                $responseTransfer,
            );
        } catch (Exception $exception) {
            $this->handleException($responseTransfer, $exception, $this->refundsMapper);
        }

        return $responseTransfer;
    }
}
