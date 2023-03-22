<?php

declare(strict_types = 1);

namespace PyzTest\Glue\PaymentTokensRestApi\RestApi;

use PyzTest\Glue\PaymentTokensRestApi\PaymentTokensRestApiApiTester;
use PyzTest\Shared\Base\AbstractCest;

/**
 * Auto-generated group annotations
 *
 * @group PyzTest
 * @group Glue
 * @group PaymentTokens
 * @group RestApi
 * @group CustomersRestApiCest
 * Add your own group annotations below this line
 * @group EndToEnd
 * @group Worldline
 */
class CustomersRestApiCest extends AbstractCest
{
    /**
     * @var \PyzTest\Glue\PaymentTokensRestApi\RestApi\PaymentTokensRestApiFixtures
     */
    protected $fixtures;

    /**
     * @param \PyzTest\Glue\PaymentTokensRestApi\PaymentTokensRestApiApiTester $I
     *
     * @return void
     */
    public function loadFixtures(PaymentTokensRestApiApiTester $I): void
    {
        /** @var \PyzTest\Glue\PaymentTokensRestApi\RestApi\PaymentTokensRestApiFixtures $fixtures */
        $fixtures = $I->loadFixtures(PaymentTokensRestApiFixtures::class);

        $this->fixtures = $fixtures;
    }

    public function testIncludePaymentTokensReturnsCustomersPaymentTokensWhenCustomerHasPaymentTokensAvailable(PaymentTokensRestApiApiTester $i)
    {
        $token = $i->haveAuthorizationToGlue($this->fixtures->getCustomerTransfer())->getAccessToken();
        $i->amBearerAuthenticated($token);

        $i->sendGet('customers/' . $this->fixtures->getCustomerTransfer()->getCustomerReference() . '?include=payment-tokens');

        $i->seeResponseCodeIs(200);
        $i->seeResponseContains($this->fixtures->getTokenTransfer()->getToken());
    }

}
