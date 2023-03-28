<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\Logger;

use Orm\Zed\Worldline\Persistence\VsyPaymentWorldlineTransactionStatusLog;
use Orm\Zed\Worldline\Persistence\VsyWorldlineApiLog;
use Spryker\Shared\Kernel\Transfer\TransferInterface;
use ValanticSpryker\Zed\Worldline\WorldlineConfig;

class ApiSettingsLogger implements WorldlineApiLoggerPluginInterface
{
    /**
     * @param \ValanticSpryker\Zed\Worldline\WorldlineConfig $config
     */
    public function __construct(private WorldlineConfig $config)
    {
    }

    /**
     * @param \Spryker\Shared\Kernel\Transfer\TransferInterface $transfer
     * @param \Orm\Zed\Worldline\Persistence\VsyWorldlineApiLog $worldlineApiLogEntity
     *
     * @return void
     */
    public function logApiCallStart(TransferInterface $transfer, VsyWorldlineApiLog $worldlineApiLogEntity): void
    {
        $worldlineApiLogEntity
            ->setApiKey($this->config->getApiKey())
            ->setMerchantId($this->config->getMerchantId())
            ->setApiEndpoint($this->config->getApiEndpoint());
    }

    /**
     * @param \Spryker\Shared\Kernel\Transfer\TransferInterface $transfer
     * @param \Orm\Zed\Worldline\Persistence\VsyWorldlineApiLog $apiLogEntity
     * @param \Orm\Zed\Worldline\Persistence\VsyPaymentWorldlineTransactionStatusLog $transactionStatusLogEntity
     *
     * @return void
     */
    public function logApiCallEnd(
        TransferInterface $transfer,
        VsyWorldlineApiLog $apiLogEntity,
        VsyPaymentWorldlineTransactionStatusLog $transactionStatusLogEntity
    ): void {
    }
}
