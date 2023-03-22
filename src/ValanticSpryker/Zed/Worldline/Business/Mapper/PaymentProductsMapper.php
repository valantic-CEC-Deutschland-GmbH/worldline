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
use Ingenico\Connect\Sdk\Domain\Payment\Definitions\OrderApprovePayment;
use Ingenico\Connect\Sdk\Domain\Payment\PaymentApprovalResponse;
use Ingenico\Connect\Sdk\Domain\Payment\PaymentResponse;
use Ingenico\Connect\Sdk\Domain\Product\PaymentProducts;
use Ingenico\Connect\Sdk\Merchant\Products\FindProductsParams;
use ValanticSpryker\Zed\Worldline\WorldlineConfig;

class PaymentProductsMapper extends AbstractWorldlineMapper implements PaymentProductsMapperInterface
{
    private const WORLDLINE_PAYMENT_METHOD_CREDIT_CARD = 'card';

    private const PAYMENT_METHOD_KEY_PART_CREDIT_CARD = 'CreditCard';

    private const PAYMENT_METHOD_KEY_PART_PAYPAL = 'Paypal';

    private const PAYMENT_METHOD_KEY_PART_PROVIDER = 'worldline';

    /**
     * @param \Generated\Shared\Transfer\WorldlineGetPaymentProductsRequestTransfer $getPaymentProductsRequestTransfer
     *
     * @return \Ingenico\Connect\Sdk\Merchant\Products\FindProductsParams
     */
    public function mapWorldlineGetPaymentProductsRequestTransferToFindProductsParams(
        WorldlineGetPaymentProductsRequestTransfer $getPaymentProductsRequestTransfer
    ): FindProductsParams {
        $findProductsParams = new FindProductsParams();

        $findProductsParams->locale = $getPaymentProductsRequestTransfer->getLocale();
        $findProductsParams->amount = $getPaymentProductsRequestTransfer->getAmount();
        $findProductsParams->countryCode = $getPaymentProductsRequestTransfer->getCountryCode();
        $findProductsParams->currencyCode = $getPaymentProductsRequestTransfer->getCurrencyCode();
        if ($getPaymentProductsRequestTransfer->getHide()) {
            $findProductsParams->hide = [$getPaymentProductsRequestTransfer->getHide()];
        }
        $findProductsParams->isRecurring = $getPaymentProductsRequestTransfer->getIsRecurring();

        return $findProductsParams;
    }

    /**
     * @param \Ingenico\Connect\Sdk\Domain\Product\PaymentProducts $paymentProducts
     * @param \Generated\Shared\Transfer\WorldlineGetPaymentProductsResponseTransfer $responseTransfer
     *
     * @return \Generated\Shared\Transfer\WorldlineGetPaymentProductsResponseTransfer
     */
    public function mapWorldlinePaymentProductsToWorldlineGetPaymentProductsResponseTransfer(
        PaymentProducts $paymentProducts,
        WorldlineGetPaymentProductsResponseTransfer $responseTransfer
    ): WorldlineGetPaymentProductsResponseTransfer {
        $this->genericallyMapWorldlineResponseToSprykerResponseTransfer($paymentProducts, $responseTransfer);

        return $responseTransfer;
    }

    /**
     * @param \Ingenico\Connect\Sdk\Domain\Payment\PaymentResponse $response
     * @param \Generated\Shared\Transfer\WorldlineGetPaymentResponseTransfer $responseTransfer
     *
     * @return \Generated\Shared\Transfer\WorldlineGetPaymentResponseTransfer
     */
    public function mapWorldlineGetPaymentResponseToWorldlineGetPaymentResponseTransfer(
        PaymentResponse $response,
        WorldlineGetPaymentResponseTransfer $responseTransfer
    ): WorldlineGetPaymentResponseTransfer {
        $this->genericallyMapWorldlineResponseToSprykerResponseTransfer($response, $responseTransfer);

        return $responseTransfer;
    }

