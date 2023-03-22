<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\Oms\Condition;

use DateInterval;
use DateTime;
use DateTimeZone;
use Generated\Shared\Transfer\ItemTransfer;
use Orm\Zed\Sales\Persistence\SpySalesOrderItem;
use Spryker\Zed\Oms\Business\OmsFacadeInterface;
use ValanticSpryker\Zed\Worldline\Business\Reader\WorldlineReaderInterface;
use ValanticSpryker\Zed\Worldline\WorldlineConfig;

class PaymentCaptureTimedOutChecker implements PaymentCaptureTimedOutCheckerInterface
{
    private const STATE_CAPTURE_RESTARTED = 'capture restarted';

    /**
     * @param \ValanticSpryker\Zed\Worldline\Business\Reader\WorldlineReaderInterface $worldlineReader
     * @param \Spryker\Zed\Oms\Business\OmsFacadeInterface $omsFacade
     * @param \ValanticSpryker\Zed\Worldline\WorldlineConfig $worldlineConfig
     */
    public function __construct(private WorldlineReaderInterface $worldlineReader, private OmsFacadeInterface $omsFacade, private WorldlineConfig $worldlineConfig)
    {
    }

    /**
     * @param \Orm\Zed\Sales\Persistence\SpySalesOrderItem $orderItem
     *
     * @return bool
     */
    public function isPaymentCaptureTimedOut(SpySalesOrderItem $orderItem): bool
    {
        $transactionStatusTransfer = $this->worldlineReader->getLatestPaymentWorldlineTransactionStatusLogByOrderItem($orderItem);
        $lastStatusCodeChangeDateTime = $transactionStatusTransfer->getStatusCodeChangeDateTime();
        $dateTimeObject = DateTime::createFromFormat('YmdHis', $lastStatusCodeChangeDateTime, new DateTimeZone('UTC'));
        $dateTimeNow = new DateTime('now', new DateTimeZone('UTC'));

        return (
        ($this->getTimeDifferenceInHours($dateTimeNow->diff($dateTimeObject)) >= $this->worldlineConfig->getMaxHoursToWaitBeforeCaptureTimesOut())
            && !$this->checkIfCaptureWasRestartedInTheLast24Hours($orderItem)
        );
    }

    /**
     * @param \Orm\Zed\Sales\Persistence\SpySalesOrderItem $orderItem
     *
     * @return bool
     */
    private function checkIfCaptureWasRestartedInTheLast24Hours(SpySalesOrderItem $orderItem): bool
    {
        $itemTransfer = new ItemTransfer();
        $itemTransfer->setIdSalesOrderItem($orderItem->getIdSalesOrderItem());

        $itemTransfers = $this->omsFacade->expandOrderItemsWithStateHistory([$itemTransfer]);
        $dateTimeNow = new DateTime('now', new DateTimeZone('UTC'));

        $latest = null;
        foreach ($itemTransfers as $itemTransfer) {
            $stateHistory = $itemTransfer->getStateHistory();
            foreach ($stateHistory as $itemStateTransfer) {
                if ($itemStateTransfer->getName() === self::STATE_CAPTURE_RESTARTED) {
                    $dateTimeObject = DateTime::createFromFormat('Y-m-d H:i:s.u', $itemStateTransfer->getCreatedAt(), new DateTimeZone('UTC'));

                    if (!$latest || $dateTimeObject > $latest) {
                        $latest = $dateTimeObject;
                    }
                }
            }
        }

        return $latest && ($this->getTimeDifferenceInHours($dateTimeNow->diff($latest)) < $this->worldlineConfig->getMaxHoursToWaitBeforeCaptureTimesOut());
    }

    /**
     * @param \DateInterval $interval
     *
     * @return int
     */
    private function getTimeDifferenceInHours(DateInterval $interval): int
    {
        return $interval->d * 24 + $interval->h;
    }
}
