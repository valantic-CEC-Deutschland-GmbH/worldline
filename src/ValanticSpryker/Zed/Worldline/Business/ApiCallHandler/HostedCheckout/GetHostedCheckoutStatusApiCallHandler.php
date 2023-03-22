<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\ApiCallHandler\HostedCheckout;

use Exception;
use Generated\Shared\Transfer\WorldlineGetHostedCheckoutStatusResponseTransfer;
use Generated\Shared\Transfer\WorldlineGetHostedCheckoutStatusTransfer;
use ValanticSpryker\Zed\Worldline\Business\ApiCallHandler\AbstractApiCallHandler;
use ValanticSpryker\Zed\Worldline\Business\Client\WorldlineClientInterface;
use ValanticSpryker\Zed\Worldline\Business\Logger\WorldlineApiLoggerInterface;
use ValanticSpryker\Zed\Worldline\Business\Mapper\HostedCheckoutMapperInterface;
use Spryker\Shared\Kernel\Transfer\TransferInterface;

class GetHostedCheckoutStatusApiCallHandler extends AbstractApiCallHandler
{
    /**
     * @param \ValanticSpryker\Zed\Worldline\Business\Client\WorldlineClientInterface $worldlineClient
     * @param \ValanticSpryker\Zed\Worldline\Business\Mapper\HostedCheckoutMapperInterface $hostedCheckoutMapper
     * @param \ValanticSpryker\Zed\Worldline\Business\Logger\WorldlineApiLoggerInterface $apiLogger
     */
    public function __construct(
        private WorldlineClientInterface $worldlineClient,
        private HostedCheckoutMapperInterface $hostedCheckoutMapper,
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
        /** @var \Generated\Shared\Transfer\WorldlineGetHostedCheckoutStatusTransfer $getHostedCheckoutStatusTransfer */
        $getHostedCheckoutStatusTransfer = $transfer;

        return $this->getStatus($getHostedCheckoutStatusTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\WorldlineGetHostedCheckoutStatusTransfer $getHostedCheckoutStatusTransfer
     *
     * @return \Generated\Shared\Transfer\WorldlineGetHostedCheckoutStatusResponseTransfer
     */
    protected function getStatus(WorldlineGetHostedCheckoutStatusTransfer $getHostedCheckoutStatusTransfer): WorldlineGetHostedCheckoutStatusResponseTransfer
    {
        $hostedCheckoutResponseTransfer = new WorldlineGetHostedCheckoutStatusResponseTransfer();
        $hostedCheckoutResponseTransfer->setIsSuccess(false);

        $hostedCheckoutId = $getHostedCheckoutStatusTransfer->getHostedCheckoutId();

        try {
            $response = $this->worldlineClient->getHostedCheckoutStatus($hostedCheckoutId);

            $hostedCheckoutResponseTransfer = $this->hostedCheckoutMapper->mapGetHostedCheckoutStatusResponseToHostedCheckoutResponseTransfer($response, $hostedCheckoutResponseTransfer);
        } catch (Exception $exception) {
            $this->handleException($hostedCheckoutResponseTransfer, $exception, $this->hostedCheckoutMapper);
        }

        return $hostedCheckoutResponseTransfer;
    }
}
