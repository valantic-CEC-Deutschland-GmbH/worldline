<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\Mapper;

use Generated\Shared\Transfer\WorldlineErrorItemTransfer;
use Ingenico\Connect\Sdk\DataObject;
use Ingenico\Connect\Sdk\Domain\Errors\Definitions\APIError;
use Spryker\Shared\Kernel\Transfer\TransferInterface;

interface WorldlineMapperInterface
{
    /**
     * @param \Ingenico\Connect\Sdk\Domain\Errors\Definitions\APIError $ingenicoError
     *
     * @return \Generated\Shared\Transfer\WorldlineErrorItemTransfer
     */
    public function mapIngenicoErrorToWorldlineErrorItem(APIError $ingenicoError): WorldlineErrorItemTransfer;

    /**
     * @param \Ingenico\Connect\Sdk\DataObject $dataObject
     * @param \Spryker\Shared\Kernel\Transfer\TransferInterface $responseTransfer
     *
     * @return void
     */
    public function genericallyMapWorldlineResponseToSprykerResponseTransfer(DataObject $dataObject, TransferInterface $responseTransfer): void;
}
