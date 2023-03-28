<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\Mapper;

use Generated\Shared\Transfer\WorldlineErrorItemTransfer;
use Ingenico\Connect\Sdk\DataObject;
use Ingenico\Connect\Sdk\Domain\Errors\Definitions\APIError;
use Spryker\Shared\Kernel\Transfer\TransferInterface;
use stdClass;

class AbstractWorldlineMapper implements WorldlineMapperInterface
{
    /**
     * @param \Ingenico\Connect\Sdk\Domain\Errors\Definitions\APIError $ingenicoError
     *
     * @return \Generated\Shared\Transfer\WorldlineErrorItemTransfer
     */
    public function mapIngenicoErrorToWorldlineErrorItem(APIError $ingenicoError): WorldlineErrorItemTransfer
    {
        $ingenicoErrorJson = $ingenicoError->toJson();
        $ingenicoErrorArray = json_decode($ingenicoErrorJson, true);

        return (new WorldlineErrorItemTransfer())->fromArray($ingenicoErrorArray, true);
    }

    /**
     * @param \Ingenico\Connect\Sdk\DataObject $dataObject
     * @param \Spryker\Shared\Kernel\Transfer\TransferInterface $responseTransfer
     *
     * @return void
     */
    public function genericallyMapWorldlineResponseToSprykerResponseTransfer(DataObject $dataObject, TransferInterface $responseTransfer): void
    {
        $dataObjectsJson = $dataObject->toJson();
        $dataObjectsArray = json_decode($dataObjectsJson, true, 512, JSON_THROW_ON_ERROR);

        if (method_exists($responseTransfer, 'setHttpStatusCode')) {
            $responseTransfer->setHttpStatusCode(200);
        }
        $responseTransfer->fromArray($dataObjectsArray, true);
    }

    /**
     * @param array $array
     *
     * @return \stdClass
     */
    protected function toObject(array $array): stdClass
    {
        $object = new stdClass();
        foreach ($array as $property => $value) {
            if ($value === null) {
                unset($array[$property]);

                continue;
            }
            if (is_array($value)) {
                $value = $this->toObject($value);
            }
            $object->{$property} = $value;
        }

        return $object;
    }
}
