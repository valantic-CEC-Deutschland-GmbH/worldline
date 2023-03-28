<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\ApiCallHandler\PaymentProducts;

use Exception;
use Generated\Shared\Transfer\WorldlineGetPaymentProductsRequestTransfer;
use Generated\Shared\Transfer\WorldlineGetPaymentProductsResponseTransfer;
use Spryker\Shared\Kernel\Transfer\TransferInterface;
use ValanticSpryker\Zed\Worldline\Business\ApiCallHandler\AbstractApiCallHandler;
use ValanticSpryker\Zed\Worldline\Business\Client\WorldlineClientInterface;
use ValanticSpryker\Zed\Worldline\Business\Logger\WorldlineApiLoggerInterface;
use ValanticSpryker\Zed\Worldline\Business\Mapper\PaymentProductsMapperInterface;

class GetPaymentProductsApiCallHandler extends AbstractApiCallHandler
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
        /** @var \Generated\Shared\Transfer\WorldlineGetPaymentProductsRequestTransfer $getPaymentProductsRequestTransfer */
        $getPaymentProductsRequestTransfer = $transfer;

        return $this->getPaymentProducts($getPaymentProductsRequestTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\WorldlineGetPaymentProductsRequestTransfer $getPaymentProductsRequestTransfer
     *
     * @return \Generated\Shared\Transfer\WorldlineGetPaymentProductsResponseTransfer
     */
    protected function getPaymentProducts(
        WorldlineGetPaymentProductsRequestTransfer $getPaymentProductsRequestTransfer
    ): WorldlineGetPaymentProductsResponseTransfer {
        $findProductsParams = $this->paymentProductsMapper->mapWorldlineGetPaymentProductsRequestTransferToFindProductsParams($getPaymentProductsRequestTransfer);

        $responseTransfer = (new WorldlineGetPaymentProductsResponseTransfer())->setIsSuccess(false);
        try {
            $response = $this->worldlineClient->getPaymentProducts($findProductsParams);
            $responseTransfer->setIsSuccess(true);

            $responseTransfer = $this->paymentProductsMapper->mapWorldlinePaymentProductsToWorldlineGetPaymentProductsResponseTransfer(
                $response,
                $responseTransfer,
            );
        } catch (Exception $exception) {
            $this->handleException($responseTransfer, $exception, $this->paymentProductsMapper);
        }

        return $responseTransfer;
    }
}
