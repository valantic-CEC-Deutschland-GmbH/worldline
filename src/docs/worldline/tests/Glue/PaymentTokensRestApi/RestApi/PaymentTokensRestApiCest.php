<?php

declare(strict_types = 1);

namespace PyzTest\Glue\PaymentTokensRestApi\RestApi;

use Generated\Shared\Transfer\RestPaymentTokenAttributesTransfer;
use Generated\Shared\Transfer\RestPaymentTokensAttributesTransfer;
use PyzTest\Glue\PaymentTokensRestApi\PaymentTokensRestApiApiTester;
use PyzTest\Shared\Base\AbstractCest;
use Spryker\Glue\CustomersRestApi\CustomersRestApiConfig;


/**
 * Auto-generated group annotations
 *
 * @group PyzTest
 * @group Glue
 * @group PaymentTokens
 * @group RestApi
 * @group PaymentTokensRestApiCest
 * Add your own group annotations below this line
 * @group EndToEnd
 * @group Worldline
 */
class PaymentTokensRestApiCest extends AbstractCest
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

    /**
     * @param \PyzTest\Glue\PaymentTokensRestApi\PaymentTokensRestApiApiTester $I
     *
     * @return void
     */
    public function requestDeleteOnATokenThatWorldlineDoesntKnowReturnsAnError(PaymentTokensRestApiApiTester $I) : void
    {
        $customerTransfer = $this->fixtures->getCustomerTransfer();
        $token = $I->haveAuthorizationToGlue($customerTransfer)->getAccessToken();
        $I->amBearerAuthenticated($token);
        $I->sendDelete($this->createPaymentTokensUrl($I, $customerTransfer->getCustomerReference()) . '/' . $this->fixtures->getTokenTransfer()->getToken());

        $I->seeResponseCodeIs(404);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson();
    }

    /**
     * @param \PyzTest\Glue\PaymentTokensRestApi\PaymentTokensRestApiApiTester $I
     *
     * @return void
     */
    public function requestDeleteOnATokenThatSprykerDoesntKnowReturnsAnError(PaymentTokensRestApiApiTester $I) : void
    {
        $customerTransfer = $this->fixtures->getCustomerTransfer();
        $token = $I->haveAuthorizationToGlue($customerTransfer)->getAccessToken();
        $I->amBearerAuthenticated($token);
        $I->sendDelete($this->createPaymentTokensUrl($I, $customerTransfer->getCustomerReference()) . '/unknownID');

        $I->seeResponseCodeIs(404);
        $I->seeResponseIsJson();
        $I->seeResponseContains('Token not found.');
    }



    /**
     * @param \PyzTest\Glue\PaymentTokensRestApi\PaymentTokensRestApiApiTester $I
     *
     * @return void
     */
    public function requestGetTokensForIllegalCustomerReferenceReturnsAnForbiddenError(PaymentTokensRestApiApiTester $I) : void
    {
        $token = $I->haveAuthorizationToGlue($this->fixtures->getCustomerTransfer())->getAccessToken();
        $I->amBearerAuthenticated($token);
        $customerTransfer = $this->fixtures->getCustomerTransfer();
        $tokenTransfer = $this->fixtures->getTokenTransfer();

        $customerReference = 'thisIsNotAValidCustomerRef';
        $I->sendGet(
            $this->createPaymentTokensUrl($I, $customerReference),
        );

        $I->seeResponseCodeIs(403);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson();
        //$I->seeResponseDataContainsResourceCollectionOfType('errors');
        $I->seeResponseContainsJson(
            array(
                'code' => 411,
                'detail' => 'Unauthorized request.',
            )
        );
    }



    /**
     * @param \PyzTest\Glue\PaymentTokensRestApi\PaymentTokensRestApiApiTester $I
     *
     * @return void
     */
    public function requestGetTokensKnownCustomerReferenceReturnsToken(PaymentTokensRestApiApiTester $I) : void
    {
        $token = $I->haveAuthorizationToGlue($this->fixtures->getCustomerTransfer())->getAccessToken();
        $I->amBearerAuthenticated($token);
        $customerTransfer = $this->fixtures->getCustomerTransfer();
        $tokenTransfer = $this->fixtures->getTokenTransfer();

        $I->sendGet(
            $this->createPaymentTokensUrl($I, $customerTransfer->getCustomerReference()),
        );

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson();
        $I->seeResponseDataContainsResourceCollectionOfType('payment-tokens');
        $I->seeResponseContainsJson(
            array(
                'attributes' => array(
                    RestPaymentTokenAttributesTransfer::TOKEN => $tokenTransfer->getToken(),
                    RestPaymentTokenAttributesTransfer::EXPIRY_MONTH => $tokenTransfer->getExpiryMonth(),
                    RestPaymentTokenAttributesTransfer::OBFUSCATED_CARD_NUMBER => $tokenTransfer->getObfuscatedCardNumber(),
                    RestPaymentTokenAttributesTransfer::HOLDER_NAME => $tokenTransfer->getHolderName(),
                    //RestPaymentTokenAttributesTransfer::PAYMENT_METHOD => $tokenTransfer->getPaymentMethodName(),
                    RestPaymentTokenAttributesTransfer::EXPIRED_AT => $tokenTransfer->getExpiredAt(),
                    RestPaymentTokenAttributesTransfer::DELETED_AT => $tokenTransfer->getDeletedAt(),
                )
            )

        );
    }

    /**
     * @param PaymentTokensRestApiApiTester $I
     * @param string $customerReference
     *
     * @return string
     */
    private function createPaymentTokensUrl(PaymentTokensRestApiApiTester $I, string $customerReference): string
    {
        return $I->formatUrl(
            '{resourceCustomers}/{customerReference}/payment-tokens',
            [
                'resourceCustomers' => CustomersRestApiConfig::RESOURCE_CUSTOMERS,
                'customerReference' => $customerReference,
            ],
        );
    }
}
