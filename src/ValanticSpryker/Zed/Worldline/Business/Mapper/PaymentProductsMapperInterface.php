<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\Mapper;

use Generated\Shared\Transfer\WorldlineApprovePaymentRequestTransfer;
use Generated\Shared\Transfer\WorldlineApprovePaymentResponseTransfer;
use Generated\Shared\Transfer\WorldlineCancelPaymentResponseTransfer;
use Generated\Shared\Transfer\WorldlineCapturePaymentRequestTransfer;
use Generated\Shared\Transfer\WorldlineCaptureResponseTransfer;
use Generated\Shared\Transfer\WorldlineGetPaymentProductsRequestTransfer;
use Generated\Shared\Transfer\WorldlineGetPaymentProductsResponseTransfer;
use Generated\Shared\Transfer\WorldlineGetPaymentResponseTransfer;
use Generated\Shared\Transfer\WorldlinePaymentProductTransfer;
use Ingenico\Connect\Sdk\Domain\Capture\CaptureResponse;
use Ingenico\Connect\Sdk\Domain\Payment\ApprovePaymentRequest;
use Ingenico\Connect\Sdk\Domain\Payment\CancelPaymentResponse;
use Ingenico\Connect\Sdk\Domain\Payment\CapturePaymentRequest;
use Ingenico\Connect\Sdk\Domain\Payment\PaymentApprovalResponse;
use Ingenico\Connect\Sdk\Domain\Payment\PaymentResponse;
use Ingenico\Connect\Sdk\Domain\Product\PaymentProducts;
use Ingenico\Connect\Sdk\Merchant\Products\FindProductsParams;

interface PaymentProductsMapperInterface extends WorldlineMapperInterface
{
    /**
     * @param \Generated\Shared\Transfer\WorldlineGetPaymentProductsRequestTransfer $getPaymentProductsRequestTransfer
     *
     * @return \Ingenico\Connect\Sdk\Merchant\Products\FindProductsParams
     */
    public function mapWorldlineGetPaymentProductsRequestTransferToFindProductsParams(
        WorldlineGetPaymentProductsRequestTransfer $getPaymentProductsRequestTransfer
    ): FindProductsParams;

    /**
     * @param \Ingenico\Connect\Sdk\Domain\Product\PaymentProducts $paymentProducts
     * @param \Generated\Shared\Transfer\WorldlineGetPaymentProductsResponseTransfer $responseTransfer
     *
     * @return \Generated\Shared\Transfer\WorldlineGetPaymentProductsResponseTransfer
     */
    public function mapWorldlinePaymentProductsToWorldlineGetPaymentProductsResponseTransfer(
        PaymentProducts $paymentProducts,
        WorldlineGetPaymentProductsResponseTransfer $responseTransfer
    ): WorldlineGetPaymentProductsResponseTransfer;

    /**
     * @param \Ingenico\Connect\Sdk\Domain\Payment\PaymentResponse $response
     * @param \Generated\Shared\Transfer\WorldlineGetPaymentResponseTransfer $responseTransfer
     *
     * @return \Generated\Shared\Transfer\WorldlineGetPaymentResponseTransfer
     */
    public function mapWorldlineGetPaymentResponseToWorldlineGetPaymentResponseTransfer(PaymentResponse $response, WorldlineGetPaymentResponseTransfer $responseTransfer): WorldlineGetPaymentResponseTransfer;

    /**
     * @param \Ingenico\Connect\Sdk\Domain\Payment\CancelPaymentResponse $response
     * @param \Generated\Shared\Transfer\WorldlineCancelPaymentResponseTransfer $responseTransfer
     *
     * @return \Generated\Shared\Transfer\WorldlineCancelPaymentResponseTransfer
     */
    public function mapWorldlineCancelPaymentResponseToWorldlineCancelPaymentResponseTransfer(CancelPaymentResponse $response, WorldlineCancelPaymentResponseTransfer $responseTransfer): WorldlineCancelPaymentResponseTransfer;

    /**
     * @param \Generated\Shared\Transfer\WorldlineCapturePaymentRequestTransfer $capturePaymentTransfer
     *
     * @return \Ingenico\Connect\Sdk\Domain\Payment\CapturePaymentRequest
     */
    public function mapWorldlineCapturePaymentRequestTransferToCapturePaymentRequest(
        WorldlineCapturePaymentRequestTransfer $capturePaymentTransfer
    ): CapturePaymentRequest;

    /**
     * @param \Ingenico\Connect\Sdk\Domain\Capture\CaptureResponse $response
     * @param \Generated\Shared\Transfer\WorldlineCaptureResponseTransfer $responseTransfer
     *
     * @return \Generated\Shared\Transfer\WorldlineCaptureResponseTransfer
     */
    public function mapWorldlineCaptureResponseToWorldlineCaptureResponseTransfer(
        CaptureResponse $response,
        WorldlineCaptureResponseTransfer $responseTransfer
    ): WorldlineCaptureResponseTransfer;

    /**
     * @param \Generated\Shared\Transfer\WorldlinePaymentProductTransfer $worldlinePaymentProduct
     *
     * @return string|null
     */
    public function getInternalPaymentMethodKeyFromWorldlinePaymentProduct(WorldlinePaymentProductTransfer $worldlinePaymentProduct): ?string;

    /**
     * @param \Generated\Shared\Transfer\WorldlineApprovePaymentRequestTransfer $approvePaymentTransfer
     *
     * @return \Ingenico\Connect\Sdk\Domain\Payment\ApprovePaymentRequest
     */
    public function mapWorldlineApprovePaymentRequestTransferToApprovePaymentRequest(
        WorldlineApprovePaymentRequestTransfer $approvePaymentTransfer
    ): ApprovePaymentRequest;

    /**
     * @param \Ingenico\Connect\Sdk\Domain\Payment\PaymentApprovalResponse $response
     * @param \Generated\Shared\Transfer\WorldlineApprovePaymentResponseTransfer $responseTransfer
     *
     * @return \Generated\Shared\Transfer\WorldlineApprovePaymentResponseTransfer
     */
    public function mapWorldlineApprovePaymentResponseToWorldlineApprovePaymentResponseTransfer(
        PaymentApprovalResponse $response,
        WorldlineApprovePaymentResponseTransfer $responseTransfer
    ): WorldlineApprovePaymentResponseTransfer;
}
