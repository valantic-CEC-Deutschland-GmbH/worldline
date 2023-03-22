<?php

declare(strict_types = 1);

namespace ValanticSpryker\Glue\PaymentTokensRestApi;

use ValanticSpryker\Client\PaymentTokens\PaymentTokensClientInterface;
use Spryker\Glue\Kernel\AbstractBundleDependencyProvider;
use Spryker\Glue\Kernel\Container;

class PaymentTokensRestApiDependencyProvider extends AbstractBundleDependencyProvider
{
    public const CLIENT_PAYMENT_TOKENS = 'CLIENT_PAYMENT_TOKENS';

    /**
     * @param \Spryker\Glue\Kernel\Container $container
     *
     * @return \Spryker\Glue\Kernel\Container
     */
    public function provideDependencies(Container $container): Container
    {
        $container = parent::provideDependencies($container);
        $container = $this->addPaymentTokensClient($container);

        return $container;
    }

    /**
     * @param \Spryker\Glue\Kernel\Container $container
     *
     * @return \Spryker\Glue\Kernel\Container
     */
    private function addPaymentTokensClient(Container $container): Container
    {
        $container->set(
            static::CLIENT_PAYMENT_TOKENS,
            fn (Container $container): PaymentTokensClientInterface => $container->getLocator()->paymentTokens()->client(),
        );

        return $container;
    }
}
