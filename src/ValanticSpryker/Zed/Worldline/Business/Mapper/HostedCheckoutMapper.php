<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\Mapper;

use Generated\Shared\Transfer\WorldlineCreateHostedCheckoutResponseTransfer;
use Generated\Shared\Transfer\WorldlineCreateHostedCheckoutTransfer;
use Generated\Shared\Transfer\WorldlineGetHostedCheckoutStatusResponseTransfer;
use Ingenico\Connect\Sdk\Domain\Hostedcheckout\CreateHostedCheckoutRequest;
use Ingenico\Connect\Sdk\Domain\Hostedcheckout\CreateHostedCheckoutResponse;
use Ingenico\Connect\Sdk\Domain\Hostedcheckout\GetHostedCheckoutResponse;

class HostedCheckoutMapper extends AbstractWorldlineMapper implements HostedCheckoutMapperInterface
{
    /**
     * @param \Generated\Shared\Transfer\WorldlineCreateHostedCheckoutTransfer $hostedCheckoutTransfer
     *
     * @return \Ingenico\Connect\Sdk\Domain\Hostedcheckout\CreateHostedCheckoutRequest
     */
    public function mapCreateHostedCheckoutTransferToWorldlineCreateHostedCheckout(WorldlineCreateHostedCheckoutTransfer $hostedCheckoutTransfer): CreateHostedCheckoutRequest
    {
        $hostedCheckoutTransfer->requireHostedCheckoutSpecificInput();
        $hostedCheckoutTransfer->requireOrder();

        $createHostedCheckoutRequest = new CreateHostedCheckoutRequest();

        $createHostedCheckoutJson = json_encode(
            $hostedCheckoutTransfer->toArray(
                true,
                true,
            ),
            JSON_UNESCAPED_UNICODE,
        );

        $createHostedCheckoutArray = json_decode($createHostedCheckoutJson, true);
        $createHostedCheckoutObject = $this->toObject($createHostedCheckoutArray);
        $createHostedCheckoutRequest->fromObject($createHostedCheckoutObject);

        return $createHostedCheckoutRequest;
    }

    /**
     * @inheritDoc
     */
    public function mapCreateHostedCheckoutResponseToCreateHostedCheckoutResponseTransfer(
        CreateHostedCheckoutResponse $response,
        WorldlineCreateHostedCheckoutResponseTransfer $createHostedCheckoutResponseTransfer
    ): WorldlineCreateHostedCheckoutResponseTransfer {
        if (!$response->hostedCheckoutId) {
            return $createHostedCheckoutResponseTransfer;
        }
        $responseJson = $response->toJson();
        $responseArray = json_decode($responseJson, true, 512, JSON_THROW_ON_ERROR);

        $createHostedCheckoutResponseTransfer->fromArray($responseArray, true);
        $createHostedCheckoutResponseTransfer->setIsSuccess(true);
        $createHostedCheckoutResponseTransfer->setHttpStatusCode(200);

        return $createHostedCheckoutResponseTransfer;
    }

    /**
     * @param \Ingenico\Connect\Sdk\Domain\Hostedcheckout\GetHostedCheckoutResponse $response
     * @param \Generated\Shared\Transfer\WorldlineGetHostedCheckoutStatusResponseTransfer $hostedCheckoutStatusResponseTransfer
     *
     * @return \Generated\Shared\Transfer\WorldlineGetHostedCheckoutStatusResponseTransfer
     */
    public function mapGetHostedCheckoutStatusResponseToHostedCheckoutResponseTransfer(
        GetHostedCheckoutResponse $response,
        WorldlineGetHostedCheckoutStatusResponseTransfer $hostedCheckoutStatusResponseTransfer
    ): WorldlineGetHostedCheckoutStatusResponseTransfer {
        $responseJson = $response->toJson();
        $responseArray = json_decode($responseJson, true, 512, JSON_THROW_ON_ERROR);

        $hostedCheckoutStatusResponseTransfer->fromArray($responseArray, true);
        $hostedCheckoutStatusResponseTransfer->setIsSuccess(true);
        $hostedCheckoutStatusResponseTransfer->setHttpStatusCode(200);

        return $hostedCheckoutStatusResponseTransfer;
    }
}
