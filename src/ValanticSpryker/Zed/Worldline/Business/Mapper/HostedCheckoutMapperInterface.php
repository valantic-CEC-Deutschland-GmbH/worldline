<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\Mapper;

use Generated\Shared\Transfer\WorldlineCreateHostedCheckoutResponseTransfer;
use Generated\Shared\Transfer\WorldlineCreateHostedCheckoutTransfer;
use Generated\Shared\Transfer\WorldlineGetHostedCheckoutStatusResponseTransfer;
use Ingenico\Connect\Sdk\Domain\Hostedcheckout\CreateHostedCheckoutRequest;
use Ingenico\Connect\Sdk\Domain\Hostedcheckout\CreateHostedCheckoutResponse;
use Ingenico\Connect\Sdk\Domain\Hostedcheckout\GetHostedCheckoutResponse;

interface HostedCheckoutMapperInterface extends WorldlineMapperInterface
{
    /**
     * @param \Generated\Shared\Transfer\WorldlineCreateHostedCheckoutTransfer $hostedCheckoutTransfer
     *
     * @return \Ingenico\Connect\Sdk\Domain\Hostedcheckout\CreateHostedCheckoutRequest
     */
    public function mapCreateHostedCheckoutTransferToWorldlineCreateHostedCheckout(WorldlineCreateHostedCheckoutTransfer $hostedCheckoutTransfer): CreateHostedCheckoutRequest;

    /**
     * @param \Ingenico\Connect\Sdk\Domain\Hostedcheckout\CreateHostedCheckoutResponse $response
     * @param \Generated\Shared\Transfer\WorldlineCreateHostedCheckoutResponseTransfer $createHostedCheckoutResponseTransfer
     *
     * @return \Generated\Shared\Transfer\WorldlineCreateHostedCheckoutResponseTransfer
     */
    public function mapCreateHostedCheckoutResponseToCreateHostedCheckoutResponseTransfer(
        CreateHostedCheckoutResponse $response,
        WorldlineCreateHostedCheckoutResponseTransfer $createHostedCheckoutResponseTransfer
    ): WorldlineCreateHostedCheckoutResponseTransfer;

    /**
     * @param \Ingenico\Connect\Sdk\Domain\Hostedcheckout\GetHostedCheckoutResponse $response
     * @param \Generated\Shared\Transfer\WorldlineGetHostedCheckoutStatusResponseTransfer $hostedCheckoutStatusResponseTransfer
     *
     * @return \Generated\Shared\Transfer\WorldlineGetHostedCheckoutStatusResponseTransfer
     */
    public function mapGetHostedCheckoutStatusResponseToHostedCheckoutResponseTransfer(
        GetHostedCheckoutResponse $response,
        WorldlineGetHostedCheckoutStatusResponseTransfer $hostedCheckoutStatusResponseTransfer
    ): WorldlineGetHostedCheckoutStatusResponseTransfer;
}
