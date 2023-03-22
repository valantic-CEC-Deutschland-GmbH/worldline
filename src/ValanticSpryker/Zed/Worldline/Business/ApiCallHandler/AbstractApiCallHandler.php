<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\ApiCallHandler;

use Exception;
use Spryker\Shared\Kernel\Transfer\TransferInterface;
use ValanticSpryker\Zed\Worldline\Business\Logger\WorldlineApiLoggerInterface;
use ValanticSpryker\Zed\Worldline\Business\Mapper\WorldlineMapperInterface;

abstract class AbstractApiCallHandler implements ApiCallHandlerInterface
{
    /**
     * @param \ValanticSpryker\Zed\Worldline\Business\Logger\WorldlineApiLoggerInterface $apiLogger
     */
    public function __construct(private WorldlineApiLoggerInterface $apiLogger)
    {
    }

    /**
     * @param \Spryker\Shared\Kernel\Transfer\TransferInterface $responseTransfer
     * @param \Exception $exception
     * @param \ValanticSpryker\Zed\Worldline\Business\Mapper\WorldlineMapperInterface $errorMapper
     *
     * @return \Spryker\Shared\Kernel\Transfer\TransferInterface
     */
    public function handleException(TransferInterface $responseTransfer, Exception $exception, WorldlineMapperInterface $errorMapper): TransferInterface
    {
        if (method_exists($responseTransfer, 'setHttpStatusCode') && method_exists($exception, 'getHttpStatusCode')) {
            $responseTransfer->setHttpStatusCode($exception->getHttpStatusCode());
        }

        if (method_exists($responseTransfer, 'setIsSuccess')) {
            $responseTransfer->setIsSuccess(false);
        }
        if (method_exists($responseTransfer, 'setErrorId') && method_exists($exception, 'getErrorId')) {
            $responseTransfer->setErrorId($exception->getErrorId());
        }

        if (method_exists($responseTransfer, 'addError') && method_exists($exception, 'getErrors')) {
            foreach ($exception->getErrors() as $ingenicoError) {
                $responseTransfer->addError($errorMapper->mapIngenicoErrorToWorldlineErrorItem($ingenicoError));
            }
        }

        return $responseTransfer;
    }

    /**
     * @param \Spryker\Shared\Kernel\Transfer\TransferInterface $transfer
     *
     * @return \Spryker\Shared\Kernel\Transfer\TransferInterface
     */
    public function handleApiCall(TransferInterface $transfer): TransferInterface
    {
        $vsyWorldlineApiLogEntity = $this->apiLogger->logApiCallStart($transfer);
        $responseTransfer = $this->handleApiCallImpl($transfer);
        $this->apiLogger->logApiCallEnd($responseTransfer, $vsyWorldlineApiLogEntity);

        return $responseTransfer;
    }

    /**
     * @param \Spryker\Shared\Kernel\Transfer\TransferInterface $transfer
     *
     * @return \Spryker\Shared\Kernel\Transfer\TransferInterface
     */
    abstract protected function handleApiCallImpl(TransferInterface $transfer): TransferInterface;
}
