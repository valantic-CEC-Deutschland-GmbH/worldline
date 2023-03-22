<?php

declare(strict_types = 1);

namespace ValanticSpryker\Client\PaymentTokens;

use Spryker\Client\Kernel\AbstractDependencyProvider;
use Spryker\Client\Kernel\Container;
use Spryker\Client\ZedRequest\ZedRequestClientInterface;

class PaymentTokensDependencyProvider extends AbstractDependencyProvider
{
    public const CLIENT_ZED_REQUEST = 'CLIENT_ZED_REQUEST';

    /**
     * @param \Spryker\Client\Kernel\Container $container
     *
     * @return \Spryker\Client\Kernel\Container
     */
    public function provideServiceLayerDependencies(Container $container): Container
    {
        $container = parent::provideServiceLayerDependencies($container);

        $container = $this->addZedRequestClient($container);

        return $container;
    }

    /**
     * @param \Spryker\Client\Kernel\Container $container
     *
     * @return \Spryker\Client\Kernel\Container
     */
    private function addZedRequestClient(Container $container): Container
    {
        $container->set(
            static::CLIENT_ZED_REQUEST,
            fn (Container $container): ZedRequestClientInterface => $container->getLocator()->zedRequest()->client(),
        );

        return $container;
    }
}
