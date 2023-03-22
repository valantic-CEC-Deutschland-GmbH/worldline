<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Persistence;

use Orm\Zed\Worldline\Persistence\VsyPaymentWorldlineHostedCheckoutQuery;
use Orm\Zed\Worldline\Persistence\VsyPaymentWorldlineQuery;
use Orm\Zed\Worldline\Persistence\VsyPaymentWorldlineTransactionStatusLogQuery;
use Orm\Zed\Worldline\Persistence\VsyWorldlineApiCallLogQuery;
use Orm\Zed\Worldline\Persistence\VsyWorldlineRestReceiveLogQuery;
use Orm\Zed\Worldline\Persistence\VsyWorldlineTokenQuery;
use ValanticSpryker\Zed\Worldline\Persistence\Mapper\WorldlinePersistenceMapper;
use ValanticSpryker\Zed\Worldline\Persistence\Mapper\WorldlinePersistenceMapperInterface;
use Spryker\Zed\Kernel\Persistence\AbstractPersistenceFactory;

/**
 * @method \ValanticSpryker\Zed\Worldline\Persistence\WorldlineEntityManagerInterface getEntityManager()
 * @method \ValanticSpryker\Zed\Worldline\Persistence\WorldlineQueryContainerInterface getQueryContainer()
 * @method \ValanticSpryker\Zed\Worldline\Persistence\WorldlineRepositoryInterface getRepository()
 * @method \ValanticSpryker\Zed\Worldline\WorldlineConfig getConfig()
 */
class WorldlinePersistenceFactory extends AbstractPersistenceFactory
{
    /**
     * @return \Orm\Zed\Worldline\Persistence\VsyWorldlineApiCallLogQuery
     */
    public function createWorldlineApiCallLogQuery(): VsyWorldlineApiCallLogQuery
    {
        return VsyWorldlineApiCallLogQuery::create();
    }

    /**
     * @return \Orm\Zed\Worldline\Persistence\VsyPaymentWorldlineQuery
     */
    public function createVsyPaymentWorldlineQuery(): VsyPaymentWorldlineQuery
    {
        return VsyPaymentWorldlineQuery::create();
    }

    /**
     * @return \ValanticSpryker\Zed\Worldline\Persistence\Mapper\WorldlinePersistenceMapperInterface
     */
    public function createWorldlinePersistenceMapper(): WorldlinePersistenceMapperInterface
    {
        return new WorldlinePersistenceMapper();
    }

    /**
     * @return \Orm\Zed\Worldline\Persistence\VsyPaymentWorldlineHostedCheckoutQuery
     */
    public function createVsyPaymentWorldlineHostedCheckoutQuery(): VsyPaymentWorldlineHostedCheckoutQuery
    {
        return VsyPaymentWorldlineHostedCheckoutQuery::create();
    }

    /**
     * @return \Orm\Zed\Worldline\Persistence\VsyWorldlineRestReceiveLogQuery
     */
    public function createVsyWorldlineRestReceiveLogQuery(): VsyWorldlineRestReceiveLogQuery
    {
        return VsyWorldlineRestReceiveLogQuery::create();
    }

    /**
     * @return \Orm\Zed\Worldline\Persistence\VsyPaymentWorldlineTransactionStatusLogQuery
     */
    public function createVsyPaymentWorldlineTransactionLogQuery(): VsyPaymentWorldlineTransactionStatusLogQuery
    {
        return VsyPaymentWorldlineTransactionStatusLogQuery::create();
    }

    /**
     * @return \Orm\Zed\Worldline\Persistence\VsyWorldlineTokenQuery
     */
    public function createVsyWorldlineTokenQuery(): VsyWorldlineTokenQuery
    {
        return VsyWorldlineTokenQuery::create();
    }
}
