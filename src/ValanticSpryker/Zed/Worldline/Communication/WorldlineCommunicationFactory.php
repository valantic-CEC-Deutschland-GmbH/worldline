<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Communication;

use Generated\Shared\Transfer\OrderTransfer;
use ValanticSpryker\Zed\Worldline\Communication\Form\PaymentTokenDeleteForm;
use ValanticSpryker\Zed\Worldline\Communication\Table\TransactionStatusLogTable;
use ValanticSpryker\Zed\Worldline\WorldlineDependencyProvider;
use Spryker\Service\UtilDateTime\UtilDateTimeServiceInterface;
use Spryker\Zed\Kernel\Communication\AbstractCommunicationFactory;
use Spryker\Zed\Router\Business\RouterFacadeInterface;
use Spryker\Zed\Sales\Business\SalesFacadeInterface;
use Symfony\Component\Form\FormInterface;

/**
 * @method \ValanticSpryker\Zed\Worldline\Persistence\WorldlineQueryContainerInterface getQueryContainer()
 * @method \ValanticSpryker\Zed\Worldline\Business\WorldlineFacadeInterface getFacade()
 * @method \ValanticSpryker\Zed\Worldline\WorldlineConfig getConfig()
 * @method \ValanticSpryker\Zed\Worldline\Persistence\WorldlineEntityManagerInterface getEntityManager()
 * @method \ValanticSpryker\Zed\Worldline\Persistence\WorldlineRepositoryInterface getRepository()
 */
class WorldlineCommunicationFactory extends AbstractCommunicationFactory
{
    /**
     * @return \Spryker\Zed\Sales\Business\SalesFacadeInterface
     */
    public function getSalesFacade(): SalesFacadeInterface
    {
        return $this->getProvidedDependency(WorldlineDependencyProvider::FACADE_SALES);
    }

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return \ValanticSpryker\Zed\Worldline\Communication\Table\TransactionStatusLogTable
     */
    public function createTransactionStatusLogTable(OrderTransfer $orderTransfer): TransactionStatusLogTable
    {
        $queryTransactionStatusLog = $this->getQueryContainer()->queryTransactionStatusLog();

        return new TransactionStatusLogTable($queryTransactionStatusLog, $this->getQueryContainer()->queryPaymentWorldline(), $this->getUtilDateTimeService(), $orderTransfer->getOrderReference());
    }

    /**
     * @return \Spryker\Service\UtilDateTime\UtilDateTimeServiceInterface
     */
    private function getUtilDateTimeService(): UtilDateTimeServiceInterface
    {
        return $this->getProvidedDependency(WorldlineDependencyProvider::SERVICE_UTIL_DATE_TIME);
    }

    /**
     * @return \Symfony\Component\Form\FormInterface
     */
    public function getPaymentTokenDeleteForm(): FormInterface
    {
        return $this->getFormFactory()->create(PaymentTokenDeleteForm::class);
    }

    /**
     * @return \Spryker\Zed\Router\Business\RouterFacadeInterface
     */
    public function getRouterFacade(): RouterFacadeInterface
    {
        return $this->getProvidedDependency(WorldlineDependencyProvider::FACADE_ROUTER);
    }
}
