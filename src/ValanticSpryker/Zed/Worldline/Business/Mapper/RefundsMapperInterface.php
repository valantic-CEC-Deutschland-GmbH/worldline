<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\Mapper;

use Generated\Shared\Transfer\WorldlineRefundRequestTransfer;
use Generated\Shared\Transfer\WorldlineRefundResponseTransfer;
use Ingenico\Connect\Sdk\Domain\Refund\RefundRequest;
use Ingenico\Connect\Sdk\Domain\Refund\RefundResponse;

interface RefundsMapperInterface extends WorldlineMapperInterface
{
    /**
     * @param \Generated\Shared\Transfer\WorldlineRefundRequestTransfer $refundRequestTransfer
     *
     * @return \Ingenico\Connect\Sdk\Domain\Refund\RefundRequest
     */
    public function mapWorldlineRefundRequestTransferToRefundRequest(WorldlineRefundRequestTransfer $refundRequestTransfer): RefundRequest;

    /**
     * @param \Ingenico\Connect\Sdk\Domain\Refund\RefundResponse $response
     * @param \Generated\Shared\Transfer\WorldlineRefundResponseTransfer $responseTransfer
     *
     * @return \Generated\Shared\Transfer\WorldlineRefundResponseTransfer
     */
    public function mapWorldlineRefundResponseToWorldlineRefundResponseTransfer(
        RefundResponse $response,
        WorldlineRefundResponseTransfer $responseTransfer
    ): WorldlineRefundResponseTransfer;
}
