<?php

declare(strict_types = 1);

namespace PyzTest\Glue\PaymentTokensRestApi\Processor;

use ArrayObject;
use Generated\Shared\DataBuilder\RestUserBuilder;
use Generated\Shared\Transfer\RestPaymentTokensAttributesTransfer;
use Generated\Shared\Transfer\RestUserTransfer;
use Generated\Shared\Transfer\WorldlineCreditCardTokenTransfer;
use Generated\Shared\Transfer\WorldlineDeleteTokenResponseTransfer;
use Generated\Shared\Transfer\WorldlineErrorItemTransfer;
use Generated\Shared\Transfer\WorldlinePaymentTokenErrorItemTransfer;
use Generated\Shared\Transfer\WorldlinePaymentTokensResponseTransfer;
use Pyz\Client\PaymentTokens\PaymentTokensClientInterface;
use Pyz\Glue\PaymentTokensRestApi\Processor\Mapper\RestPaymentTokensMapper;
use Pyz\Glue\PaymentTokensRestApi\Processor\PaymentTokensDeleter;
use Pyz\Glue\PaymentTokensRestApi\Processor\Reader\RestPaymentTokensReader;
use Pyz\Glue\PaymentTokensRestApi\Processor\ResponseBuilder\PaymentTokensRestResponseBuilder;
use Pyz\Glue\PaymentTokensRestApi\Validation\RestApiError;
use PyzTest\Glue\PaymentTokensRestApi\PaymentTokensRestApiTester;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResource;
use Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequest;
use PyzTest\Shared\Base\AbstractTest;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceBuilder;

/**
 * Auto-generated group annotations
 *
 * @group PyzTest
 * @group Glue
 * @group PaymentTokensRestApi
 * @group Processor
 * @group RestPaymentTokensReaderTest
 * Add your own group annotations below this line
 */
class RestPaymentTokensReaderTest extends AbstractTest
{

    /**
     * @var string
     */
    protected const TEST_USERNAME = 'user';

    /**
     * @var string
     */
    protected const TEST_PASSWORD = 'change123';

    /**
     * @var \PyzTest\Glue\PaymentTokensRestApi\PaymentTokensRestApiTester
     */
    protected PaymentTokensRestApiTester $tester;

    public function testGetPaymentTokensByRefReturnsTokens() : void
    {
        // Prepare
        $customerWithOrders = $this->tester->createCustomerTransfer(static::TEST_USERNAME, static::TEST_PASSWORD);
        $tokenTransfer = $this->tester->havePaymentTokenWithThreeDSecureDataForCustomer($customerWithOrders);
        $paymentTokensClientMock = $this->getMockBuilder(PaymentTokensClientInterface::class)->getMock();
        $paymentTokensClientMock->method('getCustomerPaymentTokens')->willReturn(
            (new WorldlinePaymentTokensResponseTransfer())
                ->setIsSuccessful(true)
                ->setTokens(new ArrayObject([$tokenTransfer])),
        );
       $restRequestMock = $this->createRestRequestMock($customerWithOrders->getCustomerReference());

        // Execute
        $restResponse = (new RestPaymentTokensReader($paymentTokensClientMock, new PaymentTokensRestResponseBuilder(new RestResourceBuilder(), new RestApiError(), new RestPaymentTokensMapper())))
            ->getPaymentTokensRestResponseByCustomerReference($restRequestMock);

        // Verify
        self::assertSame(200, $restResponse->getStatus());
        self::assertSame(1, count($restResponse->getResources()));

        /** @var RestPaymentTokensAttributesTransfer $restPaymentTokensAttributes */
        $restPaymentTokensAttributes =  ($restResponse->getResources()[0])->getAttributes();
        $restPaymentTokenAttributesList = $restPaymentTokensAttributes->getTokens();

        self::assertSame(1, count($restPaymentTokenAttributesList));
        $restPaymentTokenAttributes = $restPaymentTokenAttributesList[0];
        self::assertSame($tokenTransfer->getToken(), $restPaymentTokenAttributes->getToken());
        self::assertSame($tokenTransfer->getExpiryMonth(), $restPaymentTokenAttributes->getExpiryMonth());
        self::assertSame($tokenTransfer->getObfuscatedCardNumber(), $restPaymentTokenAttributes->getObfuscatedCardNumber());
        self::assertSame($tokenTransfer->getHolderName(), $restPaymentTokenAttributes->getHolderName());
        self::assertSame($tokenTransfer->getExpiredAt(), $restPaymentTokenAttributes->getExpiredAt());
        self::assertSame($tokenTransfer->getDeletedAt(), $restPaymentTokenAttributes->getDeletedAt());
    }

    /**
     * @param string $customerReference
     * @return RestRequest
     */
    private function createRestRequestMock(string $customerReference) : RestRequest
    {
        $resourceMock = $this->getMockBuilder(RestResource::class)->disableOriginalConstructor()->getMock();
        $resourceMock
            ->expects($this->once())
            ->method('getId')
            ->willReturn($customerReference);

        $restRequestMock = $this->getMockBuilder(RestRequest::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getRestUser', 'getResource'])
            ->getMock();
        $restUserTransfer = (new RestUserBuilder())->build();
        $restRequestMock
            ->method('getRestUser')
            ->willReturn($restUserTransfer);

        $restRequestMock
            ->method('getResource')
            ->willReturn($resourceMock);

        return $restRequestMock;
    }
}
