<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Communication\Plugin\Oms\Command;

use Orm\Zed\Sales\Persistence\SpySalesOrder;
use Spryker\Zed\Oms\Business\Util\ReadOnlyArrayObject;
use Spryker\Zed\Oms\Dependency\Plugin\Command\CommandByOrderInterface;

/**
 * @method \ValanticSpryker\Zed\Worldline\Business\WorldlineFacadeInterface getFacade()
 * @method \ValanticSpryker\Zed\Worldline\Communication\WorldlineCommunicationFactory getFactory()
 * @method \ValanticSpryker\Zed\Worldline\Persistence\WorldlineQueryContainerInterface getQueryContainer()
 * @method \ValanticSpryker\Zed\Worldline\WorldlineConfig getConfig()
 */
class CreateHostedCheckoutCommandPlugin extends AbstractCommandPlugin implements CommandByOrderInterface
{
    /**
     * @inheritDoc
     */
    public function run(array $orderItems, SpySalesOrder $orderEntity, ReadOnlyArrayObject $data): array
    {
        $this->getFacade()->handleCreateHostedCheckoutCommand(
            $orderItems,
            $this->getOrderTransfer($orderEntity),
        );

        return [];
    }
}
