<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\Logger;

use Orm\Zed\Worldline\Persistence\VsyPaymentWorldlineTransactionStatusLog;
use Orm\Zed\Worldline\Persistence\VsyWorldlineApiLog;
use Spryker\Shared\Kernel\Transfer\TransferInterface;

class WorldlineApiLogger implements WorldlineApiLoggerInterface
{
    /**
     * @param array<\ValanticSpryker\Zed\Worldline\Business\Logger\WorldlineApiLoggerPluginInterface> $apiLoggerPlugins
     */
    public function __construct(private array $apiLoggerPlugins)
    {
    }

    /**
     * @inheritDoc
     */
    public function logApiCallStart(TransferInterface $transfer): VsyWorldlineApiLog
    {
        $apiLogEntity = new VsyWorldlineApiLog();

        foreach ($this->apiLoggerPlugins as $apiLoggerPlugin) {
            $apiLoggerPlugin->logApiCallStart($transfer, $apiLogEntity);
        }

        return $apiLogEntity;
    }

    /**
     * @inheritDoc
     */
    public function logApiCallEnd(TransferInterface $transfer, VsyWorldlineApiLog $apiLogEntity): void
    {
        $transactionStatusLogEntity = new VsyPaymentWorldlineTransactionStatusLog();
        foreach ($this->apiLoggerPlugins as $apiLoggerPlugin) {
            $apiLoggerPlugin->logApiCallEnd($transfer, $apiLogEntity, $transactionStatusLogEntity);
        }

        if ($apiLogEntity->isNew() || $apiLogEntity->isModified()) {
            $apiLogEntity->save();
        }

        if ($transactionStatusLogEntity->isModified()) {
            $transactionStatusLogEntity->setFkWorldlineApiLog($apiLogEntity->getIdWorldlineApiLog());
            $transactionStatusLogEntity->save();
        }
    }
}
