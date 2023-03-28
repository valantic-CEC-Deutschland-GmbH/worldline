<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\Mapper;

use Generated\Shared\Transfer\WorldlineRefundRequestTransfer;
use Generated\Shared\Transfer\WorldlineRefundResponseTransfer;
use Ingenico\Connect\Sdk\Domain\Refund\RefundRequest;
use Ingenico\Connect\Sdk\Domain\Refund\RefundResponse;

class RefundsMapper extends AbstractWorldlineMapper implements RefundsMapperInterface
{
    /**
     * @inheritDoc
     */
    public function mapWorldlineRefundRequestTransferToRefundRequest(WorldlineRefundRequestTransfer $refundRequestTransfer): RefundRequest
    {
        $refundRequest = new RefundRequest();

        $refundJson = json_encode(
            $refundRequestTransfer->toArray(
                true,
                true,
            ),
            JSON_UNESCAPED_UNICODE,
        );

        $refundArray = json_decode($refundJson, true);
        $refundObject = $this->toObject($refundArray);

        $refundRequest->fromObject($refundObject);

        return $refundRequest;
    }

    /**
     * @inheritDoc
     */
    public function mapWorldlineRefundResponseToWorldlineRefundResponseTransfer(
        RefundResponse $response,
        WorldlineRefundResponseTransfer $responseTransfer
    ): WorldlineRefundResponseTransfer {
        $refundResponseJson = $response->toJson();
        $refundResponseArray = json_decode($refundResponseJson, true, 512, JSON_THROW_ON_ERROR);

        $responseTransfer->fromArray($refundResponseArray, true);

        return $responseTransfer;
    }
}
