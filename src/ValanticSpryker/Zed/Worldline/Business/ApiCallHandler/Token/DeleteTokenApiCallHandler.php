<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\ApiCallHandler\Token;

use Exception;
use Generated\Shared\Transfer\WorldlineDeleteTokenRequestTransfer;
use Generated\Shared\Transfer\WorldlineDeleteTokenResponseTransfer;
use Spryker\Shared\Kernel\Transfer\TransferInterface;
use ValanticSpryker\Zed\Worldline\Business\ApiCallHandler\AbstractApiCallHandler;
use ValanticSpryker\Zed\Worldline\Business\Client\WorldlineClientInterface;
use ValanticSpryker\Zed\Worldline\Business\Logger\WorldlineApiLoggerInterface;
use ValanticSpryker\Zed\Worldline\Business\Mapper\WorldlineMapperInterface;

class DeleteTokenApiCallHandler extends AbstractApiCallHandler
{
    /**
     * @param \ValanticSpryker\Zed\Worldline\Business\Client\WorldlineClientInterface $worldlineClient
     * @param \ValanticSpryker\Zed\Worldline\Business\Mapper\WorldlineMapperInterface $tokenMapper
     * @param \ValanticSpryker\Zed\Worldline\Business\Logger\WorldlineApiLoggerInterface $apiLogger
     */
    public function __construct(
        private WorldlineClientInterface $worldlineClient,
        private WorldlineMapperInterface $tokenMapper,
        WorldlineApiLoggerInterface $apiLogger
    ) {
        parent::__construct($apiLogger);
    }

    /**
     * @inheritDoc
     */
    protected function handleApiCallImpl(TransferInterface $transfer): TransferInterface
    {
        /** @var \Generated\Shared\Transfer\WorldlineDeleteTokenRequestTransfer $deleteTokenRequestTransfer */
        $deleteTokenRequestTransfer = $transfer;

        return $this->deleteToken($deleteTokenRequestTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\WorldlineDeleteTokenRequestTransfer $deleteTokenRequestTransfer
     *
     * @return \Generated\Shared\Transfer\WorldlineDeleteTokenResponseTransfer
     */
    private function deleteToken(WorldlineDeleteTokenRequestTransfer $deleteTokenRequestTransfer): WorldlineDeleteTokenResponseTransfer
    {
        $token = $deleteTokenRequestTransfer->getToken();

        $responseTransfer = (new WorldlineDeleteTokenResponseTransfer())->setIsSuccess(false);
        try {
            $this->worldlineClient->deleteToken($token);
            // response is always null in case of an error an Exception will be thrown (Api v1.0 Feb 2023). So no mapping necessary at this point
            $responseTransfer->setIsSuccess(true);
        } catch (Exception $exception) {
            $this->handleException($responseTransfer, $exception, $this->tokenMapper);
        }

        return $responseTransfer;
    }
}
