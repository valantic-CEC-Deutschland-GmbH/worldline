<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\Oms\Command;

use Generated\Shared\Transfer\OrderTransfer;
use ValanticSpryker\Zed\Worldline\Business\ApiCallHandler\ApiCallHandlerInterface;
use ValanticSpryker\Zed\Worldline\Business\Oms\Command\Mapper\WorldlineCommandMapperInterface;
use ValanticSpryker\Zed\Worldline\Business\Oms\Command\Saver\WorldlineCommandSaverInterface;

class GenericCommandHandler implements CommandHandlerInterface
{
    /**
     * @param \ValanticSpryker\Zed\Worldline\Business\Oms\Command\Mapper\WorldlineCommandMapperInterface $commandMapper
     * @param \ValanticSpryker\Zed\Worldline\Business\ApiCallHandler\ApiCallHandlerInterface $apiCallHandler
     * @param \ValanticSpryker\Zed\Worldline\Business\Oms\Command\Saver\WorldlineCommandSaverInterface $commandSaver
     */
    public function __construct(private WorldlineCommandMapperInterface $commandMapper, private ApiCallHandlerInterface $apiCallHandler, private WorldlineCommandSaverInterface $commandSaver)
    {
    }

    /**
     * @param array $orderItems
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return void
     */
    public function handle(array $orderItems, OrderTransfer $orderTransfer): void
    {
        $requestTransfer = $this->commandMapper->buildRequestTransfer($orderItems, $orderTransfer);

        $createHostedCheckoutResponseTransfer = $this->apiCallHandler->handleApiCall($requestTransfer);

        $this->commandSaver->save($createHostedCheckoutResponseTransfer, $orderTransfer);
    }
}
