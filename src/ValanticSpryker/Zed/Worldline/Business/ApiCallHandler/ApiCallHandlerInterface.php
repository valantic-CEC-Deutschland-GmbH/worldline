<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\ApiCallHandler;

use Exception;
use ValanticSpryker\Zed\Worldline\Business\Mapper\WorldlineMapperInterface;
use Spryker\Shared\Kernel\Transfer\TransferInterface;

interface ApiCallHandlerInterface
{
    /**
     * @param \Spryker\Shared\Kernel\Transfer\TransferInterface $transfer
     *
     * @return \Spryker\Shared\Kernel\Transfer\TransferInterface
     */
    public function handleApiCall(TransferInterface $transfer): TransferInterface;

    /**
     * @param \Spryker\Shared\Kernel\Transfer\TransferInterface $responseTransfer
     * @param \Exception $exception
     * @param \ValanticSpryker\Zed\Worldline\Business\Mapper\WorldlineMapperInterface $errorMapper
     *
     * @return \Spryker\Shared\Kernel\Transfer\TransferInterface
     */
    public function handleException(TransferInterface $responseTransfer, Exception $exception, WorldlineMapperInterface $errorMapper): TransferInterface;
}
