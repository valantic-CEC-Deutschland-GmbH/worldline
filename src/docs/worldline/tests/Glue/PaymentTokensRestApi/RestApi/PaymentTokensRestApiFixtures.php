<?php

declare(strict_types = 1);

namespace PyzTest\Glue\PaymentTokensRestApi\RestApi;

use Generated\Shared\Transfer\CustomerTransfer;
use Generated\Shared\Transfer\WorldlineCreditCardTokenTransfer;
use PyzTest\Glue\PaymentTokensRestApi\PaymentTokensRestApiApiTester;
use SprykerTest\Shared\Testify\Fixtures\FixturesBuilderInterface;
use SprykerTest\Shared\Testify\Fixtures\FixturesContainerInterface;

class PaymentTokensRestApiFixtures implements FixturesBuilderInterface, FixturesContainerInterface
{
    private const USERNAME = 'TEST_CUSTOMER_TOKEN';

    private const PASSWORD = 'change123';

    private CustomerTransfer $customerTransfer;

    /** @var \Generated\Shared\Transfer\WorldlineCreditCardTokenTransfer  */
    private WorldlineCreditCardTokenTransfer $tokenCardDataTransfer;

    /**
     * @return \Generated\Shared\Transfer\WorldlineCreditCardTokenTransfer
     */
    public function getTokenTransfer(): WorldlineCreditCardTokenTransfer
    {
        return $this->tokenCardDataTransfer;
    }

    /**
     * @return \Generated\Shared\Transfer\CustomerTransfer
     */
    public function getCustomerTransfer(): CustomerTransfer
    {
        return $this->customerTransfer;
    }

    /**
     * @param \PyzTest\Glue\PaymentTokensRestApi\PaymentTokensRestApiApiTester $apiTester
     *
     * @return \SprykerTest\Shared\Testify\Fixtures\FixturesContainerInterface
     */
    public function buildFixtures(PaymentTokensRestApiApiTester $apiTester): FixturesContainerInterface
    {
        $this->addCustomerTransfer($apiTester);
        $this->addTokenCardTransfer($apiTester);

        return $this;
    }

    /**
     * @param \PyzTest\Glue\PaymentTokensRestApi\PaymentTokensRestApiApiTester $apiTester
     *
     * @return void
     */
    private function addCustomerTransfer(PaymentTokensRestApiApiTester $apiTester): void
    {
        $this->customerTransfer = $apiTester->createCustomerTransfer(self::USERNAME, self::PASSWORD);
    }

    /**
     * @param \PyzTest\Glue\PaymentTokensRestApi\PaymentTokensRestApiApiTester $apiTester
     *
     * @return void
     */
    private function addTokenCardTransfer(PaymentTokensRestApiApiTester $apiTester): void
    {
        $this->tokenCardDataTransfer = $apiTester->havePaymentTokenWithThreeDSecureDataForCustomer($this->customerTransfer);
    }
}
