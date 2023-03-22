<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\ApiCallHandler\Refund;

use Exception;
use Generated\Shared\Transfer\WorldlineGetRefundRequestTransfer;
use Generated\Shared\Transfer\WorldlineRefundResponseTransfer;
use ValanticSpryker\Zed\Worldline\Business\ApiCallHandler\AbstractApiCallHandler;
use ValanticSpryker\Zed\Worldline\Business\Client\WorldlineClientInterface;
use ValanticSpryker\Zed\Worldline\Business\Logger\WorldlineApiLoggerInterface;
use ValanticSpryker\Zed\Worldline\Business\Mapper\RefundsMapperInterface;
use Spryker\Shared\Kernel\Transfer\TransferInterface;

class GetRefundApiCallHandler extends AbstractApiCallHandler
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
     * @inheritDoc
     */
    protected function handleApiCallImpl(TransferInterface $transfer): TransferInterface
    {
        /** @var \Generated\Shared\Transfer\WorldlineGetRefundRequestTransfer $getRefundRequestTransfer */
        $getRefundRequestTransfer = $transfer;

        return $this->getRefund($getRefundRequestTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\WorldlineGetRefundRequestTransfer $getRefundRequestTransfer
     *
     * @return \Generated\Shared\Transfer\WorldlineRefundResponseTransfer
     */
    protected function getRefund(WorldlineGetRefundRequestTransfer $getRefundRequestTransfer): WorldlineRefundResponseTransfer
    {
        $refundId = $getRefundRequestTransfer->getRefundId();

        $responseTransfer = (new WorldlineRefundResponseTransfer())->setIsSuccess(false);
        try {
            $response = $this->worldlineClient->getRefund($refundId);
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
