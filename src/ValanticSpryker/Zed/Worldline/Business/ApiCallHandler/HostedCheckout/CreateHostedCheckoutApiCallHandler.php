<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\ApiCallHandler\HostedCheckout;

use Exception;
use Generated\Shared\Transfer\WorldlineCreateHostedCheckoutResponseTransfer;
use Generated\Shared\Transfer\WorldlineCreateHostedCheckoutTransfer;
use Spryker\Shared\Kernel\Transfer\TransferInterface;
use ValanticSpryker\Zed\Worldline\Business\ApiCallHandler\AbstractApiCallHandler;
use ValanticSpryker\Zed\Worldline\Business\Client\WorldlineClientInterface;
use ValanticSpryker\Zed\Worldline\Business\Logger\WorldlineApiLoggerInterface;
use ValanticSpryker\Zed\Worldline\Business\Mapper\HostedCheckoutMapperInterface;

class CreateHostedCheckoutApiCallHandler extends AbstractApiCallHandler
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
        /** @var \Generated\Shared\Transfer\WorldlineCreateHostedCheckoutTransfer $worldlineCreateHostedCheckoutTransfer */
        $worldlineCreateHostedCheckoutTransfer = $transfer;

        return $this->createHostedCheckout($worldlineCreateHostedCheckoutTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\WorldlineCreateHostedCheckoutTransfer $createHostedCheckoutTransfer
     *
     * @return \Generated\Shared\Transfer\WorldlineCreateHostedCheckoutResponseTransfer
     */
    protected function createHostedCheckout(WorldlineCreateHostedCheckoutTransfer $createHostedCheckoutTransfer): WorldlineCreateHostedCheckoutResponseTransfer
    {
        $hostedCheckoutResponseTransfer = new WorldlineCreateHostedCheckoutResponseTransfer();
        $hostedCheckoutResponseTransfer->setIsSuccess(false);

        $body = $this->hostedCheckoutMapper->mapCreateHostedCheckoutTransferToWorldlineCreateHostedCheckout($createHostedCheckoutTransfer);

        try {
            $response = $this->worldlineClient->createHostedCheckout($body);
            $hostedCheckoutResponseTransfer = $this->hostedCheckoutMapper->mapCreateHostedCheckoutResponseToCreateHostedCheckoutResponseTransfer($response, $hostedCheckoutResponseTransfer);
        } catch (Exception $exception) {
            $this->handleException($hostedCheckoutResponseTransfer, $exception, $this->hostedCheckoutMapper);
            if ($createHostedCheckoutTransfer->getOrder() && $createHostedCheckoutTransfer->getOrder()->getReferences()) {
                $hostedCheckoutResponseTransfer->setMerchantReference(
                    $createHostedCheckoutTransfer->getOrder()->getReferences()->getMerchantReference(),
                );
            }
        }

        return $hostedCheckoutResponseTransfer;
    }
}
