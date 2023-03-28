<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\Oms\Condition;

use DateTime;
use Generated\Shared\Transfer\OrderTransfer;
use Orm\Zed\Sales\Persistence\SpySalesOrderItem;
use ValanticSpryker\Zed\Worldline\Business\Reader\WorldlineReaderInterface;
use ValanticSpryker\Zed\Worldline\WorldlineConfig;

class HostedCheckoutStatusTimeoutChecker implements HostedCheckoutStatusTimeoutCheckerInterface
{
    /**
     * @param \ValanticSpryker\Zed\Worldline\Business\Reader\WorldlineReaderInterface $worldlineReader
     * @param \ValanticSpryker\Zed\Worldline\WorldlineConfig $worldlineConfig
     */
    public function __construct(private WorldlineReaderInterface $worldlineReader, private WorldlineConfig $worldlineConfig)
    {
    }

    /**
     * @inheritDoc
     */
    public function isHostedCheckoutStatusTakingTooLong(SpySalesOrderItem $orderItem): bool
    {
        $orderTransfer = new OrderTransfer();
        $orderTransfer->setIdSalesOrder($orderItem->getFkSalesOrder());

        $paymentWorldlineTransfer = $this->worldlineReader->getPaymentWorldlineByIdSalesOrder($orderTransfer->getIdSalesOrder());
        $createdAtString = $paymentWorldlineTransfer->getPaymentHostedCheckout()->getCreatedAt();
        if (!$createdAtString) {
            return false;
        }

        $createdAt = new DateTime($createdAtString);

        $now = new DateTime();

        return ($now->getTimestamp() - $createdAt->getTimestamp()) > $this->worldlineConfig->getHostedCheckoutAllowedMaxDurationInSeconds();
    }
}
