<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline;

use Spryker\Zed\Customer\Business\CustomerFacadeInterface;
use Spryker\Zed\Oms\Business\OmsFacadeInterface;
use Spryker\Zed\Payment\Business\PaymentFacadeInterface;
use Spryker\Service\UtilDateTime\UtilDateTimeServiceInterface;
use Spryker\Service\UtilEncoding\UtilEncodingServiceInterface;
use Spryker\Zed\Kernel\AbstractBundleDependencyProvider;
use Spryker\Zed\Kernel\Container;
use Spryker\Zed\Router\Business\RouterFacadeInterface;
use Spryker\Zed\Sales\Business\SalesFacadeInterface;

/**
 * @method \ValanticSpryker\Zed\Worldline\WorldlineConfig getConfig()
 */
class WorldlineDependencyProvider extends AbstractBundleDependencyProvider
{
    public const FACADE_SALES = 'FACADE_SALES';

    public const SERVICE_UTIL_DATE_TIME = 'SERVICE_UTIL_DATE_TIME';

    public const SERVICE_UTIL_ENCODING = 'SERVICE_UTIL_ENCODING';

    public const FACADE_OMS = 'FACADE_OMS';
    public const FACADE_PAYMENT = 'FACADE_PAYMENT';
    public const FACADE_CUSTOMER = 'FACADE_CUSTOMER';

    public const FACADE_ROUTER = 'FACADE_ROUTER';

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    public function provideCommunicationLayerDependencies(Container $container): Container
    {
        $container = parent::provideCommunicationLayerDependencies($container);

        $container = $this->addSalesFacade($container);
        $container = $this->addUtilDateTimeService($container);
        $container = $this->addRouterFacade($container);

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    public function provideBusinessLayerDependencies(Container $container): Container
    {
        $container = parent::provideBusinessLayerDependencies($container);

        $container = $this->addUtilEncodingService($container);
        $container = $this->addOmsFacade($container);
        $container = $this->addPaymentFacade($container);
        $container = $this->addCustomerFacade($container);

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    private function addSalesFacade(Container $container): Container
    {
        $container->set(
            static::FACADE_SALES,
            fn (Container $container): SalesFacadeInterface => $container->getLocator()->sales()->facade(),
        );

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    private function addUtilDateTimeService(Container $container): Container
    {
        $container->set(
            static::SERVICE_UTIL_DATE_TIME,
            fn (Container $container): UtilDateTimeServiceInterface => $container->getLocator()->utilDateTime()->service(),
        );

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    private function addUtilEncodingService(Container $container): Container
    {
        $container->set(
            static::SERVICE_UTIL_ENCODING,
            fn (Container $container): UtilEncodingServiceInterface => $container->getLocator()->utilEncoding()->service(),
        );

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    private function addOmsFacade(Container $container): Container
    {
        $container->set(
            static::FACADE_OMS,
            fn (Container $container): OmsFacadeInterface => $container->getLocator()->oms()->facade(),
        );

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    private function addPaymentFacade(Container $container): Container
    {
        $container->set(
            static::FACADE_PAYMENT,
            function (Container $container): PaymentFacadeInterface {
                return $container->getLocator()->payment()->facade();
            },
        );

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    private function addCustomerFacade(Container $container): Container
    {
        $container->set(
            static::FACADE_CUSTOMER,
            fn (Container $container): CustomerFacadeInterface => $container->getLocator()->customer()->facade(),
        );

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    private function addRouterFacade(Container $container): Container
    {
        $container->set(
            static::FACADE_ROUTER,
            fn (Container $container): RouterFacadeInterface => $container->getLocator()->router()->facade(),
        );

        return $container;
    }
}
