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
class GetPaymentStatusCommandPlugin extends AbstractCommandPlugin implements CommandByOrderInterface
{
    /**
     * @param array $orderItems
     * @param \Orm\Zed\Sales\Persistence\SpySalesOrder $orderEntity
     * @param \Spryker\Zed\Oms\Business\Util\ReadOnlyArrayObject $data
     *
     * @return array
     */
    public function run(array $orderItems, SpySalesOrder $orderEntity, ReadOnlyArrayObject $data): array
    {
        $this->getFacade()->handleGetPaymentStatusCommand($orderItems, $this->getOrderTransfer($orderEntity));

        return [];
    }
}