    /**
     * @param \Ingenico\Connect\Sdk\Domain\Payment\CancelPaymentResponse $response
     * @param \Generated\Shared\Transfer\WorldlineCancelPaymentResponseTransfer $responseTransfer
     *
     * @return \Generated\Shared\Transfer\WorldlineCancelPaymentResponseTransfer
     */
    public function mapWorldlineCancelPaymentResponseToWorldlineCancelPaymentResponseTransfer(CancelPaymentResponse $response, WorldlineCancelPaymentResponseTransfer $responseTransfer): WorldlineCancelPaymentResponseTransfer
    {
        $this->genericallyMapWorldlineResponseToSprykerResponseTransfer($response, $responseTransfer);

        return $responseTransfer;
    }

    /**
     * @inheritDoc
     */
    public function mapWorldlineCapturePaymentRequestTransferToCapturePaymentRequest(
        WorldlineCapturePaymentRequestTransfer $capturePaymentTransfer
    ): CapturePaymentRequest {
        $capturePaymentRequest = new CapturePaymentRequest();

        $capturePaymentRequest->amount = $capturePaymentTransfer->getAmount();
        $capturePaymentRequest->isFinal = $capturePaymentTransfer->getIsFinal();

        return $capturePaymentRequest;
    }

    /**
     * @param \Ingenico\Connect\Sdk\Domain\Capture\CaptureResponse $response
     * @param \Generated\Shared\Transfer\WorldlineCaptureResponseTransfer $responseTransfer
     *
     * @return \Generated\Shared\Transfer\WorldlineCaptureResponseTransfer
     */
    public function mapWorldlineCaptureResponseToWorldlineCaptureResponseTransfer(
        CaptureResponse $response,
        WorldlineCaptureResponseTransfer $responseTransfer
    ): WorldlineCaptureResponseTransfer {
        $this->genericallyMapWorldlineResponseToSprykerResponseTransfer($response, $responseTransfer);

        return $responseTransfer;
    }

    /**
     * @inheritDoc
     */
    public function getInternalPaymentMethodKeyFromWorldlinePaymentProduct(WorldlinePaymentProductTransfer $worldlinePaymentProduct): ?string
    {
        $worldlinePaymentProduct->requireDisplayHints();

        $paymentMethodKey = self::PAYMENT_METHOD_KEY_PART_PROVIDER;
        $worldlinePaymentMethodName = $worldlinePaymentProduct->getPaymentMethod();

        switch ($worldlinePaymentMethodName) {
            case self::WORLDLINE_PAYMENT_METHOD_CREDIT_CARD:
                $paymentMethodKey .= self::PAYMENT_METHOD_KEY_PART_CREDIT_CARD;

                $paymentMethodKey .= str_replace(' ', '', $worldlinePaymentProduct->getDisplayHints()->getLabel());

                return $paymentMethodKey;
            case 'redirect':
                if ($worldlinePaymentProduct->getId() === WorldlineConfig::WORLDLINE_PAYPAL_PAYMENT_PRODUCT_ID) {
                    $paymentMethodKey .= self::PAYMENT_METHOD_KEY_PART_PAYPAL;

                    return $paymentMethodKey;
                }

                break;
            default:
                return null;
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function mapWorldlineApprovePaymentRequestTransferToApprovePaymentRequest(WorldlineApprovePaymentRequestTransfer $approvePaymentTransfer): ApprovePaymentRequest
    {
        $approvePaymentRequest = new ApprovePaymentRequest();

        $approvePaymentRequest->amount = $approvePaymentTransfer->getAmount();

        if ($approvePaymentTransfer->getOrder()) {
            $orderJson = json_encode(
                $approvePaymentTransfer->getOrder()->toArray(
                    true,
                    true,
                ),
                JSON_UNESCAPED_UNICODE,
            );

            $orderArray = json_decode($orderJson, true);

            $order = new OrderApprovePayment();
            $order->fromObject($this->toObject($orderArray));
            $approvePaymentRequest->order = $order;
        }

        return $approvePaymentRequest;
    }

    /**
     * @inheritDoc
     */
    public function mapWorldlineApprovePaymentResponseToWorldlineApprovePaymentResponseTransfer(
        PaymentApprovalResponse $response,
        WorldlineApprovePaymentResponseTransfer $responseTransfer
    ): WorldlineApprovePaymentResponseTransfer {
        $this->genericallyMapWorldlineResponseToSprykerResponseTransfer($response, $responseTransfer);

        return $responseTransfer;
    }
}
