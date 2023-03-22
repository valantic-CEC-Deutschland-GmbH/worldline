<?php

declare(strict_types = 1);

namespace ValanticSpryker\Client\PaymentTokens;

use Spryker\Client\Kernel\AbstractFactory;
use Spryker\Client\ZedRequest\ZedRequestClientInterface;
use ValanticSpryker\Client\PaymentTokens\Zed\PaymentTokensStub;
use ValanticSpryker\Client\PaymentTokens\Zed\PaymentTokensStubInterface;

class PaymentTokensFactory extends AbstractFactory
{
    /**
     * @return \ValanticSpryker\Client\PaymentTokens\Zed\PaymentTokensStubInterface
     */
    public function createPaymentTokensStub(): PaymentTokensStubInterface
    {
        return new PaymentTokensStub($this->getZedRequestClient());
    }

    /**
     * @return \Spryker\Client\ZedRequest\ZedRequestClientInterface
     */
    private function getZedRequestClient(): ZedRequestClientInterface
    {
        return $this->getProvidedDependency(PaymentTokensDependencyProvider::CLIENT_ZED_REQUEST);
    }
}
