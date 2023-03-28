<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Communication\Table;

use Orm\Zed\Worldline\Persistence\Map\VsyPaymentWorldlineTransactionStatusLogTableMap;
use Orm\Zed\Worldline\Persistence\VsyPaymentWorldlineQuery;
use Orm\Zed\Worldline\Persistence\VsyPaymentWorldlineTransactionStatusLogQuery;
use Spryker\Service\UtilDateTime\UtilDateTimeServiceInterface;
use Spryker\Service\UtilText\Model\Url\Url;
use Spryker\Zed\Gui\Communication\Table\AbstractTable;
use Spryker\Zed\Gui\Communication\Table\TableConfiguration;

class TransactionStatusLogTable extends AbstractTable
{
    private const URL_WORLDLINE_SALES_LIST_TABLE = '/../../worldline/sales/list-table';

    /**
     * @param \Orm\Zed\Worldline\Persistence\VsyPaymentWorldlineTransactionStatusLogQuery $transactionStatusLogQuery
     * @param \Orm\Zed\Worldline\Persistence\VsyPaymentWorldlineQuery $paymentWorldlineQuery
     * @param \Spryker\Service\UtilDateTime\UtilDateTimeServiceInterface $utilDateTimeService
     * @param string $orderReference
     */
    public function __construct(
        private VsyPaymentWorldlineTransactionStatusLogQuery $transactionStatusLogQuery,
        private VsyPaymentWorldlineQuery $paymentWorldlineQuery,
        private UtilDateTimeServiceInterface $utilDateTimeService,
        private string $orderReference
    ) {
    }

    /**
     * @inheritDoc
     */
    protected function configure(TableConfiguration $config)
    {
        $url = Url::generate(self::URL_WORLDLINE_SALES_LIST_TABLE, ['orderReference' => $this->orderReference])->build();

        $config->setUrl($url);

        $config->setHeader([
            VsyPaymentWorldlineTransactionStatusLogTableMap::COL_ID_PAYMENT_WORLDLINE_TRANSACTION_STATUS_LOG => 'Log ID',
            VsyPaymentWorldlineTransactionStatusLogTableMap::COL_STATUS => 'Status',
            VsyPaymentWorldlineTransactionStatusLogTableMap::COL_STATUS_CATEGORY => 'Status Category',
            VsyPaymentWorldlineTransactionStatusLogTableMap::COL_STATUS_CODE => 'Status Code',
            VsyPaymentWorldlineTransactionStatusLogTableMap::COL_STATUS_CODE_CHANGE_DATE_TIME => 'Status Code Changed',
            VsyPaymentWorldlineTransactionStatusLogTableMap::COL_UPDATED_AT => 'Updated at',
        ]);

        $config->setSearchable([
            VsyPaymentWorldlineTransactionStatusLogTableMap::COL_STATUS,
            VsyPaymentWorldlineTransactionStatusLogTableMap::COL_STATUS_CODE,
        ]);

        $config->setSortable([
            VsyPaymentWorldlineTransactionStatusLogTableMap::COL_ID_PAYMENT_WORLDLINE_TRANSACTION_STATUS_LOG,
            VsyPaymentWorldlineTransactionStatusLogTableMap::COL_STATUS_CODE_CHANGE_DATE_TIME,
            VsyPaymentWorldlineTransactionStatusLogTableMap::COL_UPDATED_AT,
        ]);

        $config->setDefaultSortField(
            VsyPaymentWorldlineTransactionStatusLogTableMap::COL_ID_PAYMENT_WORLDLINE_TRANSACTION_STATUS_LOG,
            TableConfiguration::SORT_DESC,
        );

        return $config;
    }

    /**
     * @inheritDoc
     */
    protected function prepareData(TableConfiguration $config)
    {
        $result = [];

        $paymentWorldlineEntity = $this->paymentWorldlineQuery->findOneByMerchantReference($this->orderReference);

        if (!$paymentWorldlineEntity) {
            return $result;
        }

        $idPaymanetWorldline = $paymentWorldlineEntity->getIdPaymentWorldline();

        $this->transactionStatusLogQuery->filterByFkPaymentWorldline($idPaymanetWorldline);

        /** @var array<\Orm\Zed\Worldline\Persistence\Base\VsyPaymentWorldlineTransactionStatusLog> $transactionStatusLogEntities */
        $transactionStatusLogEntities = $this->runQuery($this->transactionStatusLogQuery, $config, true);

        foreach ($transactionStatusLogEntities as $transactionStatusLogEntity) {
            $rowData = [
                VsyPaymentWorldlineTransactionStatusLogTableMap::COL_ID_PAYMENT_WORLDLINE_TRANSACTION_STATUS_LOG => $transactionStatusLogEntity->getIdPaymentWorldlineTransactionStatusLog(),
                VsyPaymentWorldlineTransactionStatusLogTableMap::COL_STATUS => $transactionStatusLogEntity->getStatus(),
                VsyPaymentWorldlineTransactionStatusLogTableMap::COL_STATUS_CATEGORY => $transactionStatusLogEntity->getStatusCategory(),
                VsyPaymentWorldlineTransactionStatusLogTableMap::COL_STATUS_CODE => $transactionStatusLogEntity->getStatusCode() === 150 ? 'TIMEOUT(' . $transactionStatusLogEntity->getStatusCode() . ')' : '' . $transactionStatusLogEntity->getStatusCode(),
                VsyPaymentWorldlineTransactionStatusLogTableMap::COL_STATUS_CODE_CHANGE_DATE_TIME => $this->utilDateTimeService->formatDateTime($transactionStatusLogEntity->getStatusCodeChangeDateTime()),
                VsyPaymentWorldlineTransactionStatusLogTableMap::COL_UPDATED_AT => $this->utilDateTimeService->formatDateTime($transactionStatusLogEntity->getUpdatedAt()),
            ];

            $result[] = $rowData;
        }

        return $result;
    }
}
