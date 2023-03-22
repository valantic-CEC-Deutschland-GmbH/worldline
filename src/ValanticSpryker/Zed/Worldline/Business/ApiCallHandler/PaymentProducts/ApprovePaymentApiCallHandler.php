<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\ApiCallHandler\PaymentProducts;

use Exception;
use Generated\Shared\Transfer\WorldlineApprovePaymentRequestTransfer;
use Generated\Shared\Transfer\WorldlineApprovePaymentResponseTransfer;
use ValanticSpryker\Zed\Worldline\Business\ApiCallHandler\AbstractApiCallHandler;
use ValanticSpryker\Zed\Worldline\Business\Client\WorldlineClientInterface;
use ValanticSpryker\Zed\Worldline\Business\Logger\WorldlineApiLoggerInterface;
use ValanticSpryker\Zed\Worldline\Business\Mapper\PaymentProductsMapperInterface;
use Spryker\Shared\Kernel\Transfer\TransferInterface;

class ApprovePaymentApiCallHandler extends AbstractApiCallHandler
{
    /**
     * @param \ValanticSpryker\Zed\Worldline\Business\Client\WorldlineClientInterface $worldlineClient
     * @param \ValanticSpryker\Zed\Worldline\Business\Mapper\PaymentProductsMapperInterface $paymentProductsMapper
     * @param \ValanticSpryker\Zed\Worldline\Business\Logger\WorldlineApiLoggerInterface $worldlineApiLogger
     */
    public function __construct(private WorldlineClientInterface $worldlineClient, private PaymentProductsMapperInterface $paymentProductsMapper, WorldlineApiLoggerInterface $worldlineApiLogger)
    {
        parent::__construct($worldlineApiLogger);
    }

    /**
     * @param \Spryker\Shared\Kernel\Transfer\TransferInterface $transfer
     *
     * @return \Spryker\Shared\Kernel\Transfer\TransferInterface
     */
    protected function handleApiCallImpl(TransferInterface $transfer): TransferInterface
    {
        /** @var \Generated\Shared\Transfer\WorldlineApprovePaymentRequestTransfer $approvePaymentTransfer */
        $approvePaymentTransfer = $transfer;

        return $this->approvePayment($approvePaymentTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\WorldlineApprovePaymentRequestTransfer $approvePaymentTransfer
     *
     * @return \Generated\Shared\Transfer\WorldlineApprovePaymentResponseTransfer
     */
    private function approvePayment(WorldlineApprovePaymentRequestTransfer $approvePaymentTransfer): WorldlineApprovePaymentResponseTransfer
    {
        $paymentId = $approvePaymentTransfer->getPaymentId();

        $approvePaymentRequest = $this->paymentProductsMapper->mapWorldlineApprovePaymentRequestTransferToApprovePaymentRequest($approvePaymentTransfer);

        $responseTransfer = (new WorldlineApprovePaymentResponseTransfer())->setIsSuccess(false);

        try {
            $response = $this->worldlineClient->approvePayment($paymentId, $approvePaymentRequest);
            $responseTransfer->setIsSuccess(true);

            $responseTransfer = $this->paymentProductsMapper->mapWorldlineApprovePaymentResponseToWorldlineApprovePaymentResponseTransfer(
                $response,
                $responseTransfer,
            );
        } catch (Exception $exception) {
            $this->handleException($responseTransfer, $exception, $this->paymentProductsMapper);
        }

        return $responseTransfer;
    }
}
