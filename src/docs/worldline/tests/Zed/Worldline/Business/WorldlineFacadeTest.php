<?php

declare(strict_types = 1);

namespace ValanticSprykerTest\Zed\Worldline\Business;

use DateTime;
use Faker\Provider\Uuid;
use Generated\Shared\Transfer\AmountOfMoneyTransfer;
use Generated\Shared\Transfer\CheckoutResponseTransfer;
use Generated\Shared\Transfer\CurrencyTransfer;
use Generated\Shared\Transfer\CustomerTransfer;
use Generated\Shared\Transfer\HostedCheckoutSpecificInputTransfer;
use Generated\Shared\Transfer\OrderTransfer;
use Generated\Shared\Transfer\PaymentMethodsTransfer;
use Generated\Shared\Transfer\PaymentMethodTransfer;
use Generated\Shared\Transfer\PaymentProductFiltersHostedCheckoutTransfer;
use Generated\Shared\Transfer\PaymentProviderTransfer;
use Generated\Shared\Transfer\PaymentWorldlineTransactionStatusTransfer;
use Generated\Shared\Transfer\PaymentWorldlineTransfer;
use Generated\Shared\Transfer\QueueSendMessageTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\SaveOrderTransfer;
use Generated\Shared\Transfer\TotalsTransfer;
use Generated\Shared\Transfer\WorldlineAddressPersonalTransfer;
use Generated\Shared\Transfer\WorldlineAddressTransfer;
use Generated\Shared\Transfer\WorldlineCancelPaymentTransfer;
use Generated\Shared\Transfer\WorldlineCapturePaymentRequestTransfer;
use Generated\Shared\Transfer\WorldlineCreateHostedCheckoutTransfer;
use Generated\Shared\Transfer\WorldlineCreditCardTokenTransfer;
use Generated\Shared\Transfer\WorldlineCustomerTransfer;
use Generated\Shared\Transfer\WorldlineDeleteTokenRequestTransfer;
use Generated\Shared\Transfer\WorldlineGetHostedCheckoutStatusTransfer;
use Generated\Shared\Transfer\WorldlineGetPaymentProductsRequestTransfer;
use Generated\Shared\Transfer\WorldlineGetPaymentRequestTransfer;
use Generated\Shared\Transfer\WorldlineGetRefundRequestTransfer;
use Generated\Shared\Transfer\WorldlineOrderTransfer;
use Generated\Shared\Transfer\WorldlinePaymentHostedCheckoutTransfer;
use Generated\Shared\Transfer\WorldlinePaymentProductFilterTransfer;
use Generated\Shared\Transfer\WorldlinePaymentTokenRequestTransfer;
use Generated\Shared\Transfer\WorldlinePersonalNameTransfer;
use Generated\Shared\Transfer\WorldlineRefundCustomerTransfer;
use Generated\Shared\Transfer\WorldlineRefundRequestTransfer;
use Generated\Shared\Transfer\WorldlineShippingTransfer;
use Generated\Shared\Transfer\WorldlineTokenEventDataTransfer;
use Ingenico\Connect\Sdk\Connection;
use Ingenico\Connect\Sdk\ConnectionResponse;
use Orm\Zed\Payment\Persistence\SpyPaymentMethodQuery;
use Orm\Zed\Payment\Persistence\SpyPaymentProviderQuery;
use Orm\Zed\Sales\Persistence\SpySalesOrderQuery;
use Orm\Zed\Worldline\Persistence\VsyPaymentWorldlineQuery;
use Orm\Zed\Worldline\Persistence\VsyPaymentWorldlineTransactionStatusLogQuery;
use Orm\Zed\Worldline\Persistence\VsyWorldlineApiCallLogQuery;
use Orm\Zed\Worldline\Persistence\VsyWorldlineApiLogQuery;
use Orm\Zed\Worldline\Persistence\VsyWorldlineRestLogQuery;
use Orm\Zed\Worldline\Persistence\VsyWorldlineRestReceiveLogQuery;
use Orm\Zed\Worldline\Persistence\VsyWorldlineThreeDSecureResultQuery;
use Orm\Zed\Worldline\Persistence\VsyWorldlineToken;
use Orm\Zed\Worldline\Persistence\VsyWorldlineTokenQuery;
use ValanticSpryker\Glue\CheckoutRestApi\CheckoutRestApiConfig;
use ValanticSpryker\Shared\Worldline\WorldlineConstants;
use ValanticSpryker\Shared\WorldlineWebhook\WorldlineWebhookConstants;
use ValanticSpryker\Zed\Worldline\Business\Logger\WorldlineApiCallLogger;
use ValanticSpryker\Zed\Worldline\Business\Logger\WorldlineApiCallLoggerInterface;
use ValanticSpryker\Zed\Worldline\Business\WorldlineBusinessFactory;
use ValanticSpryker\Zed\Worldline\Business\WorldlineFacade;
use ValanticSpryker\Zed\Worldline\Business\WorldlineFacadeInterface;
use ValanticSpryker\Zed\Worldline\Persistence\WorldlineEntityManager;
use ValanticSpryker\Zed\Worldline\Persistence\WorldlineQueryContainer;
use ValanticSpryker\Zed\Worldline\Persistence\WorldlineRepository;
use ValanticSpryker\Zed\Worldline\WorldlineConfig;
use ValanticSpryker\Zed\Worldline\WorldlineDependencyProvider;
use ValanticSprykerTest\Shared\Base\AbstractTest;
use ValanticSprykerTest\Zed\Worldline\WorldlineBusinessTester;
use Spryker\Zed\Kernel\Container;
use Spryker\Zed\Oms\Business\Util\ReadOnlyArrayObject;
use Spryker\Zed\PropelOrm\Business\Runtime\ActiveQuery\Criteria;
use Spryker\Zed\Sales\Business\SalesFacade;

/**
 * Auto-generated group annotations
 *
 * @group ValanticSprykerTest
 * @group Zed
 * @group Worldline
 * @group Business
 * @group Facade
 * @group WorldlineFacadeTest
 * Add your own group annotations below this line
 */
class WorldlineFacadeTest extends AbstractTest
{
    private const HOSTED_CHECKOUT_ID = '15c09dac-bf44-486a-af6b-edfd8680a166';

    private const RETURNURL = 'https://some.domain.com';

    private const SOME_PAYMENT_ID = '000000850010000188180000200001';

    /**
     * @var string
     */
    protected const TEST_USERNAME = 'test username';

    /**
     * @var string
     */
    protected const TEST_PASSWORD = 'change123';

    /**
     * @var int
     */
    protected const TEST_GRAND_TOTAL = 1;

    /**
     * @var string
     */
    private const HOSTED_CHECKOUT_STATUS_PENDING = 'hosted checkout status pending';

    /**
     * @var string
     */
    private const STATUS_HOSTED_CHECKOUT_STATUS_FAILED = 'hosted checkout status cancelled';

    /**
     * @var \ValanticSprykerTest\Zed\Worldline\WorldlineBusinessTester
     */
    protected WorldlineBusinessTester $tester;

    /**
     * @return void
     */
    public function testCreateHostedCheckoutReturnsValidHostedCheckoutResponseTransfer(): void
    {
        // Arrange
        $pathToResponses = __DIR__ . '/../_data/create_hosted_checkout_response.json';
        $body = file_get_contents($pathToResponses);

        $apiLogger = $this->getMockBuilder(WorldlineApiCallLoggerInterface::class)->getMock();

        $wlConnection = new TestingConnection(
            $apiLogger,
            $this->getMockConnectionResponse(200, ['Content-Type' => 'application/json'], $body),
        );
        $businessFactoryMock = $this->createBusinessFactoryMock($wlConnection);
        $sut = $this->createFacadeMock($wlConnection, $businessFactoryMock);

        $hostedCheckoutSpecificInputTransfer = (new HostedCheckoutSpecificInputTransfer())
            ->setLocale('de_DE')
            ->setReturnUrl(self::RETURNURL)
            ->setShowResultPage(false)
            ->setValidateShoppingCart(false)
            ->setVariant('false')
            ->setPaymentProductFilters(
                (new PaymentProductFiltersHostedCheckoutTransfer())
                    ->setRestrictTo((new WorldlinePaymentProductFilterTransfer())
                        ->setProducts([1]))
                    ->setExclude(new WorldlinePaymentProductFilterTransfer()),
            );
        $createHostedCheckoutTransfer = new WorldlineCreateHostedCheckoutTransfer();
        $createHostedCheckoutTransfer->setHostedCheckoutSpecificInput($hostedCheckoutSpecificInputTransfer);
        $createHostedCheckoutTransfer->setOrder(
            (new WorldlineOrderTransfer())
                ->setAmountOfMoney(
                    (new AmountOfMoneyTransfer())
                        ->setAmount(666)
                        ->setCurrencyCode('EUR'),
                )
                ->setCustomer(
                    (new WorldlineCustomerTransfer())
                        ->setBillingAddress(
                            (new WorldlineAddressTransfer())
                                ->setCountryCode('DE'),
                        )
                        ->setMerchantCustomerId('TESTDE08990005'),
                )
                ->setShipping(
                    (new WorldlineShippingTransfer())
                        ->setAddress(
                            (new WorldlineAddressPersonalTransfer())
                                ->setName((new WorldlinePersonalNameTransfer())->setTitle('some title')),
                        ),
                ),
        );

        // Act
        $hostedCheckoutResponseTransfer = $sut->createHostedCheckout($createHostedCheckoutTransfer);

        // Assert
        self::assertSame(self::HOSTED_CHECKOUT_ID, $hostedCheckoutResponseTransfer->getHostedCheckoutId());
    }

    /**
     * @return void
     */
    public function testCreateHostedCheckoutReturnsValidHostedCheckoutResponseTransferWithErrorOnApiError(): void
    {
        // Arrange
        $pathToResponses = __DIR__ . '/../_data/create_hosted_checkout_error_response.json';
        $body = file_get_contents($pathToResponses);

        $apiLogger = $this->getMockBuilder(WorldlineApiCallLoggerInterface::class)->getMock();

        $wlConnection = new TestingConnection(
            $apiLogger,
            $this->getMockConnectionResponse(400, ['Content-Type' => 'application/json'], $body),
        );
        $businessFactoryMock = $this->createBusinessFactoryMock($wlConnection);
        $sut = $this->createFacadeMock($wlConnection, $businessFactoryMock);

        $hostedCheckoutSpecificInputTransfer = (new HostedCheckoutSpecificInputTransfer())
            ->setLocale('de_DE')
            ->setReturnUrl(self::RETURNURL)
            ->setShowResultPage(false)
            ->setValidateShoppingCart(false)
            ->setVariant('false')
            ->setPaymentProductFilters(
                (new PaymentProductFiltersHostedCheckoutTransfer())
                    ->setRestrictTo((new WorldlinePaymentProductFilterTransfer())
                        ->setProducts([1]))
                    ->setExclude(new WorldlinePaymentProductFilterTransfer()),
            );
        $createHostedCheckoutTransfer = new WorldlineCreateHostedCheckoutTransfer();
        $createHostedCheckoutTransfer->setHostedCheckoutSpecificInput($hostedCheckoutSpecificInputTransfer);
        $createHostedCheckoutTransfer->setOrder(
            (new WorldlineOrderTransfer())
                ->setAmountOfMoney(
                    (new AmountOfMoneyTransfer())
                        ->setAmount(666)
                        ->setCurrencyCode('EUR'),
                )
                ->setCustomer(
                    (new WorldlineCustomerTransfer())
                        ->setBillingAddress(
                            (new WorldlineAddressTransfer())
                                ->setCountryCode('DE'),
                        )
                        ->setMerchantCustomerId('TESTDE08990005'),
                )
                ->setShipping(
                    (new WorldlineShippingTransfer())
                        ->setAddress(
                            (new WorldlineAddressPersonalTransfer())
                                ->setName((new WorldlinePersonalNameTransfer())->setTitle('some title')),
                        ),
                ),
        );

        // Act
        $createHostedCheckoutResponseTransfer = $sut->createHostedCheckout($createHostedCheckoutTransfer);

        // Assert
        self::assertFalse($createHostedCheckoutResponseTransfer->getIsSuccess());
        self::assertSame('15eabcd5-30b3-479b-ae03-67bb351c07e6-00000092', $createHostedCheckoutResponseTransfer->getErrorId());
        self::assertCount(1, $createHostedCheckoutResponseTransfer->getErrors());
        self::assertSame('20000000', $createHostedCheckoutResponseTransfer->getErrors()->getArrayCopy()[0]->getCode());
        self::assertSame('bankAccountBban.accountNumber', $createHostedCheckoutResponseTransfer->getErrors()->getArrayCopy()[0]->getPropertyName());
    }

    /**
     * @return void
     */
    public function testCreateHostedCheckoutLogsRequestAndResponseInDB(): void
    {
        // Arrange
        $pathToResponses = __DIR__ . '/../_data/create_hosted_checkout_response.json';
        $body = file_get_contents($pathToResponses);

        $this->tester->setConfig(WorldlineConstants::WORLDLINE_API_ENDPOINT, 'http://api.endpoint');
        $this->tester->setConfig(WorldlineConstants::WORLDLINE_API_KEY_ID, 'API_KEY_ID_010');
        $this->tester->setConfig(WorldlineConstants::WORLDLINE_API_LR_MERCHANT_ID, 'MERCHANT_ID_010');

        $apiLogger = new WorldlineApiCallLogger(new WorldlineEntityManager());

        $bodyArray = json_decode($body, true);

        $bodyArray['hostedCheckoutId'] = uniqid('abcd', true);

        $body = json_encode($bodyArray, JSON_PRETTY_PRINT);
        $wlConnection = new TestingConnection(
            $apiLogger,
            $this->getMockConnectionResponse(200, ['Content-Type' => 'application/json'], $body),
        );
        $businessFactoryMock = $this->createBusinessFactoryMock($wlConnection);
        $sut = $this->createFacadeMock($wlConnection, $businessFactoryMock);

        $hostedCheckoutSpecificInputTransfer = (new HostedCheckoutSpecificInputTransfer())
            ->setLocale('de_DE')
            ->setReturnUrl(self::RETURNURL)
            ->setShowResultPage(false)
            ->setValidateShoppingCart(false)
            ->setVariant('false')
            ->setPaymentProductFilters(
                (new PaymentProductFiltersHostedCheckoutTransfer())
                    ->setRestrictTo((new WorldlinePaymentProductFilterTransfer())
                        ->setProducts([1]))
                    ->setExclude(new WorldlinePaymentProductFilterTransfer()),
            );
        $createHostedCheckoutTransfer = new WorldlineCreateHostedCheckoutTransfer();
        $createHostedCheckoutTransfer->setHostedCheckoutSpecificInput($hostedCheckoutSpecificInputTransfer);
        $createHostedCheckoutTransfer->setOrder(
            (new WorldlineOrderTransfer())
                ->setAmountOfMoney(
                    (new AmountOfMoneyTransfer())
                        ->setAmount(666)
                        ->setCurrencyCode('EUR'),
                )
                ->setCustomer(
                    (new WorldlineCustomerTransfer())
                        ->setBillingAddress(
                            (new WorldlineAddressTransfer())
                                ->setCountryCode('DE'),
                        )
                        ->setMerchantCustomerId('TESTDE08990005'),
                )
                ->setShipping(
                    (new WorldlineShippingTransfer())
                        ->setAddress(
                            (new WorldlineAddressPersonalTransfer())
                                ->setName((new WorldlinePersonalNameTransfer())->setTitle('some title')),
                        ),
                ),
        );

        // Act
        $sut->createHostedCheckout($createHostedCheckoutTransfer);

        // Assert
        $apiCallLogEntities = VsyWorldlineApiCallLogQuery::create()->find();
        $apiCallLogEntity = VsyWorldlineApiCallLogQuery::create()->findOneByResponseBody($body);
        self::assertNotNull($apiCallLogEntity);
        self::assertSame($body, $apiCallLogEntity->getResponseBody());
        self::assertGreaterThanOrEqual(1, $apiCallLogEntities->count());
        $apiLogEntity = VsyWorldlineApiLogQuery::create()
            ->findOneByApiKey('API_KEY_ID_010');
        self::assertNotNull($apiLogEntity);
        self::assertSame('MERCHANT_ID_010', $apiLogEntity->getMerchantId());
        self::assertSame('http://api.endpoint', $apiLogEntity->getApiEndpoint());
    }

    /**
     * @return void
     */
    public function testGetHostedCheckoutStatusReturnsHostedCheckoutTransferWithStatus(): void
    {
        // Arrange
        $pathToResponses = __DIR__ . '/../_data/get_hosted_checkout_status_response_PAYMENT_CREATED.json';
        $body = file_get_contents($pathToResponses);

        $apiLogger = $this->getMockBuilder(WorldlineApiCallLoggerInterface::class)->getMock();

        $wlConnection = new TestingConnection(
            $apiLogger,
            $this->getMockConnectionResponse(200, ['Content-Type' => 'application/json'], $body),
        );
        $businessFactoryMock = $this->createBusinessFactoryMock($wlConnection);
        $sut = $this->createFacadeMock($wlConnection, $businessFactoryMock);

        $hostedCheckoutTransfer = (new WorldlineGetHostedCheckoutStatusTransfer())->setHostedCheckoutId('someId');

        // Act
        $hostedCheckoutResponseTransfer = $sut->getHostedCheckoutStatus($hostedCheckoutTransfer);

        // Assert
        self::assertTrue($hostedCheckoutResponseTransfer->getIsSuccess());

        self::assertNotNull($hostedCheckoutResponseTransfer->getStatus());
        self::assertSame('PAYMENT_CREATED', $hostedCheckoutResponseTransfer->getStatus());

        self::assertNotNull($hostedCheckoutResponseTransfer->getCreatedPaymentOutput()->getPayment());
        self::assertSame('PENDING_APPROVAL', $hostedCheckoutResponseTransfer->getCreatedPaymentOutput()->getPayment()->getStatus());
        self::assertNotNull($hostedCheckoutResponseTransfer->getCreatedPaymentOutput()->getPayment()->getId());
        self::assertNotNull($hostedCheckoutResponseTransfer->getCreatedPaymentOutput()->getPayment()->getHostedCheckoutSpecificOutput());
        self::assertNotNull($hostedCheckoutResponseTransfer->getCreatedPaymentOutput()->getPayment()->getHostedCheckoutSpecificOutput()->getHostedCheckoutId());
        self::assertNotNull($hostedCheckoutResponseTransfer->getCreatedPaymentOutput()->getPayment()->getHostedCheckoutSpecificOutput()->getVariant());

        self::assertNotNull($hostedCheckoutResponseTransfer->getCreatedPaymentOutput()->getPayment()->getPaymentOutput());
        self::assertNotNull($hostedCheckoutResponseTransfer->getCreatedPaymentOutput()->getPayment()->getPaymentOutput()->getAmountOfMoney());
        self::assertSame(2345, $hostedCheckoutResponseTransfer->getCreatedPaymentOutput()->getPayment()->getPaymentOutput()->getAmountOfMoney()->getAmount());
        self::assertSame('USD', $hostedCheckoutResponseTransfer->getCreatedPaymentOutput()->getPayment()->getPaymentOutput()->getAmountOfMoney()->getCurrencyCode());
        self::assertNotNull($hostedCheckoutResponseTransfer->getCreatedPaymentOutput()->getPayment()->getPaymentOutput()->getReferences());
        self::assertNotNull($hostedCheckoutResponseTransfer->getCreatedPaymentOutput()->getPayment()->getPaymentOutput()->getReferences()->getReferenceOrigPayment());

        self::assertNotNull($hostedCheckoutResponseTransfer->getCreatedPaymentOutput()->getPayment()->getStatusOutput());
        self::assertTrue($hostedCheckoutResponseTransfer->getCreatedPaymentOutput()->getPayment()->getStatusOutput()->getIsCancellable());
        self::assertSame('PENDING_MERCHANT', $hostedCheckoutResponseTransfer->getCreatedPaymentOutput()->getPayment()->getStatusOutput()->getStatusCategory());
        self::assertSame(600, $hostedCheckoutResponseTransfer->getCreatedPaymentOutput()->getPayment()->getStatusOutput()->getStatusCode());
        self::assertTrue($hostedCheckoutResponseTransfer->getCreatedPaymentOutput()->getPayment()->getStatusOutput()->getIsAuthorized());
    }

    /**
     * @return void
     */
    public function testGetHostedCheckoutStatusReturnsHostedCheckoutTransferWithErrorsOnApiError(): void
    {
        // Arrange
        $pathToResponses = __DIR__ . '/../_data/get_hosted_checkout_status_error_response.json';
        $body = file_get_contents($pathToResponses);

        $apiLogger = $this->getMockBuilder(WorldlineApiCallLoggerInterface::class)->getMock();

        $wlConnection = new TestingConnection(
            $apiLogger,
            $this->getMockConnectionResponse(400, ['Content-Type' => 'application/json'], $body),
        );
        $businessFactoryMock = $this->createBusinessFactoryMock($wlConnection);
        $sut = $this->createFacadeMock($wlConnection, $businessFactoryMock);

        $getHostedCheckoutStatusTransfer = (new WorldlineGetHostedCheckoutStatusTransfer())->setHostedCheckoutId('someId');

        // Act
        $getHostedCheckoutResponseTransfer = $sut->getHostedCheckoutStatus($getHostedCheckoutStatusTransfer);

        // Assert
        self::assertFalse($getHostedCheckoutResponseTransfer->getIsSuccess());
        self::assertSame('15eabcd5-30b3-479b-ae03-67bb351c07e6-00000092', $getHostedCheckoutResponseTransfer->getErrorId());
        self::assertCount(1, $getHostedCheckoutResponseTransfer->getErrors());
        self::assertSame('20000000', $getHostedCheckoutResponseTransfer->getErrors()->getArrayCopy()[0]->getCode());
        self::assertSame('bankAccountBban.accountNumber', $getHostedCheckoutResponseTransfer->getErrors()->getArrayCopy()[0]->getPropertyName());
    }

    /**
     * @return void
     */
    public function testGetPaymentProductsReturnsCorrectPaymentProducts(): void
    {
        // Arrange
        $pathToResponses = __DIR__ . '/../_data/get_payment_products_response.json';
        $body = file_get_contents($pathToResponses);

        $apiLogger = $this->getMockBuilder(WorldlineApiCallLoggerInterface::class)->getMock();

        $wlConnection = new TestingConnection(
            $apiLogger,
            $this->getMockConnectionResponse(200, ['Content-Type' => 'application/json'], $body),
        );
        $businessFactoryMock = $this->createBusinessFactoryMock($wlConnection);
        $sut = $this->createFacadeMock($wlConnection, $businessFactoryMock);

        $getPaymentProductsRequestTransfer = (new WorldlineGetPaymentProductsRequestTransfer())
            ->setCountryCode('US')
            ->setCurrencyCode('USD')
            ->setLocale('en_US')
            ->setAmount(1000)
            ->setIsRecurring(true)
            ->setHide('fields');

        // Act
        $getPaymentProductsResponseTransfer = $sut->getPaymentProducts($getPaymentProductsRequestTransfer);

        // Assert
        self::assertTrue($getPaymentProductsResponseTransfer->getIsSuccess());
        $paymentProducts = $getPaymentProductsResponseTransfer->getPaymentProducts();
        self::assertNotNull($paymentProducts);
        self::assertCount(3, $paymentProducts);
    }

    /**
     * @return void
     */
    public function testGetPaymentProductsReturnsCorrectErrorInformationOnApiError(): void
    {
        // Arrange
        $pathToResponses = __DIR__ . '/../_data/get_payment_products_error_response.json';
        $body = file_get_contents($pathToResponses);

        $apiLogger = $this->getMockBuilder(WorldlineApiCallLoggerInterface::class)->getMock();

        $wlConnection = new TestingConnection(
            $apiLogger,
            $this->getMockConnectionResponse(400, ['Content-Type' => 'application/json'], $body),
        );
        $businessFactoryMock = $this->createBusinessFactoryMock($wlConnection);
        $sut = $this->createFacadeMock($wlConnection, $businessFactoryMock);

        $getPaymentProductsRequestTransfer = (new WorldlineGetPaymentProductsRequestTransfer())
            ->setCountryCode('US')
            ->setCurrencyCode('USD')
            ->setLocale('en_US')
            ->setAmount(1000)
            ->setIsRecurring(true)
            ->setHide('fields');

        // Act
        $getPaymentProductsResponseTransfer = $sut->getPaymentProducts($getPaymentProductsRequestTransfer);

        // Assert
        self::assertFalse($getPaymentProductsResponseTransfer->getIsSuccess());
        self::assertSame('15eabcd5-30b3-479b-ae03-67bb351c07e6-00000092', $getPaymentProductsResponseTransfer->getErrorId());
        self::assertCount(1, $getPaymentProductsResponseTransfer->getErrors());
        self::assertSame('20000001', $getPaymentProductsResponseTransfer->getErrors()->getArrayCopy()[0]->getCode());
        self::assertSame('locale', $getPaymentProductsResponseTransfer->getErrors()->getArrayCopy()[0]->getPropertyName());
    }

    /**
     * @return void
     */
    public function testGetPaymentReturnsCorrectPaymentInformation(): void
    {
        // Arrange
        $pathToResponses = __DIR__ . '/../_data/get_payment_response.json';
        $body = file_get_contents($pathToResponses);

        $apiLogger = $this->getMockBuilder(WorldlineApiCallLoggerInterface::class)->getMock();

        $wlConnection = new TestingConnection(
            $apiLogger,
            $this->getMockConnectionResponse(200, ['Content-Type' => 'application/json'], $body),
        );
        $businessFactoryMock = $this->createBusinessFactoryMock($wlConnection);
        $sut = $this->createFacadeMock($wlConnection, $businessFactoryMock);

        $getPaymentTransfer = new WorldlineGetPaymentRequestTransfer();
        $getPaymentTransfer->setPaymentId(self::SOME_PAYMENT_ID);

        // Act
        $getPaymentResponseTransfer = $sut->getPayment($getPaymentTransfer);

        // Assert
        self::assertTrue($getPaymentResponseTransfer->getIsSuccess());
        self::assertSame(self::SOME_PAYMENT_ID, $getPaymentResponseTransfer->getId());
        self::assertNotNull($getPaymentResponseTransfer->getPaymentOutput());
        self::assertNotNull($getPaymentResponseTransfer->getPaymentOutput()->getAmountOfMoney());
        self::assertSame('card', $getPaymentResponseTransfer->getPaymentOutput()->getPaymentMethod());
        self::assertSame('PENDING_APPROVAL', $getPaymentResponseTransfer->getStatus());
        self::assertNotNull($getPaymentResponseTransfer->getPaymentOutput()->getCardPaymentMethodSpecificOutput());
        self::assertNotNull($getPaymentResponseTransfer->getPaymentOutput()->getCardPaymentMethodSpecificOutput()->getThreeDSecureResults());
        self::assertSame(100, $getPaymentResponseTransfer->getPaymentOutput()->getCardPaymentMethodSpecificOutput()->getThreeDSecureResults()->getAuthenticationAmount()->getAmount());
    }

    /**
     * @return void
     */
    public function testCancelPaymentGivesCorrectResponse(): void
    {
        // Arrange
        $pathToResponses = __DIR__ . '/../_data/cancel_payment_response.json';
        $body = file_get_contents($pathToResponses);

        $apiLogger = $this->getMockBuilder(WorldlineApiCallLoggerInterface::class)->getMock();

        $wlConnection = new TestingConnection(
            $apiLogger,
            $this->getMockConnectionResponse(200, ['Content-Type' => 'application/json'], $body),
        );
        $businessFactoryMock = $this->createBusinessFactoryMock($wlConnection);
        $sut = $this->createFacadeMock($wlConnection, $businessFactoryMock);

        $cancelPaymentTransfer = new WorldlineCancelPaymentTransfer();
        $cancelPaymentTransfer->setPaymentId(self::SOME_PAYMENT_ID);
        // Act
        $cancelPaymentResponseTransfer = $sut->cancelPayment($cancelPaymentTransfer);

        // Assert
        self::assertTrue($cancelPaymentResponseTransfer->getIsSuccess());
        self::assertSame('CANCELLED', $cancelPaymentResponseTransfer->getPayment()->getStatus());
        self::assertNotNull($cancelPaymentResponseTransfer->getPayment());
        self::assertNotNull($cancelPaymentResponseTransfer->getPayment()->getPaymentOutput());
        self::assertNotNull($cancelPaymentResponseTransfer->getPayment()->getPaymentOutput()->getCardPaymentMethodSpecificOutput());
        self::assertNotNull($cancelPaymentResponseTransfer->getCardPaymentMethodSpecificOutput());
        self::assertNotNull($cancelPaymentResponseTransfer->getCardPaymentMethodSpecificOutput()->getVoidResponseId());
    }

    /**
     * @return void
     */
    public function testCapturePaymentGivesCorrectResponse(): void
    {
        // Arrange
        $pathToResponses = __DIR__ . '/../_data/capture_payment_response.json';
        $body = file_get_contents($pathToResponses);

        $apiLogger = $this->getMockBuilder(WorldlineApiCallLoggerInterface::class)->getMock();

        $wlConnection = new TestingConnection(
            $apiLogger,
            $this->getMockConnectionResponse(200, ['Content-Type' => 'application/json'], $body),
        );
        $businessFactoryMock = $this->createBusinessFactoryMock($wlConnection);
        $sut = $this->createFacadeMock($wlConnection, $businessFactoryMock);

        $capturePaymentTransfer = new WorldlineCapturePaymentRequestTransfer();
        $capturePaymentTransfer->setPaymentId(self::SOME_PAYMENT_ID);
        $capturePaymentTransfer->setAmount(777);
        $capturePaymentTransfer->setIsFinal(true);

        // Act
        $captureResponseTransfer = $sut->capturePayment($capturePaymentTransfer);

        // Assert
        self::assertTrue($captureResponseTransfer->getIsSuccess());
        self::assertSame('CAPTURE_REQUESTED', $captureResponseTransfer->getStatus());
        self::assertSame(100, $captureResponseTransfer->getCaptureOutput()->getAmountOfMoney()->getAmount());
        self::assertSame('EUR', $captureResponseTransfer->getCaptureOutput()->getAmountOfMoney()->getCurrencyCode());
        self::assertSame('726747', $captureResponseTransfer->getCaptureOutput()->getCardPaymentMethodSpecificOutput()->getAuthorisationCode());
        self::assertSame('************7977', $captureResponseTransfer->getCaptureOutput()->getCardPaymentMethodSpecificOutput()->getCard()->getCardNumber());
    }

    /**
     * @return void
     */
    public function testCreateRefundReturnsCorrectResponse(): void
    {
        // Arrange
        $pathToResponses = __DIR__ . '/../_data/create_refund_response.json';
        $body = file_get_contents($pathToResponses);

        $apiLogger = $this->getMockBuilder(WorldlineApiCallLoggerInterface::class)->getMock();

        $wlConnection = new TestingConnection(
            $apiLogger,
            $this->getMockConnectionResponse(200, ['Content-Type' => 'application/json'], $body),
        );
        $businessFactoryMock = $this->createBusinessFactoryMock($wlConnection);
        $sut = $this->createFacadeMock($wlConnection, $businessFactoryMock);

        $refundRequestTransfer = new WorldlineRefundRequestTransfer();
        $refundRequestTransfer->setAmountOfMoney(
            (new AmountOfMoneyTransfer())
                ->setAmount(666)
                ->setCurrencyCode('EUR'),
        )
            ->setCustomer(
                (new WorldlineRefundCustomerTransfer())
                    ->setAddress(
                        (new WorldlineAddressPersonalTransfer())
                            ->setName((new WorldlinePersonalNameTransfer())->setTitle('my Title')),
                    )
                    ->setFiscalNumber('some number'),
            );

        // Act
        $createRefundResponseTransfer = $sut->createRefund($refundRequestTransfer);

        // Assert
        self::assertTrue($createRefundResponseTransfer->getIsSuccess());
        self::assertNotNull($createRefundResponseTransfer->getStatus());
        self::assertSame('PENDING_APPROVAL', $createRefundResponseTransfer->getStatus());
        self::assertNotNull($createRefundResponseTransfer->getStatusOutput());
        self::assertSame(800, $createRefundResponseTransfer->getStatusOutput()->getStatusCode());
        self::assertNotNull($createRefundResponseTransfer->getRefundOutput());
        self::assertNotNull($createRefundResponseTransfer->getRefundOutput()->getAmountOfMoney());
        self::assertSame(1, $createRefundResponseTransfer->getRefundOutput()->getAmountOfMoney()->getAmount());
        self::assertSame('EUR', $createRefundResponseTransfer->getRefundOutput()->getAmountOfMoney()->getCurrencyCode());
    }

    /**
     * @return void
     */
    public function testGetRefundReturnsCorrectResponse(): void
    {
        // Arrange
        $pathToResponses = __DIR__ . '/../_data/get_refund_response.json';
        $body = file_get_contents($pathToResponses);

        $apiLogger = $this->getMockBuilder(WorldlineApiCallLoggerInterface::class)->getMock();

        $wlConnection = new TestingConnection(
            $apiLogger,
            $this->getMockConnectionResponse(200, ['Content-Type' => 'application/json'], $body),
        );
        $businessFactoryMock = $this->createBusinessFactoryMock($wlConnection);
        $sut = $this->createFacadeMock($wlConnection, $businessFactoryMock);

        $getRefundRequestTransfer = new WorldlineGetRefundRequestTransfer();
        $getRefundRequestTransfer->setRefundId('00000085001000006995000-500001');

        // Act
        $createRefundResponseTransfer = $sut->getRefund($getRefundRequestTransfer);

        // Assert
        self::assertTrue($createRefundResponseTransfer->getIsSuccess());
        self::assertNotNull($createRefundResponseTransfer->getStatus());
        self::assertSame('PENDING_APPROVAL', $createRefundResponseTransfer->getStatus());
        self::assertNotNull($createRefundResponseTransfer->getStatusOutput());
        self::assertSame(800, $createRefundResponseTransfer->getStatusOutput()->getStatusCode());
        self::assertNotNull($createRefundResponseTransfer->getRefundOutput());
        self::assertNotNull($createRefundResponseTransfer->getRefundOutput()->getAmountOfMoney());
        self::assertSame(1, $createRefundResponseTransfer->getRefundOutput()->getAmountOfMoney()->getAmount());
        self::assertSame('EUR', $createRefundResponseTransfer->getRefundOutput()->getAmountOfMoney()->getCurrencyCode());
    }

    /**
     * @return void
     */
    public function testFilterPaymentMethodsFiltersOutPaypalIfNotAvailableAccordingToWorldline(): void
    {
        $paymentMethodsTransfer = $this->getPaymentMethodsTransfer();

        $quoteTransfer = new QuoteTransfer();
        $quoteTransfer
            ->setTotals((new TotalsTransfer())->setGrandTotal(4200))
            ->setCurrency((new CurrencyTransfer())->setCode('EUR'))
            ->setCustomer((new CustomerTransfer())->setLocaleName('en_US'));

        $pathToResponses = __DIR__ . '/../_data/get_payment_products_response_filter_payment_methods.json';
        $body = file_get_contents($pathToResponses);

        $apiLogger = $this->getMockBuilder(WorldlineApiCallLoggerInterface::class)->getMock();

        $wlConnection = new TestingConnection(
            $apiLogger,
            $this->getMockConnectionResponse(200, ['Content-Type' => 'application/json'], $body),
        );
        $businessFactoryMock = $this->createBusinessFactoryMock($wlConnection);
        $sut = $this->createFacadeMock($wlConnection, $businessFactoryMock);

        // Act
        $paymentMethodsTransfer = $sut->filterPaymentMethods($paymentMethodsTransfer, $quoteTransfer);

        //Assert
        self::assertCount(3, $paymentMethodsTransfer->getMethods());
        foreach ($paymentMethodsTransfer->getMethods() as $paymentMethodTransfer) {
            self::assertNotSame('PayPal', $paymentMethodTransfer->getName());
            self::assertNotSame(WorldlineConfig::PAYMENT_METHOD_PAYPAL, $paymentMethodTransfer->getPaymentMethodKey());
        }
    }

    /**
     * @return void
     */
    public function testFilterPaymentMethodsFiltersOutMastercardIfCustomerIsImpersonatedAndNoTokenExistsForMasterCard(): void
    {
        $customerTransfer = $this->tester->createCustomerTransfer(static::TEST_USERNAME, static::TEST_PASSWORD);
        $customerTransfer->setIsImpersonated(true);

        $tokenTransfer1 = $this->tester->havePaymentTokenWithThreeDSecureDataForCustomer(
            $customerTransfer,
            [
                WorldlineCreditCardTokenTransfer::DELETED_AT => null,
                WorldlineCreditCardTokenTransfer::PAYMENT_METHOD_KEY => WorldlineConfig::PAYMENT_METHOD_CREDIT_CARD_VISA,
                WorldlineCreditCardTokenTransfer::EXPIRED_AT => null,
            ]);
        $tokenTransfer2 = $this->tester->havePaymentTokenWithThreeDSecureDataForCustomer($customerTransfer, [
            WorldlineCreditCardTokenTransfer::DELETED_AT => null,
            WorldlineCreditCardTokenTransfer::PAYMENT_METHOD_KEY => WorldlineConfig::PAYMENT_METHOD_CREDIT_CARD_AMERICAN_EXPRESS,
            WorldlineCreditCardTokenTransfer::EXPIRED_AT => null,
        ]);

        $paymentMethodsTransfer = $this->getPaymentMethodsTransfer();

        $quoteTransfer = new QuoteTransfer();
        $quoteTransfer
            ->setTotals((new TotalsTransfer())->setGrandTotal(4200))
            ->setCurrency((new CurrencyTransfer())->setCode('EUR'))
            ->setCustomer(
                $customerTransfer
            );

        $pathToResponses = __DIR__ . '/../_data/get_payment_products_response_filter_payment_methods.json';
        $body = file_get_contents($pathToResponses);

        $apiLogger = $this->getMockBuilder(WorldlineApiCallLoggerInterface::class)->getMock();

        $wlConnection = new TestingConnection(
            $apiLogger,
            $this->getMockConnectionResponse(200, array('Content-Type' => 'application/json'), $body),
        );
        $businessFactoryMock = $this->createBusinessFactoryMock($wlConnection);
        $sut = $this->createFacadeMock($wlConnection, $businessFactoryMock);

        // Act
        $paymentMethodsTransfer = $sut->filterPaymentMethods($paymentMethodsTransfer, $quoteTransfer);

        //Assert
        self::assertCount(2, $paymentMethodsTransfer->getMethods());
        foreach ($paymentMethodsTransfer->getMethods() as $paymentMethodTransfer) {
            self::assertNotSame('Master Card', $paymentMethodTransfer->getName());
            self::assertNotSame(WorldlineConfig::PAYMENT_METHOD_CREDIT_CARD_MASTER_CARD, $paymentMethodTransfer->getPaymentMethodKey() );
        }
    }

    /**
     * @return void
     */
    public function testOrderPostSaveReturnsCorrectCheckoutResponseTransfer(): void
    {
        // Arrange
        $customerWithOrders = $this->tester->createCustomerTransfer(static::TEST_USERNAME, static::TEST_PASSWORD);
        $quote = $this->tester->createQuoteTransfer($customerWithOrders, [$this->tester->createProductTransfer()], $this->tester->createPaymentTransfer()->toArray());
        $saveOrderTransfer = $this->tester->createOrderTransfer($quote);

        $orderReference = $saveOrderTransfer->getOrderReference();
        $pathToResponses = __DIR__ . '/../_data/create_hosted_checkout_response.json';
        $body = file_get_contents($pathToResponses);

        $bodyArray = json_decode($body, true);

        $bodyArray['merchantReference'] = $orderReference;

        $body = json_encode($bodyArray, JSON_PRETTY_PRINT);

        $apiLogger = $this->getMockBuilder(WorldlineApiCallLoggerInterface::class)->getMock();

        $wlConnection = new TestingConnection(
            $apiLogger,
            $this->getMockConnectionResponse(200, ['Content-Type' => 'application/json'], $body),
        );
        $businessFactoryMock = $this->createBusinessFactoryMock($wlConnection);
        $sut = $this->createFacadeMock($wlConnection, $businessFactoryMock);

        $sut->saveOrderPayment($quote, $saveOrderTransfer);

        $idSalesOrder = $saveOrderTransfer->getIdSalesOrder();
        $salesOrderEntity = SpySalesOrderQuery::create()->findOneByIdSalesOrder($idSalesOrder);
        $orderItems = $salesOrderEntity->getItems()->getArrayCopy();
        $orderTransfer = new OrderTransfer();
        $orderTransfer->setOrderReference($orderReference);
        $orderTransfer->setCustomerReference($customerWithOrders->getCustomerReference());
        $orderTransfer = (new SalesFacade())->getCustomerOrderByOrderReference($orderTransfer);
        $sut->handleCreateHostedCheckoutCommand($orderItems, $orderTransfer, new ReadOnlyArrayObject());

        $checkoutResponseTransfer = new CheckoutResponseTransfer();
        // Act
        $checkoutResponseTransfer = $sut->orderPostSave($quote, $checkoutResponseTransfer);

        // Assert
        self::assertSame('fecab85c-9b0e-42ee-a9d9-ebb69b0c2eb0', $checkoutResponseTransfer->getReturnmac());
        self::assertSame('pay1.secured-by-ingenico.com/checkout/1701-8cc800ebc3b84667a0b0c9b7981d5b6a:15c09dac-bf44-486a-af6b-edfd8680a166:af6276be66bc4743abfbaa48524c59aa?requestToken=89836363-f87c-4d17-8b11-270d7d9cda9a', $checkoutResponseTransfer->getPartialRedirectUrl());
        self::assertSame('15c09dac-bf44-486a-af6b-edfd8680a166', $checkoutResponseTransfer->getHostedCheckoutId());
        self::assertSame($orderReference, $checkoutResponseTransfer->getMerchantReference());

        $vsyPamentWorldline = VsyPaymentWorldlineQuery::create()->findOneByMerchantReference($orderReference);
        self::assertSame('http://some.url' . '?orderReference=' . $orderReference, $vsyPamentWorldline->getVsyPaymentWorldlineHostedCheckout()->getReturnUrl());

        $vsyWorldlineApiLogEntities = VsyWorldlineApiLogQuery::create()
            ->filterByFkPaymentWorldline($vsyPamentWorldline->getIdPaymentWorldline())
            ->find();
        self::assertCount(1, $vsyWorldlineApiLogEntities);

        $apiLogEntity = $vsyWorldlineApiLogEntities->offsetGet(0);
        self::assertSame(WorldlineConstants::WORLDLINE_API_METHOD_CREATE_HOSTED_CHECKOUT, $apiLogEntity->getApiMethod());
        self::assertSame(200, $apiLogEntity->getHttpStatusCode());

        $transactionStatusLogEntity = VsyPaymentWorldlineTransactionStatusLogQuery::create()->findOneByFkWorldlineApiLog($apiLogEntity->getIdWorldlineApiLog());
        self::assertNotNull($transactionStatusLogEntity);
        self::assertSame(WorldlineConstants::STATUS_HOSTED_CHECKOUT_CREATED, $transactionStatusLogEntity->getStatus());
    }

    /**
     * @return void
     */
    public function testOrderPostSaveReturnsCheckoutResponseTransferWithSuccessFalseAndErrorIfCreateHostedCheckoutFailed(): void
    {
        // Arrange
        $customerWithOrders = $this->tester->createCustomerTransfer(static::TEST_USERNAME, static::TEST_PASSWORD);
        $quote = $this->tester->createQuoteTransfer($customerWithOrders, [$this->tester->createProductTransfer()], $this->tester->createPaymentTransfer()->toArray());
        $saveOrderTransfer = $this->tester->createOrderTransfer($quote);

        $orderReference = $saveOrderTransfer->getOrderReference();

        $paymentWorldlineTransfer = $this->tester->haveHostedCheckout($saveOrderTransfer, $quote);

        $pathToResponses = __DIR__ . '/../_data/create_hosted_checkout_error_response.json';
        $body = file_get_contents($pathToResponses);

        $bodyArray = json_decode($body, true);

        $bodyArray['merchantReference'] = $orderReference;

        $body = json_encode($bodyArray, JSON_PRETTY_PRINT);

        $apiLogger = $this->getMockBuilder(WorldlineApiCallLoggerInterface::class)->getMock();

        $wlConnection = new TestingConnection(
            $apiLogger,
            $this->getMockConnectionResponse(400, ['Content-Type' => 'application/json'], $body),
        );
        $businessFactoryMock = $this->createBusinessFactoryMock($wlConnection);
        $sut = $this->createFacadeMock($wlConnection, $businessFactoryMock);

        $idSalesOrder = $saveOrderTransfer->getIdSalesOrder();
        $salesOrderEntity = SpySalesOrderQuery::create()->findOneByIdSalesOrder($idSalesOrder);
        $orderItems = $salesOrderEntity->getItems()->getArrayCopy();
        $orderTransfer = new OrderTransfer();
        $orderTransfer->setOrderReference($orderReference);
        $orderTransfer->setCustomerReference($customerWithOrders->getCustomerReference());
        $orderTransfer = (new SalesFacade())->getCustomerOrderByOrderReference($orderTransfer);
        $sut->handleCreateHostedCheckoutCommand($orderItems, $orderTransfer, new ReadOnlyArrayObject());

        $checkoutResponseTransfer = new CheckoutResponseTransfer();
        // Act
        $checkoutResponseTransfer = $sut->orderPostSave($quote, $checkoutResponseTransfer);

        // Assert
        self::assertFalse($checkoutResponseTransfer->getIsSuccess());
        self::assertNull($checkoutResponseTransfer->getReturnmac());
        self::assertNull($checkoutResponseTransfer->getPartialRedirectUrl());
        self::assertNull($checkoutResponseTransfer->getMerchantReference());

        $vsyPamentWorldline = VsyPaymentWorldlineQuery::create()->findOneByMerchantReference($orderReference);

        $vsyWorldlineApiLogEntities = VsyWorldlineApiLogQuery::create()
            ->filterByFkPaymentWorldline($vsyPamentWorldline->getIdPaymentWorldline())
            ->find();
        self::assertCount(1, $vsyWorldlineApiLogEntities);

        /** @var \Orm\Zed\Worldline\Persistence\VsyWorldlineApiLog $apiLogEntity */
        $apiLogEntity = $vsyWorldlineApiLogEntities->offsetGet(0);
        self::assertSame(WorldlineConstants::WORLDLINE_API_METHOD_CREATE_HOSTED_CHECKOUT, $apiLogEntity->getApiMethod());
        self::assertSame(400, $apiLogEntity->getHttpStatusCode());
        self::assertSame('20000000', $apiLogEntity->getErrorCode());
        self::assertSame('bankAccountBban.accountNumber', $apiLogEntity->getErrorPropertyName());
        self::assertSame('PARAMETER_NOT_FOUND_IN_REQUEST', $apiLogEntity->getErrorMessage());

        $transactionStatusLogEntity = VsyPaymentWorldlineTransactionStatusLogQuery::create()->findOneByFkWorldlineApiLog($apiLogEntity->getIdWorldlineApiLog());
        self::assertNotNull($transactionStatusLogEntity);
        self::assertSame(WorldlineConstants::STATUS_HOSTED_CHECKOUT_FAILED, $transactionStatusLogEntity->getStatus());
    }

    /**
     * @return void
     */
    public function testIsHostedCheckoutFailedReturnsTrueIfHostedCheckoutFailed(): void
    {
        // Arrange
        $customerWithOrders = $this->tester->createCustomerTransfer(static::TEST_USERNAME, static::TEST_PASSWORD);
        $quote = $this->tester->createQuoteTransfer($customerWithOrders, [$this->tester->createProductTransfer()], $this->tester->createPaymentTransfer()->toArray());
        $saveOrderTransfer = $this->tester->createOrderTransfer($quote);

        $orderReference = $saveOrderTransfer->getOrderReference();

        $paymentWorldlineTransfer = $this->tester->haveHostedCheckout($saveOrderTransfer, $quote);

        $pathToResponses = __DIR__ . '/../_data/create_hosted_checkout_error_response.json';
        $body = file_get_contents($pathToResponses);

        $bodyArray = json_decode($body, true);

        $bodyArray['merchantReference'] = $orderReference;

        $body = json_encode($bodyArray, JSON_PRETTY_PRINT);

        $apiLogger = $this->getMockBuilder(WorldlineApiCallLoggerInterface::class)->getMock();

        $wlConnection = new TestingConnection(
            $apiLogger,
            $this->getMockConnectionResponse(400, ['Content-Type' => 'application/json'], $body),
        );
        $businessFactoryMock = $this->createBusinessFactoryMock($wlConnection);
        $sut = $this->createFacadeMock($wlConnection, $businessFactoryMock);

        $idSalesOrder = $saveOrderTransfer->getIdSalesOrder();
        $salesOrderEntity = SpySalesOrderQuery::create()->findOneByIdSalesOrder($idSalesOrder);
        $orderItems = $salesOrderEntity->getItems()->getArrayCopy();
        $orderTransfer = new OrderTransfer();
        $orderTransfer->setOrderReference($orderReference);
        $orderTransfer->setCustomerReference($customerWithOrders->getCustomerReference());
        $orderTransfer = (new SalesFacade())->getCustomerOrderByOrderReference($orderTransfer);
        $sut->handleCreateHostedCheckoutCommand($orderItems, $orderTransfer, new ReadOnlyArrayObject());

        $checkoutResponseTransfer = new CheckoutResponseTransfer();

        $checkoutResponseTransfer = $sut->orderPostSave($quote, $checkoutResponseTransfer);

        // Act
        $result = $sut->isHostedCheckoutFailed($orderItems[0]);

        // Assert
        self::assertTrue($result);
    }

    /**
     * @return void
     */
    public function testGetHostedCheckoutStatusSetsPaymentCreatedStateInVsyPaymentWorldlineTransactionStatusLogWhenSucceeding(): void
    {
        // Arrange
        $customerWithOrders = $this->tester->createCustomerTransfer(static::TEST_USERNAME, static::TEST_PASSWORD);
        $quote = $this->tester->createQuoteTransfer($customerWithOrders, [$this->tester->createProductTransfer()], $this->tester->createPaymentTransfer()->toArray());
        $saveOrderTransfer = $this->tester->createOrderTransfer($quote);

        $orderReference = $saveOrderTransfer->getOrderReference();

        $paymentWorldlineTransfer = $this->tester->haveHostedCheckout($saveOrderTransfer, $quote);

        $pathToResponses = __DIR__ . '/../_data/get_hosted_checkout_status_response_PAYMENT_CREATED.json';
        $body = file_get_contents($pathToResponses);

        $apiLogger = $this->getMockBuilder(WorldlineApiCallLoggerInterface::class)->getMock();

        $wlConnection = new TestingConnection(
            $apiLogger,
            $this->getMockConnectionResponse(200, ['Content-Type' => 'application/json'], $body),
        );
        $businessFactoryMock = $this->createBusinessFactoryMock($wlConnection);
        $sut = $this->createFacadeMock($wlConnection, $businessFactoryMock);

        $idSalesOrder = $saveOrderTransfer->getIdSalesOrder();
        $salesOrderEntity = SpySalesOrderQuery::create()->findOneByIdSalesOrder($idSalesOrder);
        $orderItems = $salesOrderEntity->getItems()->getArrayCopy();
        $orderTransfer = new OrderTransfer();
        $orderTransfer->setOrderReference($orderReference);
        $orderTransfer->setCustomerReference($customerWithOrders->getCustomerReference());
        $orderTransfer = (new SalesFacade())->getCustomerOrderByOrderReference($orderTransfer);

        // Act
        $sut->handleGetHostedCheckoutStatusCommand($orderItems, $orderTransfer);

        // Assert
        $vsyPamentWorldline = VsyPaymentWorldlineQuery::create()->findOneByMerchantReference($orderReference);
        self::assertSame('000000891566072501680000200001', $vsyPamentWorldline->getPaymentId());

        $vsyWorldlineApiLogEntities = VsyWorldlineApiLogQuery::create()
            ->filterByFkPaymentWorldline($vsyPamentWorldline->getIdPaymentWorldline())
            ->find();
        self::assertCount(1, $vsyWorldlineApiLogEntities);

        /** @var \Orm\Zed\Worldline\Persistence\VsyWorldlineApiLog $apiLogEntity */
        $apiLogEntity = $vsyWorldlineApiLogEntities->offsetGet(0);
        self::assertSame(WorldlineConstants::WORLDLINE_API_METHOD_GET_HOSTED_CHECKOUT_STATUS, $apiLogEntity->getApiMethod());
        self::assertSame(200, $apiLogEntity->getHttpStatusCode());
        self::assertSame('000000891566072501680000200001', $apiLogEntity->getPaymentId());

        $transactionStatusLogEntity = VsyPaymentWorldlineTransactionStatusLogQuery::create()->findOneByFkWorldlineApiLog($apiLogEntity->getIdWorldlineApiLog());
        self::assertNotNull($transactionStatusLogEntity);
        self::assertSame(WorldlineConstants::STATUS_PAYMENT_PENDING_APPROVAL, $transactionStatusLogEntity->getStatus());
        self::assertSame(WorldlineConstants::STATUS_CATEGORY_PENDING_MERCHANT, $transactionStatusLogEntity->getStatusCategory());
        self::assertSame(600, $transactionStatusLogEntity->getStatusCode());
    }

    /**
     * @return void
     */
    public function testGetHostedCheckoutStatusSetsCancelledStateInVsyPaymentWorldlineTransactionStatusLogWhenHostedCheckoutWasCancelledByConsumer(): void
    {
        // Arrange
        $customerWithOrders = $this->tester->createCustomerTransfer(static::TEST_USERNAME, static::TEST_PASSWORD);
        $quote = $this->tester->createQuoteTransfer($customerWithOrders, [$this->tester->createProductTransfer()], $this->tester->createPaymentTransfer()->toArray());
        $saveOrderTransfer = $this->tester->createOrderTransfer($quote);

        $orderReference = $saveOrderTransfer->getOrderReference();

        $paymentWorldlineTransfer = $this->tester->haveHostedCheckout($saveOrderTransfer, $quote);

        $pathToResponses = __DIR__ . '/../_data/get_hosted_checkout_status_response_CANCELLED_BY_CONSUMER.json';
        $body = file_get_contents($pathToResponses);

        $apiLogger = $this->getMockBuilder(WorldlineApiCallLoggerInterface::class)->getMock();

        $wlConnection = new TestingConnection(
            $apiLogger,
            $this->getMockConnectionResponse(200, ['Content-Type' => 'application/json'], $body),
        );
        $businessFactoryMock = $this->createBusinessFactoryMock($wlConnection);
        $sut = $this->createFacadeMock($wlConnection, $businessFactoryMock);

        $idSalesOrder = $saveOrderTransfer->getIdSalesOrder();
        $salesOrderEntity = SpySalesOrderQuery::create()->findOneByIdSalesOrder($idSalesOrder);
        $orderItems = $salesOrderEntity->getItems()->getArrayCopy();
        $orderTransfer = new OrderTransfer();
        $orderTransfer->setOrderReference($orderReference);
        $orderTransfer->setCustomerReference($customerWithOrders->getCustomerReference());
        $orderTransfer = (new SalesFacade())->getCustomerOrderByOrderReference($orderTransfer);

        // Act
        $sut->handleGetHostedCheckoutStatusCommand($orderItems, $orderTransfer);

        // Assert
        $vsyPamentWorldline = VsyPaymentWorldlineQuery::create()->findOneByMerchantReference($orderReference);

        $vsyWorldlineApiLogEntities = VsyWorldlineApiLogQuery::create()
            ->filterByFkPaymentWorldline($vsyPamentWorldline->getIdPaymentWorldline())
            ->find();
        self::assertCount(1, $vsyWorldlineApiLogEntities);

        /** @var \Orm\Zed\Worldline\Persistence\VsyWorldlineApiLog $apiLogEntity */
        $apiLogEntity = $vsyWorldlineApiLogEntities->offsetGet(0);
        self::assertSame(WorldlineConstants::WORLDLINE_API_METHOD_GET_HOSTED_CHECKOUT_STATUS, $apiLogEntity->getApiMethod());
        self::assertSame(200, $apiLogEntity->getHttpStatusCode());

        $transactionStatusLogEntity = VsyPaymentWorldlineTransactionStatusLogQuery::create()->findOneByFkWorldlineApiLog($apiLogEntity->getIdWorldlineApiLog());
        self::assertNotNull($transactionStatusLogEntity);
        self::assertSame(WorldlineConstants::STATUS_HOSTED_CHECKOUT_STATUS_PAYMENT_CANCELLED_BY_CONSUMER, $transactionStatusLogEntity->getStatus());
    }

    /**
     * @return void
     */
    public function testGetHostedCheckoutStatusSetsCorrectCancelledStateInVsyPaymentWorldlineTransactionStatusLogWhenHostedCheckoutReturnedClientNotEligible(): void
    {
        // Arrange
        $customerWithOrders = $this->tester->createCustomerTransfer(static::TEST_USERNAME, static::TEST_PASSWORD);
        $quote = $this->tester->createQuoteTransfer($customerWithOrders, [$this->tester->createProductTransfer()], $this->tester->createPaymentTransfer()->toArray());
        $saveOrderTransfer = $this->tester->createOrderTransfer($quote);

        $orderReference = $saveOrderTransfer->getOrderReference();

        $paymentWorldlineTransfer = $this->tester->haveHostedCheckout($saveOrderTransfer, $quote);

        $pathToResponses = __DIR__ . '/../_data/get_hosted_checkout_status_response_CLIENT_NOT_ELIGIBLE_FOR_SELECTED_PAYMENT_PRODUCT.json';
        $body = file_get_contents($pathToResponses);

        $apiLogger = $this->getMockBuilder(WorldlineApiCallLoggerInterface::class)->getMock();

        $wlConnection = new TestingConnection(
            $apiLogger,
            $this->getMockConnectionResponse(200, ['Content-Type' => 'application/json'], $body),
        );
        $businessFactoryMock = $this->createBusinessFactoryMock($wlConnection);
        $sut = $this->createFacadeMock($wlConnection, $businessFactoryMock);

        $idSalesOrder = $saveOrderTransfer->getIdSalesOrder();
        $salesOrderEntity = SpySalesOrderQuery::create()->findOneByIdSalesOrder($idSalesOrder);
        $orderItems = $salesOrderEntity->getItems()->getArrayCopy();
        $orderTransfer = new OrderTransfer();
        $orderTransfer->setOrderReference($orderReference);
        $orderTransfer->setCustomerReference($customerWithOrders->getCustomerReference());
        $orderTransfer = (new SalesFacade())->getCustomerOrderByOrderReference($orderTransfer);

        // Act
        $sut->handleGetHostedCheckoutStatusCommand($orderItems, $orderTransfer);

        // Assert
        $vsyPamentWorldline = VsyPaymentWorldlineQuery::create()->findOneByMerchantReference($orderReference);

        $vsyWorldlineApiLogEntities = VsyWorldlineApiLogQuery::create()
            ->filterByFkPaymentWorldline($vsyPamentWorldline->getIdPaymentWorldline())
            ->find();
        self::assertCount(1, $vsyWorldlineApiLogEntities);

        /** @var \Orm\Zed\Worldline\Persistence\VsyWorldlineApiLog $apiLogEntity */
        $apiLogEntity = $vsyWorldlineApiLogEntities->offsetGet(0);
        self::assertSame(WorldlineConstants::WORLDLINE_API_METHOD_GET_HOSTED_CHECKOUT_STATUS, $apiLogEntity->getApiMethod());
        self::assertSame(200, $apiLogEntity->getHttpStatusCode());

        $transactionStatusLogEntity = VsyPaymentWorldlineTransactionStatusLogQuery::create()->findOneByFkWorldlineApiLog($apiLogEntity->getIdWorldlineApiLog());
        self::assertNotNull($transactionStatusLogEntity);
        self::assertSame('CLIENT_NOT_ELIGIBLE_FOR_SELECTED_PAYMENT_PRODUCT', $transactionStatusLogEntity->getStatus());
    }

    /**
     * @return void
     */
    public function testGetHostedCheckoutStatusSavesTokenAndThreeDSecureResultsCorrectlyWhenReturned(): void
    {
        // Arrange
        $customerWithOrders = $this->tester->createCustomerTransfer(static::TEST_USERNAME, static::TEST_PASSWORD);
        $quote = $this->tester->createQuoteTransfer($customerWithOrders, [$this->tester->createProductTransfer()], $this->tester->createPaymentTransfer()->toArray());
        $saveOrderTransfer = $this->tester->createOrderTransfer($quote);

        $orderReference = $saveOrderTransfer->getOrderReference();

        $paymentWorldlineTransfer = $this->tester->haveHostedCheckout($saveOrderTransfer, $quote);

        $pathToResponses = __DIR__ . '/../_data/get_hosted_checkout_status_response_PAYMENT_CREATED_tokenization_successful.json';
        $body = file_get_contents($pathToResponses);

        $apiLogger = $this->getMockBuilder(WorldlineApiCallLoggerInterface::class)->getMock();

        $wlConnection = new TestingConnection(
            $apiLogger,
            $this->getMockConnectionResponse(200, ['Content-Type' => 'application/json'], $body),
        );
        $businessFactoryMock = $this->createBusinessFactoryMock($wlConnection);
        $sut = $this->createFacadeMock($wlConnection, $businessFactoryMock);

        $idSalesOrder = $saveOrderTransfer->getIdSalesOrder();
        $salesOrderEntity = SpySalesOrderQuery::create()->findOneByIdSalesOrder($idSalesOrder);
        $orderItems = $salesOrderEntity->getItems()->getArrayCopy();
        $orderTransfer = new OrderTransfer();
        $orderTransfer->setOrderReference($orderReference);
        $orderTransfer->setCustomerReference($customerWithOrders->getCustomerReference());
        $orderTransfer = (new SalesFacade())->getCustomerOrderByOrderReference($orderTransfer);

        // Act
        $sut->handleGetHostedCheckoutStatusCommand($orderItems, $orderTransfer);

        // Assert
        $vsyPamentWorldline = VsyPaymentWorldlineQuery::create()->findOneByMerchantReference($orderReference);

        $vsyWorldlineApiLogEntities = VsyWorldlineApiLogQuery::create()
            ->filterByFkPaymentWorldline($vsyPamentWorldline->getIdPaymentWorldline())
            ->find();
        self::assertCount(1, $vsyWorldlineApiLogEntities);

        /** @var \Orm\Zed\Worldline\Persistence\VsyWorldlineApiLog $apiLogEntity */
        $apiLogEntity = $vsyWorldlineApiLogEntities->offsetGet(0);
        self::assertSame(WorldlineConstants::WORLDLINE_API_METHOD_GET_HOSTED_CHECKOUT_STATUS, $apiLogEntity->getApiMethod());
        self::assertSame(200, $apiLogEntity->getHttpStatusCode());

        $vsyWorldlineTokenEntity = VsyWorldlineTokenQuery::create()->findOneByFkCustomer($customerWithOrders->getIdCustomer());
        self::assertNotNull($vsyWorldlineTokenEntity);
        self::assertSame('786e59e9-ea32-4b1e-bb57-0e36347eb8b5', $vsyWorldlineTokenEntity->getToken());
        self::assertSame('worldlineCreditCardVisa', $vsyWorldlineTokenEntity->getPaymentMethodKey());
        self::assertSame('************1111', $vsyWorldlineTokenEntity->getObfuscatedCardNumber());

        $vsyWorldLineThreeDSecureResultEntity = VsyWorldlineThreeDSecureResultQuery::create()
            ->findOneByIdThreeDSecureResult(
                $vsyWorldlineTokenEntity->getFkInitialThreeDSecureResult(),
            );

        self::assertNotNull($vsyWorldLineThreeDSecureResultEntity);
        self::assertSame('ED8E162C-D239-49BB-B027-EDC3E923274E', $vsyWorldLineThreeDSecureResultEntity->getAcsTransactionId());
        self::assertSame('frictionless', $vsyWorldLineThreeDSecureResultEntity->getMethod());
        self::assertSame('202301240937', $vsyWorldLineThreeDSecureResultEntity->getUtcTimestamp());
    }

    /**
     * @return void
     */
    public function testGetHostedCheckoutStatusSavesTokenAndThreeDSecureResultsCorrectlyWhenPreviousTokensExistButNewTokenIsAdded(): void
    {
        // Arrange
        $customerWithOrders = $this->tester->createCustomerTransfer(static::TEST_USERNAME, static::TEST_PASSWORD);
        $quote = $this->tester->createQuoteTransfer($customerWithOrders, [$this->tester->createProductTransfer()], $this->tester->createPaymentTransfer()->toArray());
        $saveOrderTransfer = $this->tester->createOrderTransfer($quote);

        $orderReference = $saveOrderTransfer->getOrderReference();

        $paymentWorldlineTransfer = $this->tester->haveHostedCheckout($saveOrderTransfer, $quote);
        $token1Transfer = $this->tester->havePaymentTokenWithThreeDSecureDataForCustomer($customerWithOrders);
        $token2Transfer = $this->tester->havePaymentTokenWithThreeDSecureDataForCustomer($customerWithOrders);

        $pathToResponses = __DIR__ . '/../_data/get_hosted_checkout_status_response_PAYMENT_CREATED_tokenization_successful.json';
        $body = file_get_contents($pathToResponses);

        $bodyArray = json_decode($body, true);

        $newToken = 'expected_token';

        $bodyArray['createdPaymentOutput']['tokens'] = $token1Transfer->getToken() . ',' . $token2Transfer->getToken() . ',' . $newToken;

        $body = json_encode($bodyArray);

        $apiLogger = $this->getMockBuilder(WorldlineApiCallLoggerInterface::class)->getMock();

        $wlConnection = new TestingConnection(
            $apiLogger,
            $this->getMockConnectionResponse(200, ['Content-Type' => 'application/json'], $body),
        );
        $businessFactoryMock = $this->createBusinessFactoryMock($wlConnection);
        $sut = $this->createFacadeMock($wlConnection, $businessFactoryMock);

        $idSalesOrder = $saveOrderTransfer->getIdSalesOrder();
        $salesOrderEntity = SpySalesOrderQuery::create()->findOneByIdSalesOrder($idSalesOrder);
        $orderItems = $salesOrderEntity->getItems()->getArrayCopy();
        $orderTransfer = new OrderTransfer();
        $orderTransfer->setOrderReference($orderReference);
        $orderTransfer->setCustomerReference($customerWithOrders->getCustomerReference());
        $orderTransfer = (new SalesFacade())->getCustomerOrderByOrderReference($orderTransfer);

        // Act
        $sut->handleGetHostedCheckoutStatusCommand($orderItems, $orderTransfer);

        // Assert
        $vsyPamentWorldline = VsyPaymentWorldlineQuery::create()->findOneByMerchantReference($orderReference);

        $vsyWorldlineApiLogEntities = VsyWorldlineApiLogQuery::create()
            ->filterByFkPaymentWorldline($vsyPamentWorldline->getIdPaymentWorldline())
            ->find();
        self::assertCount(1, $vsyWorldlineApiLogEntities);

        /** @var \Orm\Zed\Worldline\Persistence\VsyWorldlineApiLog $apiLogEntity */
        $apiLogEntity = $vsyWorldlineApiLogEntities->offsetGet(0);
        self::assertSame(WorldlineConstants::WORLDLINE_API_METHOD_GET_HOSTED_CHECKOUT_STATUS, $apiLogEntity->getApiMethod());
        self::assertSame(200, $apiLogEntity->getHttpStatusCode());

        $vsyWorldlineTokenEntities = VsyWorldlineTokenQuery::create()->filterByFkCustomer($customerWithOrders->getIdCustomer());
        self::assertNotNull($vsyWorldlineTokenEntities);
        $newTokenFound = false;

        $fkInitialThreeDSecureResult = null;
        foreach ($vsyWorldlineTokenEntities as /** @var \Orm\Zed\Worldline\Persistence\VsyWorldlineToken */ $vsyWorldlineTokenEntity) {
            if ($vsyWorldlineTokenEntity->getToken() === $newToken) {
                $newTokenFound = true;
                self::assertSame('worldlineCreditCardVisa', $vsyWorldlineTokenEntity->getPaymentMethodKey());
                self::assertSame('************1111', $vsyWorldlineTokenEntity->getObfuscatedCardNumber());
                $fkInitialThreeDSecureResult = $vsyWorldlineTokenEntity->getFkInitialThreeDSecureResult();
            }
        }
        self::assertTrue($newTokenFound);

        $vsyWorldLineThreeDSecureResultEntity = null;
        if ($fkInitialThreeDSecureResult) {
            $vsyWorldLineThreeDSecureResultEntity = VsyWorldlineThreeDSecureResultQuery::create()
                ->findOneByIdThreeDSecureResult(
                    $fkInitialThreeDSecureResult,
                );
        }

        self::assertNotNull($vsyWorldLineThreeDSecureResultEntity);
        self::assertSame('ED8E162C-D239-49BB-B027-EDC3E923274E', $vsyWorldLineThreeDSecureResultEntity->getAcsTransactionId());
        self::assertSame('frictionless', $vsyWorldLineThreeDSecureResultEntity->getMethod());
        self::assertSame('202301240937', $vsyWorldLineThreeDSecureResultEntity->getUtcTimestamp());
    }

    /**
     * @return void
     */
    public function testGetHostedCheckoutStatusUpdatesTokenAndThreeDSecureResultsCorrectlyWhenPreviouslyExistingTokenIsUsed(): void
    {
        // Arrange
        $customerWithOrders = $this->tester->createCustomerTransfer(static::TEST_USERNAME, static::TEST_PASSWORD);
        $quote = $this->tester->createQuoteTransfer($customerWithOrders, [$this->tester->createProductTransfer()], $this->tester->createPaymentTransfer()->toArray());
        $saveOrderTransfer = $this->tester->createOrderTransfer($quote);

        $orderReference = $saveOrderTransfer->getOrderReference();

        $paymentWorldlineTransfer = $this->tester->haveHostedCheckout($saveOrderTransfer, $quote);

        $token1Transfer = $this->tester->havePaymentTokenWithThreeDSecureDataForCustomer($customerWithOrders);
        $token2Transfer = $this->tester->havePaymentTokenWithThreeDSecureDataForCustomer($customerWithOrders);

        $pathToResponses = __DIR__ . '/../_data/get_hosted_checkout_status_response_PAYMENT_CREATED_tokenization_successful_with_token.json';
        $body = file_get_contents($pathToResponses);

        $bodyArray = json_decode($body, true);
        $bodyArray['createdPaymentOutput']['payment']['paymentOutput']['cardPaymentMethodSpecificOutput']['token'] = $token1Transfer->getToken();
        $bodyArray['createdPaymentOutput']['tokens'] = $token1Transfer->getToken() . ',' . $token2Transfer->getToken();

        $body = json_encode($bodyArray);

        $apiLogger = $this->getMockBuilder(WorldlineApiCallLoggerInterface::class)->getMock();

        $wlConnection = new TestingConnection(
            $apiLogger,
            $this->getMockConnectionResponse(200, ['Content-Type' => 'application/json'], $body),
        );
        $businessFactoryMock = $this->createBusinessFactoryMock($wlConnection);
        $sut = $this->createFacadeMock($wlConnection, $businessFactoryMock);

        $idSalesOrder = $saveOrderTransfer->getIdSalesOrder();
        $salesOrderEntity = SpySalesOrderQuery::create()->findOneByIdSalesOrder($idSalesOrder);
        $orderItems = $salesOrderEntity->getItems()->getArrayCopy();
        $orderTransfer = new OrderTransfer();
        $orderTransfer->setOrderReference($orderReference);
        $orderTransfer->setCustomerReference($customerWithOrders->getCustomerReference());
        $orderTransfer = (new SalesFacade())->getCustomerOrderByOrderReference($orderTransfer);

        // Act
        $sut->handleGetHostedCheckoutStatusCommand($orderItems, $orderTransfer);

        // Assert
        $vsyPamentWorldline = VsyPaymentWorldlineQuery::create()->findOneByMerchantReference($orderReference);

        $vsyWorldlineApiLogEntities = VsyWorldlineApiLogQuery::create()
            ->filterByFkPaymentWorldline($vsyPamentWorldline->getIdPaymentWorldline())
            ->find();
        self::assertCount(1, $vsyWorldlineApiLogEntities);

        /** @var \Orm\Zed\Worldline\Persistence\VsyWorldlineApiLog $apiLogEntity */
        $apiLogEntity = $vsyWorldlineApiLogEntities->offsetGet(0);
        self::assertSame(WorldlineConstants::WORLDLINE_API_METHOD_GET_HOSTED_CHECKOUT_STATUS, $apiLogEntity->getApiMethod());
        self::assertSame(200, $apiLogEntity->getHttpStatusCode());

        $vsyWorldlineTokenEntities = VsyWorldlineTokenQuery::create()->filterByFkCustomer($customerWithOrders->getIdCustomer());
        self::assertNotNull($vsyWorldlineTokenEntities);
        $newTokenFound = false;

        foreach ($vsyWorldlineTokenEntities as /** @var \Orm\Zed\Worldline\Persistence\VsyWorldlineToken */ $vsyWorldlineTokenEntity) {
            if ($vsyWorldlineTokenEntity->getToken() === $token1Transfer->getToken()) {
                $newTokenFound = true;
                self::assertSame('worldlineCreditCardVisa', $vsyWorldlineTokenEntity->getPaymentMethodKey());
                self::assertSame('************9026', $vsyWorldlineTokenEntity->getObfuscatedCardNumber());
                self::assertSame('0525', $vsyWorldlineTokenEntity->getExpiryMonth());
            }
        }
        self::assertTrue($newTokenFound);
    }

    /**
     * @return void
     */
    public function testGetPaymentStatusSetsCorrectPendingApprovalStateInVsyPaymentWorldlineTransactionStatusLogWhenGetPaymentReturnedPendingApproval(): void
    {
        // Arrange
        $customerWithOrders = $this->tester->createCustomerTransfer(static::TEST_USERNAME, static::TEST_PASSWORD);
        $quote = $this->tester->createQuoteTransfer($customerWithOrders, [$this->tester->createProductTransfer()], $this->tester->createPaymentTransfer()->toArray());
        $saveOrderTransfer = $this->tester->createOrderTransfer($quote);

        $orderReference = $saveOrderTransfer->getOrderReference();

        $paymentWorldlineTransfer = $this->tester->haveHostedCheckout($saveOrderTransfer, $quote);

        $pathToResponses = __DIR__ . '/../_data/get_payment_response.json';
        $body = file_get_contents($pathToResponses);
        $bodyArray = json_decode($body, true);
        $bodyArray['id'] = $paymentWorldlineTransfer->getPaymentId();
        $bodyArray['paymentOutput']['references']['merchantReference'] = $orderReference;

        $body = json_encode($bodyArray, JSON_PRETTY_PRINT);

        $apiLogger = $this->getMockBuilder(WorldlineApiCallLoggerInterface::class)->getMock();

        $wlConnection = new TestingConnection(
            $apiLogger,
            $this->getMockConnectionResponse(200, ['Content-Type' => 'application/json'], $body),
        );
        $businessFactoryMock = $this->createBusinessFactoryMock($wlConnection);
        $sut = $this->createFacadeMock($wlConnection, $businessFactoryMock);

        $idSalesOrder = $saveOrderTransfer->getIdSalesOrder();
        $salesOrderEntity = SpySalesOrderQuery::create()->findOneByIdSalesOrder($idSalesOrder);
        $orderItems = $salesOrderEntity->getItems()->getArrayCopy();
        $orderTransfer = new OrderTransfer();
        $orderTransfer->setOrderReference($orderReference);
        $orderTransfer->setCustomerReference($customerWithOrders->getCustomerReference());
        $orderTransfer = (new SalesFacade())->getCustomerOrderByOrderReference($orderTransfer);

        // Act
        $sut->handleGetPaymentStatusCommand($orderItems, $orderTransfer);

        // Assert
        $vsyPamentWorldline = VsyPaymentWorldlineQuery::create()->findOneByMerchantReference($orderReference);

        $vsyWorldlineApiLogEntities = VsyWorldlineApiLogQuery::create()
            ->filterByFkPaymentWorldline($vsyPamentWorldline->getIdPaymentWorldline())
            ->find();
        self::assertCount(1, $vsyWorldlineApiLogEntities);

        /** @var \Orm\Zed\Worldline\Persistence\VsyWorldlineApiLog $apiLogEntity */
        $apiLogEntity = $vsyWorldlineApiLogEntities->offsetGet(0);
        self::assertSame(WorldlineConstants::WORLDLINE_API_METHOD_GET_PAYMENT, $apiLogEntity->getApiMethod());
        self::assertSame(200, $apiLogEntity->getHttpStatusCode());

        $transactionStatusLogEntity = VsyPaymentWorldlineTransactionStatusLogQuery::create()->findOneByFkWorldlineApiLog($apiLogEntity->getIdWorldlineApiLog());
        self::assertNotNull($transactionStatusLogEntity);
        self::assertSame(WorldlineConstants::STATUS_PAYMENT_PENDING_APPROVAL, $transactionStatusLogEntity->getStatus());
        self::assertTrue($transactionStatusLogEntity->getAuthorized());
    }

    /**
     * @return void
     */
    public function testGetPaymentStatusSetsCorrectPendingCaptureStateInVsyPaymentWorldlineTransactionStatusLogWhenGetPaymentReturnedPendingApproval(): void
    {
        // Arrange
        $customerWithOrders = $this->tester->createCustomerTransfer(static::TEST_USERNAME, static::TEST_PASSWORD);
        $quote = $this->tester->createQuoteTransfer($customerWithOrders, [$this->tester->createProductTransfer()], $this->tester->createPaymentTransfer()->toArray());
        $saveOrderTransfer = $this->tester->createOrderTransfer($quote);

        $orderReference = $saveOrderTransfer->getOrderReference();

        $paymentWorldlineTransfer = $this->tester->haveHostedCheckout($saveOrderTransfer, $quote);

        $pathToResponses = __DIR__ . '/../_data/get_payment_pending_capture_response.json';
        $body = file_get_contents($pathToResponses);
        $bodyArray = json_decode($body, true);
        $bodyArray['id'] = $paymentWorldlineTransfer->getPaymentId();
        $bodyArray['paymentOutput']['references']['merchantReference'] = $orderReference;

        $body = json_encode($bodyArray, JSON_PRETTY_PRINT);

        $apiLogger = $this->getMockBuilder(WorldlineApiCallLoggerInterface::class)->getMock();

        $wlConnection = new TestingConnection(
            $apiLogger,
            $this->getMockConnectionResponse(200, ['Content-Type' => 'application/json'], $body),
        );
        $businessFactoryMock = $this->createBusinessFactoryMock($wlConnection);
        $sut = $this->createFacadeMock($wlConnection, $businessFactoryMock);

        $idSalesOrder = $saveOrderTransfer->getIdSalesOrder();
        $salesOrderEntity = SpySalesOrderQuery::create()->findOneByIdSalesOrder($idSalesOrder);
        $orderItems = $salesOrderEntity->getItems()->getArrayCopy();
        $orderTransfer = new OrderTransfer();
        $orderTransfer->setOrderReference($orderReference);
        $orderTransfer->setCustomerReference($customerWithOrders->getCustomerReference());
        $orderTransfer = (new SalesFacade())->getCustomerOrderByOrderReference($orderTransfer);

        // Act
        $sut->handleGetPaymentStatusCommand($orderItems, $orderTransfer);

        // Assert
        $vsyPamentWorldline = VsyPaymentWorldlineQuery::create()->findOneByMerchantReference($orderReference);

        $vsyWorldlineApiLogEntities = VsyWorldlineApiLogQuery::create()
            ->filterByFkPaymentWorldline($vsyPamentWorldline->getIdPaymentWorldline())
            ->find();
        self::assertCount(1, $vsyWorldlineApiLogEntities);

        /** @var \Orm\Zed\Worldline\Persistence\VsyWorldlineApiLog $apiLogEntity */
        $apiLogEntity = $vsyWorldlineApiLogEntities->offsetGet(0);
        self::assertSame(WorldlineConstants::WORLDLINE_API_METHOD_GET_PAYMENT, $apiLogEntity->getApiMethod());
        self::assertSame(200, $apiLogEntity->getHttpStatusCode());

        $transactionStatusLogEntity = VsyPaymentWorldlineTransactionStatusLogQuery::create()->findOneByFkWorldlineApiLog($apiLogEntity->getIdWorldlineApiLog());
        self::assertNotNull($transactionStatusLogEntity);
        self::assertSame(WorldlineConstants::STATUS_PENDING_CAPTURE, $transactionStatusLogEntity->getStatus());
        self::assertTrue($transactionStatusLogEntity->getAuthorized());
        self::assertSame(WorldlineConstants::STATUS_CATEGORY_PENDING_MERCHANT, $transactionStatusLogEntity->getStatusCategory());
    }

    /**
     * @return void
     */
    public function testGetPaymentStatusSetsCorrectCaptureRequestedStateInVsyPaymentWorldlineTransactionStatusLogWhenGetPaymentReturnedPendingApproval(): void
    {
        // Arrange
        $customerWithOrders = $this->tester->createCustomerTransfer(static::TEST_USERNAME, static::TEST_PASSWORD);
        $quote = $this->tester->createQuoteTransfer($customerWithOrders, [$this->tester->createProductTransfer()], $this->tester->createPaymentTransfer()->toArray());
        $saveOrderTransfer = $this->tester->createOrderTransfer($quote);

        $orderReference = $saveOrderTransfer->getOrderReference();

        $paymentWorldlineTransfer = $this->tester->haveHostedCheckout($saveOrderTransfer, $quote);

        $pathToResponses = __DIR__ . '/../_data/get_payment_capture_requested_response.json';
        $body = file_get_contents($pathToResponses);
        $bodyArray = json_decode($body, true);
        $bodyArray['id'] = $paymentWorldlineTransfer->getPaymentId();
        $bodyArray['paymentOutput']['references']['merchantReference'] = $orderReference;

        $body = json_encode($bodyArray, JSON_PRETTY_PRINT);

        $apiLogger = $this->getMockBuilder(WorldlineApiCallLoggerInterface::class)->getMock();

        $wlConnection = new TestingConnection(
            $apiLogger,
            $this->getMockConnectionResponse(200, ['Content-Type' => 'application/json'], $body),
        );
        $businessFactoryMock = $this->createBusinessFactoryMock($wlConnection);
        $sut = $this->createFacadeMock($wlConnection, $businessFactoryMock);

        $idSalesOrder = $saveOrderTransfer->getIdSalesOrder();
        $salesOrderEntity = SpySalesOrderQuery::create()->findOneByIdSalesOrder($idSalesOrder);
        $orderItems = $salesOrderEntity->getItems()->getArrayCopy();
        $orderTransfer = new OrderTransfer();
        $orderTransfer->setOrderReference($orderReference);
        $orderTransfer->setCustomerReference($customerWithOrders->getCustomerReference());
        $orderTransfer = (new SalesFacade())->getCustomerOrderByOrderReference($orderTransfer);

        // Act
        $sut->handleGetPaymentStatusCommand($orderItems, $orderTransfer);

        // Assert
        $vsyPamentWorldline = VsyPaymentWorldlineQuery::create()->findOneByMerchantReference($orderReference);

        $vsyWorldlineApiLogEntities = VsyWorldlineApiLogQuery::create()
            ->filterByFkPaymentWorldline($vsyPamentWorldline->getIdPaymentWorldline())
            ->find();
        self::assertCount(1, $vsyWorldlineApiLogEntities);

        /** @var \Orm\Zed\Worldline\Persistence\VsyWorldlineApiLog $apiLogEntity */
        $apiLogEntity = $vsyWorldlineApiLogEntities->offsetGet(0);
        self::assertSame(WorldlineConstants::WORLDLINE_API_METHOD_GET_PAYMENT, $apiLogEntity->getApiMethod());
        self::assertSame(200, $apiLogEntity->getHttpStatusCode());

        $transactionStatusLogEntity = VsyPaymentWorldlineTransactionStatusLogQuery::create()->findOneByFkWorldlineApiLog($apiLogEntity->getIdWorldlineApiLog());
        self::assertNotNull($transactionStatusLogEntity);
        self::assertSame(WorldlineConstants::STATUS_CAPTURE_REQUESTED, $transactionStatusLogEntity->getStatus());
        self::assertTrue($transactionStatusLogEntity->getAuthorized());
        self::assertSame(WorldlineConstants::STATUS_CATEGORY_PENDING_CONNECT_OR_3RD_PARTY, $transactionStatusLogEntity->getStatusCategory());
    }

    /**
     * @return void
     */
    public function testGetPaymentStatusSetsCorrectCapturedStateInVsyPaymentWorldlineTransactionStatusLogWhenGetPaymentReturnedPendingApproval(): void
    {
        // Arrange
        $customerWithOrders = $this->tester->createCustomerTransfer(static::TEST_USERNAME, static::TEST_PASSWORD);
        $quote = $this->tester->createQuoteTransfer($customerWithOrders, [$this->tester->createProductTransfer()], $this->tester->createPaymentTransfer()->toArray());
        $saveOrderTransfer = $this->tester->createOrderTransfer($quote);

        $orderReference = $saveOrderTransfer->getOrderReference();

        $paymentWorldlineTransfer = $this->tester->haveHostedCheckout($saveOrderTransfer, $quote);

        $pathToResponses = __DIR__ . '/../_data/get_payment_captured_response.json';
        $body = file_get_contents($pathToResponses);
        $bodyArray = json_decode($body, true);
        $bodyArray['id'] = $paymentWorldlineTransfer->getPaymentId();
        $bodyArray['paymentOutput']['references']['merchantReference'] = $orderReference;

        $body = json_encode($bodyArray, JSON_PRETTY_PRINT);

        $apiLogger = $this->getMockBuilder(WorldlineApiCallLoggerInterface::class)->getMock();

        $wlConnection = new TestingConnection(
            $apiLogger,
            $this->getMockConnectionResponse(200, ['Content-Type' => 'application/json'], $body),
        );
        $businessFactoryMock = $this->createBusinessFactoryMock($wlConnection);
        $sut = $this->createFacadeMock($wlConnection, $businessFactoryMock);

        $idSalesOrder = $saveOrderTransfer->getIdSalesOrder();
        $salesOrderEntity = SpySalesOrderQuery::create()->findOneByIdSalesOrder($idSalesOrder);
        $orderItems = $salesOrderEntity->getItems()->getArrayCopy();
        $orderTransfer = new OrderTransfer();
        $orderTransfer->setOrderReference($orderReference);
        $orderTransfer->setCustomerReference($customerWithOrders->getCustomerReference());
        $orderTransfer = (new SalesFacade())->getCustomerOrderByOrderReference($orderTransfer);

        // Act
        $sut->handleGetPaymentStatusCommand($orderItems, $orderTransfer);

        // Assert
        $vsyPamentWorldline = VsyPaymentWorldlineQuery::create()->findOneByMerchantReference($orderReference);

        $vsyWorldlineApiLogEntities = VsyWorldlineApiLogQuery::create()
            ->filterByFkPaymentWorldline($vsyPamentWorldline->getIdPaymentWorldline())
            ->find();
        self::assertCount(1, $vsyWorldlineApiLogEntities);

        /** @var \Orm\Zed\Worldline\Persistence\VsyWorldlineApiLog $apiLogEntity */
        $apiLogEntity = $vsyWorldlineApiLogEntities->offsetGet(0);
        self::assertSame(WorldlineConstants::WORLDLINE_API_METHOD_GET_PAYMENT, $apiLogEntity->getApiMethod());
        self::assertSame(200, $apiLogEntity->getHttpStatusCode());

        $transactionStatusLogEntity = VsyPaymentWorldlineTransactionStatusLogQuery::create()->findOneByFkWorldlineApiLog($apiLogEntity->getIdWorldlineApiLog());
        self::assertNotNull($transactionStatusLogEntity);
        self::assertSame(WorldlineConstants::STATUS_CAPTURED, $transactionStatusLogEntity->getStatus());
        self::assertTrue($transactionStatusLogEntity->getAuthorized());
        self::assertSame(WorldlineConstants::STATUS_CATEGORY_COMPLETED, $transactionStatusLogEntity->getStatusCategory());
    }

    /**
     * @return void
     */
    public function testGetPaymentStatusSetsCorrectPaidStateInVsyPaymentWorldlineTransactionStatusLogWhenGetPaymentReturnedPaid(): void
    {
        // Arrange
        $customerWithOrders = $this->tester->createCustomerTransfer(static::TEST_USERNAME, static::TEST_PASSWORD);
        $quote = $this->tester->createQuoteTransfer($customerWithOrders, [$this->tester->createProductTransfer()], $this->tester->createPaymentTransfer()->toArray());
        $saveOrderTransfer = $this->tester->createOrderTransfer($quote);

        $orderReference = $saveOrderTransfer->getOrderReference();

        $paymentWorldlineTransfer = $this->tester->haveHostedCheckout($saveOrderTransfer, $quote);

        $pathToResponses = __DIR__ . '/../_data/get_payment_paid_response.json';
        $body = file_get_contents($pathToResponses);
        $bodyArray = json_decode($body, true);
        $bodyArray['id'] = $paymentWorldlineTransfer->getPaymentId();
        $bodyArray['paymentOutput']['references']['merchantReference'] = $orderReference;

        $body = json_encode($bodyArray, JSON_PRETTY_PRINT);

        $apiLogger = $this->getMockBuilder(WorldlineApiCallLoggerInterface::class)->getMock();

        $wlConnection = new TestingConnection(
            $apiLogger,
            $this->getMockConnectionResponse(200, ['Content-Type' => 'application/json'], $body),
        );
        $businessFactoryMock = $this->createBusinessFactoryMock($wlConnection);
        $sut = $this->createFacadeMock($wlConnection, $businessFactoryMock);

        $idSalesOrder = $saveOrderTransfer->getIdSalesOrder();
        $salesOrderEntity = SpySalesOrderQuery::create()->findOneByIdSalesOrder($idSalesOrder);
        $orderItems = $salesOrderEntity->getItems()->getArrayCopy();
        $orderTransfer = new OrderTransfer();
        $orderTransfer->setOrderReference($orderReference);
        $orderTransfer->setCustomerReference($customerWithOrders->getCustomerReference());
        $orderTransfer = (new SalesFacade())->getCustomerOrderByOrderReference($orderTransfer);

        // Act
        $sut->handleGetPaymentStatusCommand($orderItems, $orderTransfer);

        // Assert
        $vsyPamentWorldline = VsyPaymentWorldlineQuery::create()->findOneByMerchantReference($orderReference);

        $vsyWorldlineApiLogEntities = VsyWorldlineApiLogQuery::create()
            ->filterByFkPaymentWorldline($vsyPamentWorldline->getIdPaymentWorldline())
            ->find();
        self::assertCount(1, $vsyWorldlineApiLogEntities);

        /** @var \Orm\Zed\Worldline\Persistence\VsyWorldlineApiLog $apiLogEntity */
        $apiLogEntity = $vsyWorldlineApiLogEntities->offsetGet(0);
        self::assertSame(WorldlineConstants::WORLDLINE_API_METHOD_GET_PAYMENT, $apiLogEntity->getApiMethod());
        self::assertSame(200, $apiLogEntity->getHttpStatusCode());

        $transactionStatusLogEntity = VsyPaymentWorldlineTransactionStatusLogQuery::create()->findOneByFkWorldlineApiLog($apiLogEntity->getIdWorldlineApiLog());
        self::assertNotNull($transactionStatusLogEntity);
        self::assertSame(WorldlineConstants::STATUS_PAID, $transactionStatusLogEntity->getStatus());
        self::assertTrue($transactionStatusLogEntity->getAuthorized());
        self::assertSame(WorldlineConstants::STATUS_CATEGORY_COMPLETED, $transactionStatusLogEntity->getStatusCategory());
    }

    /**
     * @return void
     */
    public function testHandleApprovePaymentCommandSetsCorrectCaptureRequestedStateInVsyPaymentWorldlineTransactionStatusLogWhenApprovePaymentReturnedCaptureRequested(): void
    {
        // Arrange
        $customerWithOrders = $this->tester->createCustomerTransfer(static::TEST_USERNAME, static::TEST_PASSWORD);
        $quote = $this->tester->createQuoteTransfer($customerWithOrders, [$this->tester->createProductTransfer()], $this->tester->createPaymentTransfer()->toArray());
        $saveOrderTransfer = $this->tester->createOrderTransfer($quote);

        $orderReference = $saveOrderTransfer->getOrderReference();

        $paymentWorldlineTransfer = $this->tester->haveHostedCheckout($saveOrderTransfer, $quote);

        $pathToResponses = __DIR__ . '/../_data/approve_payment_response.json';
        $body = file_get_contents($pathToResponses);
        $bodyArray = json_decode($body, true);
        $bodyArray['id'] = $paymentWorldlineTransfer->getPaymentId();
        $bodyArray['paymentOutput']['references']['merchantReference'] = $orderReference;

        $body = json_encode($bodyArray, JSON_PRETTY_PRINT);

        $apiLogger = $this->getMockBuilder(WorldlineApiCallLoggerInterface::class)->getMock();

        $wlConnection = new TestingConnection(
            $apiLogger,
            $this->getMockConnectionResponse(200, ['Content-Type' => 'application/json'], $body),
        );
        $businessFactoryMock = $this->createBusinessFactoryMock($wlConnection);
        $sut = $this->createFacadeMock($wlConnection, $businessFactoryMock);

        $idSalesOrder = $saveOrderTransfer->getIdSalesOrder();
        $salesOrderEntity = SpySalesOrderQuery::create()->findOneByIdSalesOrder($idSalesOrder);
        $orderItems = $salesOrderEntity->getItems()->getArrayCopy();
        $orderTransfer = new OrderTransfer();
        $orderTransfer->setOrderReference($orderReference);
        $orderTransfer->setCustomerReference($customerWithOrders->getCustomerReference());
        $orderTransfer = (new SalesFacade())->getCustomerOrderByOrderReference($orderTransfer);

        // Act
        $sut->handleApprovePaymentCommand($orderItems, $orderTransfer);

        // Assert
        $vsyPamentWorldline = VsyPaymentWorldlineQuery::create()->findOneByMerchantReference($orderReference);

        $vsyWorldlineApiLogEntities = VsyWorldlineApiLogQuery::create()
            ->filterByFkPaymentWorldline($vsyPamentWorldline->getIdPaymentWorldline())
            ->find();
        self::assertCount(1, $vsyWorldlineApiLogEntities);

        /** @var \Orm\Zed\Worldline\Persistence\VsyWorldlineApiLog $apiLogEntity */
        $apiLogEntity = $vsyWorldlineApiLogEntities->offsetGet(0);
        self::assertSame(WorldlineConstants::WORLDLINE_API_METHOD_APPROVE_PAYMENT, $apiLogEntity->getApiMethod());
        self::assertSame(200, $apiLogEntity->getHttpStatusCode());

        $transactionStatusLogEntity = VsyPaymentWorldlineTransactionStatusLogQuery::create()->findOneByFkWorldlineApiLog($apiLogEntity->getIdWorldlineApiLog());
        self::assertNotNull($transactionStatusLogEntity);
        self::assertSame(WorldlineConstants::STATUS_CAPTURE_REQUESTED, $transactionStatusLogEntity->getStatus());
        self::assertTrue($transactionStatusLogEntity->getAuthorized());
        self::assertSame(800, $transactionStatusLogEntity->getStatusCode());
        self::assertSame('20140627120735', $transactionStatusLogEntity->getStatusCodeChangeDateTime());
    }

    /**
     * @return void
     */
    public function testHandleApprovePaymentCommandSetsCorrectRejectedCaptureStateInVsyPaymentWorldlineTransactionStatusLogWhenApprovePaymentReturnedRejectedCapture(): void
    {
        // Arrange
        $customerWithOrders = $this->tester->createCustomerTransfer(static::TEST_USERNAME, static::TEST_PASSWORD);
        $quote = $this->tester->createQuoteTransfer($customerWithOrders, [$this->tester->createProductTransfer()], $this->tester->createPaymentTransfer()->toArray());
        $saveOrderTransfer = $this->tester->createOrderTransfer($quote);

        $orderReference = $saveOrderTransfer->getOrderReference();

        $paymentWorldlineTransfer = $this->tester->haveHostedCheckout($saveOrderTransfer, $quote);

        $pathToResponses = __DIR__ . '/../_data/approve_payment_rejected_capture_response.json';
        $body = file_get_contents($pathToResponses);
        $bodyArray = json_decode($body, true);
        $bodyArray['id'] = $paymentWorldlineTransfer->getPaymentId();
        $bodyArray['paymentOutput']['references']['merchantReference'] = $orderReference;

        $body = json_encode($bodyArray, JSON_PRETTY_PRINT);

        $apiLogger = $this->getMockBuilder(WorldlineApiCallLoggerInterface::class)->getMock();

        $wlConnection = new TestingConnection(
            $apiLogger,
            $this->getMockConnectionResponse(200, ['Content-Type' => 'application/json'], $body),
        );
        $businessFactoryMock = $this->createBusinessFactoryMock($wlConnection);
        $sut = $this->createFacadeMock($wlConnection, $businessFactoryMock);

        $idSalesOrder = $saveOrderTransfer->getIdSalesOrder();
        $salesOrderEntity = SpySalesOrderQuery::create()->findOneByIdSalesOrder($idSalesOrder);
        $orderItems = $salesOrderEntity->getItems()->getArrayCopy();
        $orderTransfer = new OrderTransfer();
        $orderTransfer->setOrderReference($orderReference);
        $orderTransfer->setCustomerReference($customerWithOrders->getCustomerReference());
        $orderTransfer = (new SalesFacade())->getCustomerOrderByOrderReference($orderTransfer);

        // Act
        $sut->handleApprovePaymentCommand($orderItems, $orderTransfer);

        // Assert
        $vsyPamentWorldline = VsyPaymentWorldlineQuery::create()->findOneByMerchantReference($orderReference);

        $vsyWorldlineApiLogEntities = VsyWorldlineApiLogQuery::create()
            ->filterByFkPaymentWorldline($vsyPamentWorldline->getIdPaymentWorldline())
            ->find();
        self::assertCount(1, $vsyWorldlineApiLogEntities);

        /** @var \Orm\Zed\Worldline\Persistence\VsyWorldlineApiLog $apiLogEntity */
        $apiLogEntity = $vsyWorldlineApiLogEntities->offsetGet(0);
        self::assertSame(WorldlineConstants::WORLDLINE_API_METHOD_APPROVE_PAYMENT, $apiLogEntity->getApiMethod());
        self::assertSame(200, $apiLogEntity->getHttpStatusCode());

        $transactionStatusLogEntity = VsyPaymentWorldlineTransactionStatusLogQuery::create()->findOneByFkWorldlineApiLog($apiLogEntity->getIdWorldlineApiLog());
        self::assertNotNull($transactionStatusLogEntity);
        self::assertSame(WorldlineConstants::STATUS_REJECTED_CAPTURE, $transactionStatusLogEntity->getStatus());
        self::assertSame(0, $transactionStatusLogEntity->getStatusCode());
        self::assertSame('20170901150000', $transactionStatusLogEntity->getStatusCodeChangeDateTime());
    }

    /**
     * @return void
     */
    public function testExpandOrderTransferWithHostedCheckoutCompletionStatusAndHostedCheckoutId(): void
    {
        // Arrange
        $customerWithOrders = $this->tester->createCustomerTransfer(static::TEST_USERNAME, static::TEST_PASSWORD);
        $quote = $this->tester->createQuoteTransfer($customerWithOrders, [$this->tester->createProductTransfer()], $this->tester->createPaymentTransfer()->toArray());
        $saveOrderTransfer = $this->tester->createOrderTransfer($quote);

        $orderReference = $saveOrderTransfer->getOrderReference();
        $pathToResponses = __DIR__ . '/../_data/create_hosted_checkout_response.json';
        $body = file_get_contents($pathToResponses);

        $bodyArray = json_decode($body, true);

        $bodyArray['merchantReference'] = $orderReference;

        $body = json_encode($bodyArray, JSON_PRETTY_PRINT);

        $apiLogger = $this->getMockBuilder(WorldlineApiCallLoggerInterface::class)->getMock();

        $wlConnection = new TestingConnection(
            $apiLogger,
            $this->getMockConnectionResponse(200, ['Content-Type' => 'application/json'], $body),
        );
        $businessFactoryMock = $this->createBusinessFactoryMock($wlConnection);
        $sut = $this->createFacadeMock($wlConnection, $businessFactoryMock);

        $expectedHostedCheckoutId = self::HOSTED_CHECKOUT_ID;
        $initialTransactionStatus = WorldlineConstants::STATUS_HOSTED_CHECKOUT_STATUS_PAYMENT_CREATED;
        $initialTransactionStatusCategory = null;

        $this->haveHostedCheckout($saveOrderTransfer, $quote, $expectedHostedCheckoutId, $initialTransactionStatus, $initialTransactionStatusCategory);

        $idSalesOrder = $saveOrderTransfer->getIdSalesOrder();
        $salesOrderEntity = SpySalesOrderQuery::create()->findOneByIdSalesOrder($idSalesOrder);
        $orderItems = $salesOrderEntity->getItems();

        foreach ($orderItems as $orderItem) {
            $this->tester->setItemState($orderItem->getIdSalesOrderItem(), self::HOSTED_CHECKOUT_STATUS_PENDING);
        }

        $orderTransfer = new OrderTransfer();
        $orderTransfer->fromArray($saveOrderTransfer->toArray(true, true), true);
        $orderTransfer->setPayments($quote->getPayments());

        // Act
        $orderTransfer = $sut->hydrateOrderTransferWithHostedCheckoutStatusForOrder($orderTransfer);

        // Assert
        self::assertSame(WorldlineConstants::HOSTED_CHECKOUT_COMPLETION_STATUS_SUCCESSFUL, $orderTransfer->getHostedCheckoutCompletionStatus());
        self::assertSame(self::HOSTED_CHECKOUT_ID, $orderTransfer->getHostedCheckoutId());
    }

    /**
     * @return void
     */
    public function testExpandOrderTransferWithHostedCheckoutCompletionStatusAndHostedCheckoutIdWhenStatusIsPending(): void
    {
        // Arrange
        $customerWithOrders = $this->tester->createCustomerTransfer(static::TEST_USERNAME, static::TEST_PASSWORD);
        $quote = $this->tester->createQuoteTransfer($customerWithOrders, [$this->tester->createProductTransfer()], $this->tester->createPaymentTransfer()->toArray());
        $saveOrderTransfer = $this->tester->createOrderTransfer($quote);

        $orderReference = $saveOrderTransfer->getOrderReference();
        $pathToResponses = __DIR__ . '/../_data/get_hosted_checkout_status_response_PAYMENT_CREATED.json';
        $body = file_get_contents($pathToResponses);

        $bodyArray = json_decode($body, true);

        $bodyArray['merchantReference'] = $orderReference;

        $body = json_encode($bodyArray, JSON_PRETTY_PRINT);

        $apiLogger = $this->getMockBuilder(WorldlineApiCallLoggerInterface::class)->getMock();

        $wlConnection = new TestingConnection(
            $apiLogger,
            $this->getMockConnectionResponse(200, ['Content-Type' => 'application/json'], $body),
        );
        $businessFactoryMock = $this->createBusinessFactoryMock($wlConnection);
        $sut = $this->createFacadeMock($wlConnection, $businessFactoryMock);

        $expectedHostedCheckoutId = self::HOSTED_CHECKOUT_ID;
        $initialTransactionStatus = WorldlineConstants::STATUS_HOSTED_CHECKOUT_STATUS_PENDING;
        $initialTransactionStatusCategory = null;

        $this->haveHostedCheckout($saveOrderTransfer, $quote, $expectedHostedCheckoutId, $initialTransactionStatus, $initialTransactionStatusCategory);

        $idSalesOrder = $saveOrderTransfer->getIdSalesOrder();
        $salesOrderEntity = SpySalesOrderQuery::create()->findOneByIdSalesOrder($idSalesOrder);
        $orderItems = $salesOrderEntity->getItems();

        foreach ($orderItems as $orderItem) {
            $this->tester->setItemState($orderItem->getIdSalesOrderItem(), self::HOSTED_CHECKOUT_STATUS_PENDING);
        }

        $orderTransfer = new OrderTransfer();
        $orderTransfer->fromArray($saveOrderTransfer->toArray(true, true), true);
        $orderTransfer->setPayments($quote->getPayments());

        // Act
        $orderTransfer = $sut->hydrateOrderTransferWithHostedCheckoutStatusForOrder($orderTransfer);

        // Assert
        self::assertSame(WorldlineConstants::HOSTED_CHECKOUT_COMPLETION_STATUS_SUCCESSFUL, $orderTransfer->getHostedCheckoutCompletionStatus());
        self::assertSame(self::HOSTED_CHECKOUT_ID, $orderTransfer->getHostedCheckoutId());
    }

    /**
     * @return void
     */
    public function testExpandOrderTransferWithHostedCheckoutCompletionStatusAndHostedCheckoutIdWhenStatusIsCancelledByConsumer(): void
    {
        // Arrange
        $customerWithOrders = $this->tester->createCustomerTransfer(static::TEST_USERNAME, static::TEST_PASSWORD);
        $quote = $this->tester->createQuoteTransfer($customerWithOrders, [$this->tester->createProductTransfer()], $this->tester->createPaymentTransfer()->toArray());
        $saveOrderTransfer = $this->tester->createOrderTransfer($quote);

        $orderReference = $saveOrderTransfer->getOrderReference();
        $pathToResponses = __DIR__ . '/../_data/get_hosted_checkout_status_response_CANCELLED_BY_CONSUMER.json';
        $body = file_get_contents($pathToResponses);

        $bodyArray = json_decode($body, true);

        $bodyArray['merchantReference'] = $orderReference;

        $body = json_encode($bodyArray, JSON_PRETTY_PRINT);

        $apiLogger = $this->getMockBuilder(WorldlineApiCallLoggerInterface::class)->getMock();

        $wlConnection = new TestingConnection(
            $apiLogger,
            $this->getMockConnectionResponse(200, ['Content-Type' => 'application/json'], $body),
        );
        $businessFactoryMock = $this->createBusinessFactoryMock($wlConnection);
        $sut = $this->createFacadeMock($wlConnection, $businessFactoryMock);

        $expectedHostedCheckoutId = self::HOSTED_CHECKOUT_ID;
        $initialTransactionStatus = WorldlineConstants::STATUS_HOSTED_CHECKOUT_STATUS_PENDING;
        $initialTransactionStatusCategory = null;

        $this->haveHostedCheckout($saveOrderTransfer, $quote, $expectedHostedCheckoutId, $initialTransactionStatus, $initialTransactionStatusCategory);

        $idSalesOrder = $saveOrderTransfer->getIdSalesOrder();
        $salesOrderEntity = SpySalesOrderQuery::create()->findOneByIdSalesOrder($idSalesOrder);
        $orderItems = $salesOrderEntity->getItems();

        foreach ($orderItems as $orderItem) {
            $this->tester->setItemState($orderItem->getIdSalesOrderItem(), self::HOSTED_CHECKOUT_STATUS_PENDING);
        }

        $orderTransfer = new OrderTransfer();
        $orderTransfer->fromArray($saveOrderTransfer->toArray(true, true), true);
        $orderTransfer->setPayments($quote->getPayments());

        // Act
        $orderTransfer = $sut->hydrateOrderTransferWithHostedCheckoutStatusForOrder($orderTransfer);

        // Assert
        self::assertSame(WorldlineConstants::HOSTED_CHECKOUT_COMPLETION_STATUS_UNSUCCESSFUL, $orderTransfer->getHostedCheckoutCompletionStatus());
        self::assertSame(self::HOSTED_CHECKOUT_ID, $orderTransfer->getHostedCheckoutId());
    }

    /**
     * @return void
     */
    public function testExpandOrderTransferWithHostedCheckoutCompletionStatusAndHostedCheckoutIdWhenStatusIsCancelledBecauseClientNotEligible(): void
    {
        // Arrange
        $customerWithOrders = $this->tester->createCustomerTransfer(static::TEST_USERNAME, static::TEST_PASSWORD);
        $quote = $this->tester->createQuoteTransfer($customerWithOrders, [$this->tester->createProductTransfer()], $this->tester->createPaymentTransfer()->toArray());
        $saveOrderTransfer = $this->tester->createOrderTransfer($quote);

        $orderReference = $saveOrderTransfer->getOrderReference();
        $pathToResponses = __DIR__ . '/../_data/get_hosted_checkout_status_response_CLIENT_NOT_ELIGIBLE_FOR_SELECTED_PAYMENT_PRODUCT.json';
        $body = file_get_contents($pathToResponses);

        $bodyArray = json_decode($body, true);

        $bodyArray['merchantReference'] = $orderReference;

        $body = json_encode($bodyArray, JSON_PRETTY_PRINT);

        $apiLogger = $this->getMockBuilder(WorldlineApiCallLoggerInterface::class)->getMock();

        $wlConnection = new TestingConnection(
            $apiLogger,
            $this->getMockConnectionResponse(200, ['Content-Type' => 'application/json'], $body),
        );
        $businessFactoryMock = $this->createBusinessFactoryMock($wlConnection);
        $sut = $this->createFacadeMock($wlConnection, $businessFactoryMock);

        $expectedHostedCheckoutId = self::HOSTED_CHECKOUT_ID;
        $initialTransactionStatus = WorldlineConstants::STATUS_HOSTED_CHECKOUT_STATUS_PENDING;
        $initialTransactionStatusCategory = null;

        $this->haveHostedCheckout($saveOrderTransfer, $quote, $expectedHostedCheckoutId, $initialTransactionStatus, $initialTransactionStatusCategory);

        $idSalesOrder = $saveOrderTransfer->getIdSalesOrder();
        $salesOrderEntity = SpySalesOrderQuery::create()->findOneByIdSalesOrder($idSalesOrder);
        $orderItems = $salesOrderEntity->getItems();

        foreach ($orderItems as $orderItem) {
            $this->tester->setItemState($orderItem->getIdSalesOrderItem(), self::HOSTED_CHECKOUT_STATUS_PENDING);
        }

        $orderTransfer = new OrderTransfer();
        $orderTransfer->fromArray($saveOrderTransfer->toArray(true, true), true);
        $orderTransfer->setPayments($quote->getPayments());

        // Act
        $orderTransfer = $sut->hydrateOrderTransferWithHostedCheckoutStatusForOrder($orderTransfer);

        // Assert
        self::assertSame(WorldlineConstants::HOSTED_CHECKOUT_COMPLETION_STATUS_UNSUCCESSFUL, $orderTransfer->getHostedCheckoutCompletionStatus());
        self::assertSame(self::HOSTED_CHECKOUT_ID, $orderTransfer->getHostedCheckoutId());
    }

    /**
     * @return void
     */
    public function testProcessEventMessageProcessesAPaymentEventWithStatusCreatedCorrectlyWhenTheReferencedMerchantReferenceIsKnown(): void
    {
        // Arrange
        $customerWithOrders = $this->tester->createCustomerTransfer(static::TEST_USERNAME, static::TEST_PASSWORD);
        $quote = $this->tester->createQuoteTransfer($customerWithOrders, [$this->tester->createProductTransfer()], $this->tester->createPaymentTransfer()->toArray());
        $saveOrderTransfer = $this->tester->createOrderTransfer($quote);

        // for timestamp conversion of timestamps in worldline time zone
        $this->tester->setConfig(WorldlineConstants::WORLDLINE_TIMEZONE, 'Europe/Paris');

        $orderReference = $saveOrderTransfer->getOrderReference();
        $pathToResponses = __DIR__ . '/../_data/get_hosted_checkout_status_response_PAYMENT_CREATED.json';
        $body = file_get_contents($pathToResponses);

        $bodyArray = json_decode($body, true);

        $bodyArray['merchantReference'] = $orderReference;

        $body = json_encode($bodyArray, JSON_PRETTY_PRINT);

        $apiLogger = $this->getMockBuilder(WorldlineApiCallLoggerInterface::class)->getMock();

        $wlConnection = new TestingConnection(
            $apiLogger,
            $this->getMockConnectionResponse(200, ['Content-Type' => 'application/json'], $body),
        );
        $businessFactoryMock = $this->createBusinessFactoryMock($wlConnection);
        $sut = $this->createFacadeMock($wlConnection, $businessFactoryMock);

        $expectedHostedCheckoutId = self::HOSTED_CHECKOUT_ID;

        $expectedPaymentId = 'expected_payment_id';
        $initialTransactionStatus = WorldlineConstants::STATUS_HOSTED_CHECKOUT_STATUS_PAYMENT_CREATED;
        $initialTransactionStatusCategory = null;

        $this->haveHostedCheckoutWithPaymentId(
            $saveOrderTransfer,
            $quote,
            $expectedPaymentId,
            $expectedHostedCheckoutId,
            $initialTransactionStatus,
            $initialTransactionStatusCategory,
        );

        $exampleFileToLoad = '/../_data/payment_created_event.json';

        $queueMessage = $this->createPaymentEventQueueMessage($exampleFileToLoad, $expectedPaymentId, $orderReference);

        $this->tester->cleanupInMemoryQueue();

        $this->tester->getQueueClient()->sendMessage(WorldlineWebhookConstants::WORLDLINE_WEBHOOK_EVENT_QUEUE_NAME, $queueMessage);

        $queueReceiveMessageTransfer = $this->tester->getQueueClient()->receiveMessages(WorldlineWebhookConstants::WORLDLINE_WEBHOOK_EVENT_QUEUE_NAME)[0];
        // Act
        $sut->processEventMessage($queueReceiveMessageTransfer);

        // Assert
        $restReceiveLogEntity = VsyWorldlineRestReceiveLogQuery::create()->findOneByEventId('97a4fcdf-9a08-4a59-a0c0-69bfd7e1d59d');
        self::assertNotNull($restReceiveLogEntity);
        $restLogEntity = VsyWorldlineRestLogQuery::create()->findOneByEventId('97a4fcdf-9a08-4a59-a0c0-69bfd7e1d59d');
        self::assertSame('6511', $restLogEntity->getMerchantId());

        /** @var \Orm\Zed\Worldline\Persistence\VsyPaymentWorldlineTransactionStatusLog $transactionLogEntity */
        $transactionLogEntity = $restLogEntity->getVsyPaymentWorldlineTransactionStatusLogs()->getFirst();

        self::assertSame(WorldlineConstants::STATUS_CREATED, $transactionLogEntity->getStatus());
        self::assertSame(WorldlineConstants::STATUS_CREATED, $transactionLogEntity->getStatusCategory());
        self::assertSame('20170901150000', $transactionLogEntity->getStatusCodeChangeDateTime());
        self::assertSame(0, $transactionLogEntity->getStatusCode());
    }

    /**
     * @return void
     */
    public function testProcessEventMessageProcessesAPaymentEventWithStatusPendingApprovalCorrectlyWhenTheReferencedMerchantReferenceIsKnown(): void
    {
        // Arrange
        $customerWithOrders = $this->tester->createCustomerTransfer(static::TEST_USERNAME, static::TEST_PASSWORD);
        $quote = $this->tester->createQuoteTransfer($customerWithOrders, [$this->tester->createProductTransfer()], $this->tester->createPaymentTransfer()->toArray());
        $saveOrderTransfer = $this->tester->createOrderTransfer($quote);

        // for timestamp conversion of timestamps in worldline time zone
        $this->tester->setConfig(WorldlineConstants::WORLDLINE_TIMEZONE, 'Europe/Paris');

        $orderReference = $saveOrderTransfer->getOrderReference();
        $pathToResponses = __DIR__ . '/../_data/get_hosted_checkout_status_response_PAYMENT_CREATED.json';
        $body = file_get_contents($pathToResponses);

        $bodyArray = json_decode($body, true);

        $bodyArray['merchantReference'] = $orderReference;

        $body = json_encode($bodyArray, JSON_PRETTY_PRINT);

        $apiLogger = $this->getMockBuilder(WorldlineApiCallLoggerInterface::class)->getMock();

        $wlConnection = new TestingConnection(
            $apiLogger,
            $this->getMockConnectionResponse(200, ['Content-Type' => 'application/json'], $body),
        );
        $businessFactoryMock = $this->createBusinessFactoryMock($wlConnection);
        $sut = $this->createFacadeMock($wlConnection, $businessFactoryMock);

        $expectedHostedCheckoutId = self::HOSTED_CHECKOUT_ID;

        $expectedPaymentId = 'expected_payment_id';
        $initialTransactionStatus = WorldlineConstants::STATUS_HOSTED_CHECKOUT_STATUS_PAYMENT_CREATED;
        $initialTransactionStatusCategory = null;

        $this->haveHostedCheckoutWithPaymentId(
            $saveOrderTransfer,
            $quote,
            $expectedPaymentId,
            $expectedHostedCheckoutId,
            $initialTransactionStatus,
            $initialTransactionStatusCategory,
        );

        $exampleFileToLoad = '/../_data/payment_PENDING_APPROVAL_event.json';

        $queueMessage = $this->createPaymentEventQueueMessage($exampleFileToLoad, $expectedPaymentId, $orderReference);

        $this->tester->cleanupInMemoryQueue();

        $this->tester->getQueueClient()->sendMessage(WorldlineWebhookConstants::WORLDLINE_WEBHOOK_EVENT_QUEUE_NAME, $queueMessage);

        $queueReceiveMessageTransfer = $this->tester->getQueueClient()->receiveMessages(WorldlineWebhookConstants::WORLDLINE_WEBHOOK_EVENT_QUEUE_NAME)[0];
        // Act
        $sut->processEventMessage($queueReceiveMessageTransfer);

        // Assert
        $restReceiveLogEntity = VsyWorldlineRestReceiveLogQuery::create()->findOneByEventId('97a4fcdf-9a08-4a59-a0c0-69bfd7e1d579');
        self::assertNotNull($restReceiveLogEntity);
        $restLogEntity = VsyWorldlineRestLogQuery::create()->findOneByEventId('97a4fcdf-9a08-4a59-a0c0-69bfd7e1d579');
        self::assertSame('6511', $restLogEntity->getMerchantId());

        /** @var \Orm\Zed\Worldline\Persistence\VsyPaymentWorldlineTransactionStatusLog $transactionLogEntity */
        $transactionLogEntity = $restLogEntity->getVsyPaymentWorldlineTransactionStatusLogs()->getFirst();

        self::assertSame(WorldlineConstants::STATUS_PAYMENT_PENDING_APPROVAL, $transactionLogEntity->getStatus());
        self::assertSame(WorldlineConstants::STATUS_CATEGORY_PENDING_MERCHANT, $transactionLogEntity->getStatusCategory());
        self::assertSame('20170901150000', $transactionLogEntity->getStatusCodeChangeDateTime());
        self::assertSame(0, $transactionLogEntity->getStatusCode());
    }

    /**
     * @return void
     */
    public function testProcessEventMessageProcessesAPaymentEventWithStatusRejectedCorrectlyWhenTheReferencedMerchantReferenceIsKnown(): void
    {
        // Arrange
        $customerWithOrders = $this->tester->createCustomerTransfer(static::TEST_USERNAME, static::TEST_PASSWORD);
        $quote = $this->tester->createQuoteTransfer($customerWithOrders, [$this->tester->createProductTransfer()], $this->tester->createPaymentTransfer()->toArray());
        $saveOrderTransfer = $this->tester->createOrderTransfer($quote);

        // for timestamp conversion of timestamps in worldline time zone
        $this->tester->setConfig(WorldlineConstants::WORLDLINE_TIMEZONE, 'Europe/Paris');

        $orderReference = $saveOrderTransfer->getOrderReference();
        $pathToResponses = __DIR__ . '/../_data/get_hosted_checkout_status_response_PAYMENT_CREATED.json';
        $body = file_get_contents($pathToResponses);

        $bodyArray = json_decode($body, true);

        $bodyArray['merchantReference'] = $orderReference;

        $body = json_encode($bodyArray, JSON_PRETTY_PRINT);

        $apiLogger = $this->getMockBuilder(WorldlineApiCallLoggerInterface::class)->getMock();

        $wlConnection = new TestingConnection(
            $apiLogger,
            $this->getMockConnectionResponse(200, ['Content-Type' => 'application/json'], $body),
        );
        $businessFactoryMock = $this->createBusinessFactoryMock($wlConnection);
        $sut = $this->createFacadeMock($wlConnection, $businessFactoryMock);

        $expectedHostedCheckoutId = self::HOSTED_CHECKOUT_ID;

        $expectedPaymentId = 'expected_payment_id';
        $initialTransactionStatus = WorldlineConstants::STATUS_HOSTED_CHECKOUT_STATUS_PAYMENT_CREATED;
        $initialTransactionStatusCategory = null;

        $this->haveHostedCheckoutWithPaymentId(
            $saveOrderTransfer,
            $quote,
            $expectedPaymentId,
            $expectedHostedCheckoutId,
            $initialTransactionStatus,
            $initialTransactionStatusCategory,
        );

        $exampleFileToLoad = '/../_data/payment_rejected_event.json';

        $queueMessage = $this->createPaymentEventQueueMessage($exampleFileToLoad, $expectedPaymentId, $orderReference);

        $this->tester->cleanupInMemoryQueue();

        $this->tester->getQueueClient()->sendMessage(WorldlineWebhookConstants::WORLDLINE_WEBHOOK_EVENT_QUEUE_NAME, $queueMessage);

        $queueReceiveMessageTransfer = $this->tester->getQueueClient()->receiveMessages(WorldlineWebhookConstants::WORLDLINE_WEBHOOK_EVENT_QUEUE_NAME)[0];
        // Act
        $sut->processEventMessage($queueReceiveMessageTransfer);

        // Assert
        $restReceiveLogEntity = VsyWorldlineRestReceiveLogQuery::create()->findOneByEventId('97a4fcdf-9a08-4a59-a0c0-69bfd7e1d560');
        self::assertNotNull($restReceiveLogEntity);
        $restLogEntity = VsyWorldlineRestLogQuery::create()->findOneByEventId('97a4fcdf-9a08-4a59-a0c0-69bfd7e1d560');
        self::assertSame('6511', $restLogEntity->getMerchantId());

        /** @var \Orm\Zed\Worldline\Persistence\VsyPaymentWorldlineTransactionStatusLog $transactionLogEntity */
        $transactionLogEntity = $restLogEntity->getVsyPaymentWorldlineTransactionStatusLogs()->getFirst();

        self::assertSame(WorldlineConstants::STATUS_REJECTED, $transactionLogEntity->getStatus());
        self::assertSame(WorldlineConstants::STATUS_CATEGORY_UNSUCCESSFUL, $transactionLogEntity->getStatusCategory());
        self::assertSame('20170901150000', $transactionLogEntity->getStatusCodeChangeDateTime()); // converted Timezone
        self::assertSame(0, $transactionLogEntity->getStatusCode());
    }

    /**
     * @return void
     */
    public function testProcessEventMessageProcessesAPaymentEventWithStatusRejectedCaptureCorrectlyWhenTheReferencedMerchantReferenceIsKnown(): void
    {
        // Arrange
        $customerWithOrders = $this->tester->createCustomerTransfer(static::TEST_USERNAME, static::TEST_PASSWORD);
        $quote = $this->tester->createQuoteTransfer($customerWithOrders, [$this->tester->createProductTransfer()], $this->tester->createPaymentTransfer()->toArray());
        $saveOrderTransfer = $this->tester->createOrderTransfer($quote);

        $orderReference = $saveOrderTransfer->getOrderReference();
        $pathToResponses = __DIR__ . '/../_data/get_hosted_checkout_status_response_PAYMENT_CREATED.json';
        $body = file_get_contents($pathToResponses);

        $bodyArray = json_decode($body, true);

        $bodyArray['merchantReference'] = $orderReference;

        $body = json_encode($bodyArray, JSON_PRETTY_PRINT);

        $apiLogger = $this->getMockBuilder(WorldlineApiCallLoggerInterface::class)->getMock();

        $wlConnection = new TestingConnection(
            $apiLogger,
            $this->getMockConnectionResponse(200, ['Content-Type' => 'application/json'], $body),
        );
        $businessFactoryMock = $this->createBusinessFactoryMock($wlConnection);
        $sut = $this->createFacadeMock($wlConnection, $businessFactoryMock);

        $expectedHostedCheckoutId = self::HOSTED_CHECKOUT_ID;

        $expectedPaymentId = 'expected_payment_id';
        $initialTransactionStatus = WorldlineConstants::STATUS_HOSTED_CHECKOUT_STATUS_PAYMENT_CREATED;
        $initialTransactionStatusCategory = null;

        $this->haveHostedCheckoutWithPaymentId(
            $saveOrderTransfer,
            $quote,
            $expectedPaymentId,
            $expectedHostedCheckoutId,
            $initialTransactionStatus,
            $initialTransactionStatusCategory,
        );

        $exampleFileToLoad = '/../_data/payment_rejected_capture_event.json';

        $queueMessage = $this->createPaymentEventQueueMessage($exampleFileToLoad, $expectedPaymentId, $orderReference);

        $this->tester->cleanupInMemoryQueue();

        $this->tester->getQueueClient()->sendMessage(WorldlineWebhookConstants::WORLDLINE_WEBHOOK_EVENT_QUEUE_NAME, $queueMessage);

        $queueReceiveMessageTransfer = $this->tester->getQueueClient()->receiveMessages(WorldlineWebhookConstants::WORLDLINE_WEBHOOK_EVENT_QUEUE_NAME)[0];
        // Act
        $sut->processEventMessage($queueReceiveMessageTransfer);

        // Assert
        $restReceiveLogEntity = VsyWorldlineRestReceiveLogQuery::create()->findOneByEventId('97a4fcdf-9a08-4a59-a0c0-69bfd7e1d560');
        self::assertNotNull($restReceiveLogEntity);
        $restLogEntity = VsyWorldlineRestLogQuery::create()->findOneByEventId('97a4fcdf-9a08-4a59-a0c0-69bfd7e1d560');
        self::assertSame('6511', $restLogEntity->getMerchantId());

        /** @var \Orm\Zed\Worldline\Persistence\VsyPaymentWorldlineTransactionStatusLog $transactionLogEntity */
        $transactionLogEntity = $restLogEntity->getVsyPaymentWorldlineTransactionStatusLogs()->getFirst();

        self::assertSame(WorldlineConstants::STATUS_REJECTED_CAPTURE, $transactionLogEntity->getStatus());
        self::assertSame(WorldlineConstants::STATUS_CATEGORY_UNSUCCESSFUL, $transactionLogEntity->getStatusCategory());
        self::assertSame('20170901150000', $transactionLogEntity->getStatusCodeChangeDateTime());
        self::assertSame(0, $transactionLogEntity->getStatusCode());
    }

    /**
     * @return void
     */
    public function testProcessEventProcessesAPaymentEventCorrectlyWhenTheReferencedMerchantReferenceIsUnKnownDoesNotSaveEventData(): void
    {
        // Arrange
        $customerWithOrders = $this->tester->createCustomerTransfer(static::TEST_USERNAME, static::TEST_PASSWORD);
        $quote = $this->tester->createQuoteTransfer($customerWithOrders, [$this->tester->createProductTransfer()], $this->tester->createPaymentTransfer()->toArray());
        $saveOrderTransfer = $this->tester->createOrderTransfer($quote);

        $orderReference = $saveOrderTransfer->getOrderReference();
        $pathToResponses = __DIR__ . '/../_data/get_hosted_checkout_status_response_PAYMENT_CREATED.json';
        $body = file_get_contents($pathToResponses);

        $bodyArray = json_decode($body, true);

        $bodyArray['merchantReference'] = $orderReference;

        $body = json_encode($bodyArray, JSON_PRETTY_PRINT);

        $apiLogger = $this->getMockBuilder(WorldlineApiCallLoggerInterface::class)->getMock();

        $wlConnection = new TestingConnection(
            $apiLogger,
            $this->getMockConnectionResponse(200, ['Content-Type' => 'application/json'], $body),
        );
        $businessFactoryMock = $this->createBusinessFactoryMock($wlConnection);
        $sut = $this->createFacadeMock($wlConnection, $businessFactoryMock);

        $expectedHostedCheckoutId = self::HOSTED_CHECKOUT_ID;

        $expectedPaymentId = 'expected_payment_id';
        $initialTransactionStatus = WorldlineConstants::STATUS_HOSTED_CHECKOUT_STATUS_PAYMENT_CREATED;
        $initialTransactionStatusCategory = null;

        $this->haveHostedCheckoutWithPaymentId($saveOrderTransfer, $quote, $expectedPaymentId, $expectedHostedCheckoutId, $initialTransactionStatus, $initialTransactionStatusCategory);

        $unexpectedPaymentId = 'unexpected_payment_id';
        $exampleFileToLoad = '/../_data/payment_created_event.json';

        $unexpectedOrderReference = 'unexpected_merchant_reference';

        $queueMessage = $this->createPaymentEventQueueMessage($exampleFileToLoad, $unexpectedPaymentId, $unexpectedOrderReference);

        $this->tester->cleanupInMemoryQueue();

        $this->tester->getQueueClient()->sendMessage(WorldlineWebhookConstants::WORLDLINE_WEBHOOK_EVENT_QUEUE_NAME, $queueMessage);

        $queueReceiveMessageTransfer = $this->tester->getQueueClient()->receiveMessages(WorldlineWebhookConstants::WORLDLINE_WEBHOOK_EVENT_QUEUE_NAME)[0];
        // Act
        $sut->processEventMessage($queueReceiveMessageTransfer);

        // Assert
        $restReceiveLogEntity = VsyWorldlineRestReceiveLogQuery::create()->findOneByEventId('eventId');
        self::assertNull($restReceiveLogEntity);
    }

    /**
     * @return void
     */
    public function testProcessEventMessageProcessesAPaymentEventWithStatusPendingCaptureCorrectlyWhenTheReferencedMerchantReferenceIsKnown(): void
    {
        // Arrange
        $customerWithOrders = $this->tester->createCustomerTransfer(static::TEST_USERNAME, static::TEST_PASSWORD);
        $quote = $this->tester->createQuoteTransfer($customerWithOrders, [$this->tester->createProductTransfer()], $this->tester->createPaymentTransfer()->toArray());
        $saveOrderTransfer = $this->tester->createOrderTransfer($quote);

        $orderReference = $saveOrderTransfer->getOrderReference();
        $pathToResponses = __DIR__ . '/../_data/get_hosted_checkout_status_response_PAYMENT_CREATED.json';
        $body = file_get_contents($pathToResponses);

        $bodyArray = json_decode($body, true);

        $bodyArray['merchantReference'] = $orderReference;

        $body = json_encode($bodyArray, JSON_PRETTY_PRINT);

        $apiLogger = $this->getMockBuilder(WorldlineApiCallLoggerInterface::class)->getMock();

        $wlConnection = new TestingConnection(
            $apiLogger,
            $this->getMockConnectionResponse(200, ['Content-Type' => 'application/json'], $body),
        );
        $businessFactoryMock = $this->createBusinessFactoryMock($wlConnection);
        $sut = $this->createFacadeMock($wlConnection, $businessFactoryMock);

        $expectedHostedCheckoutId = self::HOSTED_CHECKOUT_ID;

        $expectedPaymentId = 'expected_payment_id';
        $initialTransactionStatus = WorldlineConstants::STATUS_PAYMENT_PENDING_APPROVAL;
        $initialTransactionStatusCategory = WorldlineConstants::STATUS_CATEGORY_PENDING_MERCHANT;

        $this->haveHostedCheckoutWithPaymentId(
            $saveOrderTransfer,
            $quote,
            $expectedPaymentId,
            $expectedHostedCheckoutId,
            $initialTransactionStatus,
            $initialTransactionStatusCategory,
        );

        $exampleFileToLoad = '/../_data/payment_pending_capture_event.json';

        $queueMessage = $this->createPaymentEventQueueMessage($exampleFileToLoad, $expectedPaymentId, $orderReference);

        $this->tester->cleanupInMemoryQueue();

        $this->tester->getQueueClient()->sendMessage(WorldlineWebhookConstants::WORLDLINE_WEBHOOK_EVENT_QUEUE_NAME, $queueMessage);

        $queueReceiveMessageTransfer = $this->tester->getQueueClient()->receiveMessages(WorldlineWebhookConstants::WORLDLINE_WEBHOOK_EVENT_QUEUE_NAME)[0];
        // Act
        $sut->processEventMessage($queueReceiveMessageTransfer);

        // Assert
        $restReceiveLogEntity = VsyWorldlineRestReceiveLogQuery::create()->findOneByEventId('97a4fcdf-9a08-4a59-a0c0-69bfd7e1d666');
        self::assertNotNull($restReceiveLogEntity);
        $restLogEntity = VsyWorldlineRestLogQuery::create()->findOneByEventId('97a4fcdf-9a08-4a59-a0c0-69bfd7e1d666');
        self::assertSame('6511', $restLogEntity->getMerchantId());

        /** @var \Orm\Zed\Worldline\Persistence\VsyPaymentWorldlineTransactionStatusLog $transactionLogEntity */
        $transactionLogEntity = $restLogEntity->getVsyPaymentWorldlineTransactionStatusLogs()->getFirst();

        self::assertSame(WorldlineConstants::STATUS_PENDING_CAPTURE, $transactionLogEntity->getStatus());
        self::assertSame(WorldlineConstants::STATUS_CATEGORY_PENDING_MERCHANT, $transactionLogEntity->getStatusCategory());
        self::assertSame('20220901150000', $transactionLogEntity->getStatusCodeChangeDateTime());
        self::assertSame(600, $transactionLogEntity->getStatusCode());
    }

    /**
     * @return void
     */
    public function testProcessEventMessageProcessesAPaymentEventWithStatusCaptureRequestedCorrectlyWhenTheReferencedMerchantReferenceIsKnown(): void
    {
        // Arrange
        $customerWithOrders = $this->tester->createCustomerTransfer(static::TEST_USERNAME, static::TEST_PASSWORD);
        $quote = $this->tester->createQuoteTransfer($customerWithOrders, [$this->tester->createProductTransfer()], $this->tester->createPaymentTransfer()->toArray());
        $saveOrderTransfer = $this->tester->createOrderTransfer($quote);

        $orderReference = $saveOrderTransfer->getOrderReference();
        $pathToResponses = __DIR__ . '/../_data/get_hosted_checkout_status_response_PAYMENT_CREATED.json';
        $body = file_get_contents($pathToResponses);

        $bodyArray = json_decode($body, true);

        $bodyArray['merchantReference'] = $orderReference;

        $body = json_encode($bodyArray, JSON_PRETTY_PRINT);

        $apiLogger = $this->getMockBuilder(WorldlineApiCallLoggerInterface::class)->getMock();

        $wlConnection = new TestingConnection(
            $apiLogger,
            $this->getMockConnectionResponse(200, ['Content-Type' => 'application/json'], $body),
        );
        $businessFactoryMock = $this->createBusinessFactoryMock($wlConnection);
        $sut = $this->createFacadeMock($wlConnection, $businessFactoryMock);

        $expectedHostedCheckoutId = self::HOSTED_CHECKOUT_ID;

        $expectedPaymentId = 'expected_payment_id';

        $initialTransactionStatus = WorldlineConstants::STATUS_PENDING_CAPTURE;

        $initialTRansactionStatusCategory = WorldlineConstants::STATUS_CATEGORY_PENDING_MERCHANT;

        $this->haveHostedCheckoutWithPaymentId(
            $saveOrderTransfer,
            $quote,
            $expectedPaymentId,
            $expectedHostedCheckoutId,
            $initialTransactionStatus,
            $initialTRansactionStatusCategory,
        );

        $exampleFileToLoad = '/../_data/payment_capture_requested_event.json';

        $queueMessage = $this->createPaymentEventQueueMessage($exampleFileToLoad, $expectedPaymentId, $orderReference);

        $this->tester->cleanupInMemoryQueue();

        $this->tester->getQueueClient()->sendMessage(WorldlineWebhookConstants::WORLDLINE_WEBHOOK_EVENT_QUEUE_NAME, $queueMessage);

        $queueReceiveMessageTransfer = $this->tester->getQueueClient()->receiveMessages(WorldlineWebhookConstants::WORLDLINE_WEBHOOK_EVENT_QUEUE_NAME)[0];
        // Act
        $sut->processEventMessage($queueReceiveMessageTransfer);

        // Assert
        $restReceiveLogEntity = VsyWorldlineRestReceiveLogQuery::create()->findOneByEventId('97a4fcdf-9a08-4a59-a0c0-69bfd7e1d777');
        self::assertNotNull($restReceiveLogEntity);
        $restLogEntity = VsyWorldlineRestLogQuery::create()->findOneByEventId('97a4fcdf-9a08-4a59-a0c0-69bfd7e1d777');
        self::assertSame('6511', $restLogEntity->getMerchantId());

        /** @var \Orm\Zed\Worldline\Persistence\VsyPaymentWorldlineTransactionStatusLog $transactionLogEntity */
        $transactionLogEntity = $restLogEntity->getVsyPaymentWorldlineTransactionStatusLogs()->getFirst();

        self::assertSame(WorldlineConstants::STATUS_CAPTURE_REQUESTED, $transactionLogEntity->getStatus());
        self::assertSame(WorldlineConstants::STATUS_CATEGORY_PENDING_CONNECT_OR_3RD_PARTY, $transactionLogEntity->getStatusCategory());
        self::assertSame('20220901150000', $transactionLogEntity->getStatusCodeChangeDateTime());
        self::assertSame(700, $transactionLogEntity->getStatusCode());
    }

    /**
     * @return void
     */
    public function testProcessEventMessageProcessesAPaymentEventWithStatusCapturedCorrectlyWhenTheReferencedMerchantReferenceIsKnown(): void
    {
        // Arrange
        $customerWithOrders = $this->tester->createCustomerTransfer(static::TEST_USERNAME, static::TEST_PASSWORD);
        $quote = $this->tester->createQuoteTransfer($customerWithOrders, [$this->tester->createProductTransfer()], $this->tester->createPaymentTransfer()->toArray());
        $saveOrderTransfer = $this->tester->createOrderTransfer($quote);

        $orderReference = $saveOrderTransfer->getOrderReference();
        $pathToResponses = __DIR__ . '/../_data/get_hosted_checkout_status_response_PAYMENT_CREATED.json';
        $body = file_get_contents($pathToResponses);

        $bodyArray = json_decode($body, true);

        $bodyArray['merchantReference'] = $orderReference;

        $body = json_encode($bodyArray, JSON_PRETTY_PRINT);

        $apiLogger = $this->getMockBuilder(WorldlineApiCallLoggerInterface::class)->getMock();

        $wlConnection = new TestingConnection(
            $apiLogger,
            $this->getMockConnectionResponse(200, ['Content-Type' => 'application/json'], $body),
        );
        $businessFactoryMock = $this->createBusinessFactoryMock($wlConnection);
        $sut = $this->createFacadeMock($wlConnection, $businessFactoryMock);

        $expectedHostedCheckoutId = self::HOSTED_CHECKOUT_ID;

        $initialTransactionStatus = WorldlineConstants::STATUS_CAPTURE_REQUESTED;
        $initialTransactionStatusCategory = WorldlineConstants::STATUS_CATEGORY_PENDING_CONNECT_OR_3RD_PARTY;
        $expectedPaymentId = 'expected_payment_id';

        $this->haveHostedCheckoutWithPaymentId(
            $saveOrderTransfer,
            $quote,
            $expectedPaymentId,
            $expectedHostedCheckoutId,
            $initialTransactionStatus,
            $initialTransactionStatusCategory,
        );

        $exampleFileToLoad = '/../_data/payment_captured_event.json';

        $queueMessage = $this->createPaymentEventQueueMessage($exampleFileToLoad, $expectedPaymentId, $orderReference);

        $this->tester->cleanupInMemoryQueue();

        $this->tester->getQueueClient()->sendMessage(WorldlineWebhookConstants::WORLDLINE_WEBHOOK_EVENT_QUEUE_NAME, $queueMessage);

        $queueReceiveMessageTransfer = $this->tester->getQueueClient()->receiveMessages(WorldlineWebhookConstants::WORLDLINE_WEBHOOK_EVENT_QUEUE_NAME)[0];
        // Act
        $sut->processEventMessage($queueReceiveMessageTransfer);

        // Assert
        $restReceiveLogEntity = VsyWorldlineRestReceiveLogQuery::create()->findOneByEventId('97a4fcdf-9a08-4a59-a0c0-69bfd7e1d888');
        self::assertNotNull($restReceiveLogEntity);
        $restLogEntity = VsyWorldlineRestLogQuery::create()->findOneByEventId('97a4fcdf-9a08-4a59-a0c0-69bfd7e1d888');
        self::assertSame('6511', $restLogEntity->getMerchantId());

        /** @var \Orm\Zed\Worldline\Persistence\VsyPaymentWorldlineTransactionStatusLog $transactionLogEntity */
        $transactionLogEntity = $restLogEntity->getVsyPaymentWorldlineTransactionStatusLogs()->getFirst();

        self::assertSame(WorldlineConstants::STATUS_CAPTURED, $transactionLogEntity->getStatus());
        self::assertSame(WorldlineConstants::STATUS_CATEGORY_COMPLETED, $transactionLogEntity->getStatusCategory());
        self::assertSame('20220901150000', $transactionLogEntity->getStatusCodeChangeDateTime());
        self::assertSame(600, $transactionLogEntity->getStatusCode());
    }

    /**
     * @return void
     */
    public function testProcessEventMessageProcessesAPaymentEventWithStatusPaidCorrectlyWhenTheReferencedMerchantReferenceIsKnown(): void
    {
        // Arrange
        $customerWithOrders = $this->tester->createCustomerTransfer(static::TEST_USERNAME, static::TEST_PASSWORD);
        $quote = $this->tester->createQuoteTransfer($customerWithOrders, [$this->tester->createProductTransfer()], $this->tester->createPaymentTransfer()->toArray());
        $saveOrderTransfer = $this->tester->createOrderTransfer($quote);

        $orderReference = $saveOrderTransfer->getOrderReference();
        $pathToResponses = __DIR__ . '/../_data/get_hosted_checkout_status_response_PAYMENT_CREATED.json';
        $body = file_get_contents($pathToResponses);

        $bodyArray = json_decode($body, true);

        $bodyArray['merchantReference'] = $orderReference;

        $body = json_encode($bodyArray, JSON_PRETTY_PRINT);

        $apiLogger = $this->getMockBuilder(WorldlineApiCallLoggerInterface::class)->getMock();

        $wlConnection = new TestingConnection(
            $apiLogger,
            $this->getMockConnectionResponse(200, ['Content-Type' => 'application/json'], $body),
        );
        $businessFactoryMock = $this->createBusinessFactoryMock($wlConnection);
        $sut = $this->createFacadeMock($wlConnection, $businessFactoryMock);

        $expectedHostedCheckoutId = self::HOSTED_CHECKOUT_ID;

        $initialTransactionStatus = WorldlineConstants::STATUS_CAPTURED;

        $initialTransactionStatusCategory = WorldlineConstants::STATUS_CATEGORY_COMPLETED;
        $expectedPaymentid = 'expected_payment_id';

        $this->haveHostedCheckoutWithPaymentId(
            $saveOrderTransfer,
            $quote,
            $expectedPaymentid,
            $expectedHostedCheckoutId,
            $initialTransactionStatus,
            $initialTransactionStatusCategory,
        );

        $exampleFileToLoad = '/../_data/payment_paid_event.json';

        $queueMessage = $this->createPaymentEventQueueMessage($exampleFileToLoad, $expectedPaymentid, $orderReference);

        $this->tester->cleanupInMemoryQueue();

        $this->tester->getQueueClient()->sendMessage(WorldlineWebhookConstants::WORLDLINE_WEBHOOK_EVENT_QUEUE_NAME, $queueMessage);

        $queueReceiveMessageTransfer = $this->tester->getQueueClient()->receiveMessages(WorldlineWebhookConstants::WORLDLINE_WEBHOOK_EVENT_QUEUE_NAME)[0];
        // Act
        $sut->processEventMessage($queueReceiveMessageTransfer);

        // Assert
        $restReceiveLogEntity = VsyWorldlineRestReceiveLogQuery::create()->findOneByEventId('97a4fcdf-9a08-4a59-a0c0-69bfd7e1d999');
        self::assertNotNull($restReceiveLogEntity);
        $restLogEntity = VsyWorldlineRestLogQuery::create()->findOneByEventId('97a4fcdf-9a08-4a59-a0c0-69bfd7e1d999');
        self::assertSame('6511', $restLogEntity->getMerchantId());

        /** @var \Orm\Zed\Worldline\Persistence\VsyPaymentWorldlineTransactionStatusLog $transactionLogEntity */
        $transactionLogEntity = $restLogEntity->getVsyPaymentWorldlineTransactionStatusLogs()->getFirst();

        self::assertSame(WorldlineConstants::STATUS_PAID, $transactionLogEntity->getStatus());
        self::assertSame(WorldlineConstants::STATUS_CATEGORY_COMPLETED, $transactionLogEntity->getStatusCategory());
        self::assertSame('20220901150000', $transactionLogEntity->getStatusCodeChangeDateTime());
        self::assertSame(600, $transactionLogEntity->getStatusCode());
    }

    /**
     * @return void
     */
    public function testProcessEventMessageProcessesATokenUpdatedEventCorrectlyWhenTheTokenIsKnown(): void
    {
        // Arrange
        $customerWithOrders = $this->tester->createCustomerTransfer(static::TEST_USERNAME, static::TEST_PASSWORD);
        $quote = $this->tester->createQuoteTransfer($customerWithOrders, [$this->tester->createProductTransfer()], $this->tester->createPaymentTransfer()->toArray());
        $saveOrderTransfer = $this->tester->createOrderTransfer($quote);

        $orderReference = $saveOrderTransfer->getOrderReference();
        $pathToResponses = __DIR__ . '/../_data/get_hosted_checkout_status_response_PAYMENT_CREATED.json';
        $body = file_get_contents($pathToResponses);

        $bodyArray = json_decode($body, true);

        $bodyArray['merchantReference'] = $orderReference;

        $body = json_encode($bodyArray, JSON_PRETTY_PRINT);

        $apiLogger = $this->getMockBuilder(WorldlineApiCallLoggerInterface::class)->getMock();

        $wlConnection = new TestingConnection(
            $apiLogger,
            $this->getMockConnectionResponse(200, ['Content-Type' => 'application/json'], $body),
        );
        $businessFactoryMock = $this->createBusinessFactoryMock($wlConnection);
        $sut = $this->createFacadeMock($wlConnection, $businessFactoryMock);

        $expectedHostedCheckoutId = self::HOSTED_CHECKOUT_ID;

        $initialTransactionStatus = WorldlineConstants::STATUS_CAPTURED;

        $initialTransactionStatusCategory = WorldlineConstants::STATUS_CATEGORY_COMPLETED;
        $expectedPaymentid = 'expected_payment_id';

        $tokenTransfer = $this->tester->havePaymentTokenWithThreeDSecureDataForCustomer($customerWithOrders);

        $this->haveHostedCheckoutWithPaymentId(
            $saveOrderTransfer,
            $quote,
            $expectedPaymentid,
            $expectedHostedCheckoutId,
            $initialTransactionStatus,
            $initialTransactionStatusCategory,
        );

        $exampleFileToLoad = '/../_data/token_updated_event.json';
        $expectedTokenId = $tokenTransfer->getToken();

        $queueMessage = $this->createTokenEventQueueMessage($exampleFileToLoad, $expectedTokenId);

        $this->tester->cleanupInMemoryQueue();

        $this->tester->getQueueClient()->sendMessage(WorldlineWebhookConstants::WORLDLINE_WEBHOOK_EVENT_QUEUE_NAME, $queueMessage);

        $queueReceiveMessageTransfer = $this->tester->getQueueClient()->receiveMessages(WorldlineWebhookConstants::WORLDLINE_WEBHOOK_EVENT_QUEUE_NAME)[0];
        // Act
        $sut->processEventMessage($queueReceiveMessageTransfer);

        // Assert
        $restReceiveLogEntity = VsyWorldlineRestReceiveLogQuery::create()->findOneByEventId('97a4fcdf-9a08-4a59-a0c0-69bfd7e1d111');
        self::assertNotNull($restReceiveLogEntity);
        $restLogEntity = VsyWorldlineRestLogQuery::create()->findOneByEventId('97a4fcdf-9a08-4a59-a0c0-69bfd7e1d111');
        self::assertSame('6511', $restLogEntity->getMerchantId());

        /** @var \Orm\Zed\Worldline\Persistence\VsyWorldlineToken $tokenEntity */
        $tokenEntity = VsyWorldlineTokenQuery::create()->findByToken($expectedTokenId)->getFirst();

        self::assertSame('0929', $tokenEntity->getExpiryMonth());
        self::assertSame($tokenTransfer->getFkInitialThreeDSecureResult(), $tokenEntity->getFkInitialThreeDSecureResult());
        self::assertSame($tokenTransfer->getInitialSchemeTransactionId(), $tokenEntity->getInitialSchemeTransactionId());
        self::assertSame($tokenTransfer->getFkCustomer(), $tokenEntity->getFkCustomer());
        self::assertSame('********9999', $tokenEntity->getObfuscatedCardNumber());
        self::assertSame('Max Mustermann', $tokenEntity->getHolderName());
    }

    /**
     * @return void
     */
    public function testProcessEventMessageProcessesATokenExpiredEventCorrectlyWhenTheTokenIsKnown(): void
    {
        // Arrange
        $customerWithOrders = $this->tester->createCustomerTransfer(static::TEST_USERNAME, static::TEST_PASSWORD);
        $quote = $this->tester->createQuoteTransfer($customerWithOrders, [$this->tester->createProductTransfer()], $this->tester->createPaymentTransfer()->toArray());
        $saveOrderTransfer = $this->tester->createOrderTransfer($quote);

        $orderReference = $saveOrderTransfer->getOrderReference();
        $pathToResponses = __DIR__ . '/../_data/get_hosted_checkout_status_response_PAYMENT_CREATED.json';
        $body = file_get_contents($pathToResponses);

        $bodyArray = json_decode($body, true);

        $bodyArray['merchantReference'] = $orderReference;

        $body = json_encode($bodyArray, JSON_PRETTY_PRINT);

        $apiLogger = $this->getMockBuilder(WorldlineApiCallLoggerInterface::class)->getMock();

        $wlConnection = new TestingConnection(
            $apiLogger,
            $this->getMockConnectionResponse(200, ['Content-Type' => 'application/json'], $body),
        );
        $businessFactoryMock = $this->createBusinessFactoryMock($wlConnection);
        $sut = $this->createFacadeMock($wlConnection, $businessFactoryMock);

        $expectedHostedCheckoutId = self::HOSTED_CHECKOUT_ID;

        $initialTransactionStatus = WorldlineConstants::STATUS_CAPTURED;

        $initialTransactionStatusCategory = WorldlineConstants::STATUS_CATEGORY_COMPLETED;
        $expectedPaymentid = 'expected_payment_id';

        $tokenTransfer = $this->tester->havePaymentTokenWithThreeDSecureDataForCustomer($customerWithOrders);

        $this->haveHostedCheckoutWithPaymentId(
            $saveOrderTransfer,
            $quote,
            $expectedPaymentid,
            $expectedHostedCheckoutId,
            $initialTransactionStatus,
            $initialTransactionStatusCategory,
        );

        $exampleFileToLoad = '/../_data/token_expired_event.json';
        $expectedTokenId = $tokenTransfer->getToken();

        $queueMessage = $this->createTokenEventQueueMessage($exampleFileToLoad, $expectedTokenId);

        $this->tester->cleanupInMemoryQueue();

        $this->tester->getQueueClient()->sendMessage(WorldlineWebhookConstants::WORLDLINE_WEBHOOK_EVENT_QUEUE_NAME, $queueMessage);

        $queueReceiveMessageTransfer = $this->tester->getQueueClient()->receiveMessages(WorldlineWebhookConstants::WORLDLINE_WEBHOOK_EVENT_QUEUE_NAME)[0];
        // Act
        $sut->processEventMessage($queueReceiveMessageTransfer);

        // Assert
        $restReceiveLogEntity = VsyWorldlineRestReceiveLogQuery::create()->findOneByEventId('97a4fcdf-9a08-4a59-a0c0-69bfd7e1d222');
        self::assertNotNull($restReceiveLogEntity);
        $restLogEntity = VsyWorldlineRestLogQuery::create()->findOneByEventId('97a4fcdf-9a08-4a59-a0c0-69bfd7e1d222');
        self::assertSame('6511', $restLogEntity->getMerchantId());

        /** @var \Orm\Zed\Worldline\Persistence\VsyWorldlineToken $tokenEntity */
        $tokenEntity = VsyWorldlineTokenQuery::create()->findByToken($expectedTokenId)->getFirst();

        self::assertSame('0922', $tokenEntity->getExpiryMonth());
        self::assertNotNull($tokenEntity->getExpiredAt());
        self::assertSame($tokenTransfer->getFkInitialThreeDSecureResult(), $tokenEntity->getFkInitialThreeDSecureResult());
        self::assertSame($tokenTransfer->getInitialSchemeTransactionId(), $tokenEntity->getInitialSchemeTransactionId());
        self::assertSame($tokenTransfer->getFkCustomer(), $tokenEntity->getFkCustomer());
        self::assertSame('********9999', $tokenEntity->getObfuscatedCardNumber());
        self::assertSame('Max Mustermann', $tokenEntity->getHolderName());
    }

    /**
     * @return void
     */
    public function testProcessEventMessageProcessesATokenDeletedEventCorrectlyWhenTheTokenIsKnown(): void
    {
        // Arrange
        $customerWithOrders = $this->tester->createCustomerTransfer(static::TEST_USERNAME, static::TEST_PASSWORD);
        $quote = $this->tester->createQuoteTransfer($customerWithOrders, [$this->tester->createProductTransfer()], $this->tester->createPaymentTransfer()->toArray());
        $saveOrderTransfer = $this->tester->createOrderTransfer($quote);

        $orderReference = $saveOrderTransfer->getOrderReference();
        $pathToResponses = __DIR__ . '/../_data/get_hosted_checkout_status_response_PAYMENT_CREATED.json';
        $body = file_get_contents($pathToResponses);

        $bodyArray = json_decode($body, true);

        $bodyArray['merchantReference'] = $orderReference;

        $body = json_encode($bodyArray, JSON_PRETTY_PRINT);

        $apiLogger = $this->getMockBuilder(WorldlineApiCallLoggerInterface::class)->getMock();

        $wlConnection = new TestingConnection(
            $apiLogger,
            $this->getMockConnectionResponse(200, ['Content-Type' => 'application/json'], $body),
        );
        $businessFactoryMock = $this->createBusinessFactoryMock($wlConnection);
        $sut = $this->createFacadeMock($wlConnection, $businessFactoryMock);

        $expectedHostedCheckoutId = self::HOSTED_CHECKOUT_ID;

        $initialTransactionStatus = WorldlineConstants::STATUS_CAPTURED;

        $initialTransactionStatusCategory = WorldlineConstants::STATUS_CATEGORY_COMPLETED;
        $expectedPaymentid = 'expected_payment_id';

        $tokenTransfer = $this->tester->havePaymentTokenWithThreeDSecureDataForCustomer($customerWithOrders);

        $this->haveHostedCheckoutWithPaymentId(
            $saveOrderTransfer,
            $quote,
            $expectedPaymentid,
            $expectedHostedCheckoutId,
            $initialTransactionStatus,
            $initialTransactionStatusCategory,
        );

        $exampleFileToLoad = '/../_data/token_deleted_event.json';
        $expectedTokenId = $tokenTransfer->getToken();

        $queueMessage = $this->createTokenEventQueueMessage($exampleFileToLoad, $expectedTokenId);

        $this->tester->cleanupInMemoryQueue();

        $this->tester->getQueueClient()->sendMessage(WorldlineWebhookConstants::WORLDLINE_WEBHOOK_EVENT_QUEUE_NAME, $queueMessage);

        $queueReceiveMessageTransfer = $this->tester->getQueueClient()->receiveMessages(WorldlineWebhookConstants::WORLDLINE_WEBHOOK_EVENT_QUEUE_NAME)[0];
        // Act
        $sut->processEventMessage($queueReceiveMessageTransfer);

        // Assert
        $restReceiveLogEntity = VsyWorldlineRestReceiveLogQuery::create()->findOneByEventId('97a4fcdf-9a08-4a59-a0c0-69bfd7e1d333');
        self::assertNotNull($restReceiveLogEntity);
        $restLogEntity = VsyWorldlineRestLogQuery::create()->findOneByEventId('97a4fcdf-9a08-4a59-a0c0-69bfd7e1d333');
        self::assertSame('6511', $restLogEntity->getMerchantId());

        /** @var \Orm\Zed\Worldline\Persistence\VsyWorldlineToken $tokenEntity */
        $tokenEntity = VsyWorldlineTokenQuery::create()->findByToken($expectedTokenId)->getFirst();

        self::assertSame($tokenTransfer->getExpiryMonth(), $tokenEntity->getExpiryMonth());
        self::assertNotNull($tokenEntity->getDeletedAt());
        self::assertSame($tokenTransfer->getFkInitialThreeDSecureResult(), $tokenEntity->getFkInitialThreeDSecureResult());
        self::assertSame($tokenTransfer->getInitialSchemeTransactionId(), $tokenEntity->getInitialSchemeTransactionId());
        self::assertSame($tokenTransfer->getFkCustomer(), $tokenEntity->getFkCustomer());
        self::assertSame($tokenTransfer->getObfuscatedCardNumber(), $tokenEntity->getObfuscatedCardNumber());
        self::assertSame($tokenTransfer->getHolderName(), $tokenEntity->getHolderName());
    }

    public function testDeleteWorldlineTokensMarkedAsDeletedDeletesTheConfiguredAmountOfTokensWhenMoreThanTheLimitIndicatesAreExpired()
    {
        // Arrange
        $limitOfTokensToDelete = 5;
        $this->tester->setConfig(WorldlineConstants::WORLDLINE_LIMIT_OF_DELETED_TOKENS_TO_REMOVE_AT_A_TIME, $limitOfTokensToDelete);

        $customerWithOrders = $this->tester->createCustomerTransfer(static::TEST_USERNAME, static::TEST_PASSWORD);

        $tokenTransfer1 = $this->tester->havePaymentTokenWithThreeDSecureDataForCustomer($customerWithOrders, [WorldlineCreditCardTokenTransfer::DELETED_AT => (new DateTime())->format('Y-m-d')]);
        $tokenTransfer2 = $this->tester->havePaymentTokenWithThreeDSecureDataForCustomer($customerWithOrders, [WorldlineCreditCardTokenTransfer::DELETED_AT => (new DateTime())->format('Y-m-d')]);
        $tokenTransfer3 = $this->tester->havePaymentTokenWithThreeDSecureDataForCustomer($customerWithOrders, [WorldlineCreditCardTokenTransfer::DELETED_AT => (new DateTime())->format('Y-m-d')]);
        $tokenTransfer4 = $this->tester->havePaymentTokenWithThreeDSecureDataForCustomer($customerWithOrders, [WorldlineCreditCardTokenTransfer::DELETED_AT => (new DateTime())->format('Y-m-d')]);
        $tokenTransfer5 = $this->tester->havePaymentTokenWithThreeDSecureDataForCustomer($customerWithOrders, [WorldlineCreditCardTokenTransfer::DELETED_AT => (new DateTime())->format('Y-m-d')]);
        $tokenTransfer6 = $this->tester->havePaymentTokenWithThreeDSecureDataForCustomer($customerWithOrders, [WorldlineCreditCardTokenTransfer::DELETED_AT => (new DateTime())->format('Y-m-d')]);
        $tokenTransfer7 = $this->tester->havePaymentTokenWithThreeDSecureDataForCustomer($customerWithOrders, [WorldlineCreditCardTokenTransfer::DELETED_AT => (new DateTime())->format('Y-m-d')]);

        $apiLogger = $this->getMockBuilder(WorldlineApiCallLoggerInterface::class)->getMock();

        $wlConnection = new TestingConnection(
            $apiLogger,
            $this->getMockConnectionResponse(200, array('Content-Type' => 'application/json'), ''),
        );
        $businessFactoryMock = $this->createBusinessFactoryMock($wlConnection);
        $sut = $this->createFacadeMock($wlConnection, $businessFactoryMock);


        // Act
        $actualNumberOfDeletedTokens = $sut->deleteWorldlineTokensMarkedAsDeleted();

        self::assertSame($limitOfTokensToDelete, $actualNumberOfDeletedTokens);
    }

    public function testDeleteWorldlineTokensMarkedAsDeletedDeletesOnlyTokensMarkedAsDeleted()
    {
        // Arrange
        $limitOfTokensToDelete = 5;
        VsyWorldlineTokenQuery::create()->filterByDeletedAt(comparison: Criteria::ISNOTNULL)->delete(); // make sure there isn't already a token that should be deleted;


        $this->tester->setConfig(WorldlineConstants::WORLDLINE_LIMIT_OF_DELETED_TOKENS_TO_REMOVE_AT_A_TIME, $limitOfTokensToDelete);

        $customerWithOrders = $this->tester->createCustomerTransfer(static::TEST_USERNAME, static::TEST_PASSWORD);

        $tokenTransfer1 = $this->tester->havePaymentTokenWithThreeDSecureDataForCustomer($customerWithOrders, [WorldlineCreditCardTokenTransfer::DELETED_AT => null]);
        $tokenTransfer2 = $this->tester->havePaymentTokenWithThreeDSecureDataForCustomer($customerWithOrders, [WorldlineCreditCardTokenTransfer::DELETED_AT => null]);
        $tokenTransfer3 = $this->tester->havePaymentTokenWithThreeDSecureDataForCustomer($customerWithOrders, [WorldlineCreditCardTokenTransfer::DELETED_AT => null]);
        $tokenTransfer4 = $this->tester->havePaymentTokenWithThreeDSecureDataForCustomer($customerWithOrders, [WorldlineCreditCardTokenTransfer::DELETED_AT => (new DateTime())->format('Y-m-d')]);
        $tokenTransfer5 = $this->tester->havePaymentTokenWithThreeDSecureDataForCustomer($customerWithOrders, [WorldlineCreditCardTokenTransfer::DELETED_AT => (new DateTime())->format('Y-m-d')]);
        $tokenTransfer6 = $this->tester->havePaymentTokenWithThreeDSecureDataForCustomer($customerWithOrders, [WorldlineCreditCardTokenTransfer::DELETED_AT => (new DateTime())->format('Y-m-d')]);
        $tokenTransfer7 = $this->tester->havePaymentTokenWithThreeDSecureDataForCustomer($customerWithOrders, [WorldlineCreditCardTokenTransfer::DELETED_AT => (new DateTime())->format('Y-m-d')]);

        $apiLogger = $this->getMockBuilder(WorldlineApiCallLoggerInterface::class)->getMock();

        $wlConnection = new TestingConnection(
            $apiLogger,
            $this->getMockConnectionResponse(200, array('Content-Type' => 'application/json'), ''),
        );
        $businessFactoryMock = $this->createBusinessFactoryMock($wlConnection);
        $sut = $this->createFacadeMock($wlConnection, $businessFactoryMock);


        // Act
        $actualNumberOfDeletedTokens = $sut->deleteWorldlineTokensMarkedAsDeleted();

        self::assertSame(4, $actualNumberOfDeletedTokens);

        $tokenEntity = VsyWorldlineTokenQuery::create()->findByToken($tokenTransfer1->getToken())->getFirst();
        self::assertNotNull($tokenEntity);
        $tokenEntity = VsyWorldlineTokenQuery::create()->findByToken($tokenTransfer2->getToken())->getFirst();
        self::assertNotNull($tokenEntity);
        $tokenEntity = VsyWorldlineTokenQuery::create()->findByToken($tokenTransfer3->getToken())->getFirst();
        self::assertNotNull($tokenEntity);
    }

    public function testDeletePaymentTokenByIdSendADeleteTokenCallToWorldlineAndMarksCorrectTokensAsDeleted()
    {
        // Arrange
        $limitOfTokensToDelete = 5;
        VsyWorldlineTokenQuery::create()->filterByDeletedAt(comparison: Criteria::ISNOTNULL)->delete(); // make sure there isn't already a token that should be deleted;

        $this->tester->setConfig(WorldlineConstants::WORLDLINE_LIMIT_OF_DELETED_TOKENS_TO_REMOVE_AT_A_TIME, $limitOfTokensToDelete);

        $customerWithOrders = $this->tester->createCustomerTransfer(static::TEST_USERNAME, static::TEST_PASSWORD);

        $customerWithOrders2 = $this->tester->createCustomerTransfer(static::TEST_USERNAME . '_2', static::TEST_PASSWORD);

        $tokenTransfer1 = $this->tester->havePaymentTokenWithThreeDSecureDataForCustomer($customerWithOrders, [WorldlineCreditCardTokenTransfer::DELETED_AT => null, WorldlineCreditCardTokenTransfer::TOKEN => 'tokenToDelete']);
        $tokenTransfer2 = $this->tester->havePaymentTokenWithThreeDSecureDataForCustomer($customerWithOrders, [WorldlineCreditCardTokenTransfer::DELETED_AT => null]);
        $tokenTransfer3 = $this->tester->havePaymentTokenWithThreeDSecureDataForCustomer($customerWithOrders, [WorldlineCreditCardTokenTransfer::DELETED_AT => null]);
        $tokenTransfer4 = $this->tester->havePaymentTokenWithThreeDSecureDataForCustomer($customerWithOrders, [WorldlineCreditCardTokenTransfer::DELETED_AT => null]);
        $tokenTransfer5 = $this->tester->havePaymentTokenWithThreeDSecureDataForCustomer($customerWithOrders2, [WorldlineCreditCardTokenTransfer::DELETED_AT => null,  WorldlineCreditCardTokenTransfer::TOKEN => 'tokenToDelete']);
        $tokenTransfer6 = $this->tester->havePaymentTokenWithThreeDSecureDataForCustomer($customerWithOrders, [WorldlineCreditCardTokenTransfer::DELETED_AT => null]);
        $tokenTransfer7 = $this->tester->havePaymentTokenWithThreeDSecureDataForCustomer($customerWithOrders, [WorldlineCreditCardTokenTransfer::DELETED_AT => null]);

        $apiLogger = $this->getMockBuilder(WorldlineApiCallLoggerInterface::class)->getMock();

        $wlConnection = new TestingConnection(
            $apiLogger,
            $this->getMockConnectionResponse(204, array('Content-Type' => 'application/json'), null),
        );
        $businessFactoryMock = $this->createBusinessFactoryMock($wlConnection);

        $apiCallEntity = VsyWorldlineApiLogQuery::create()
            ->filterByApiMethod(WorldlineConstants::WORLDLINE_API_METHOD_DELETE_TOKEN)
            ->delete();

        $sut = $this->createFacadeMock($wlConnection, $businessFactoryMock);

        $tokenTransfer = new WorldlineDeleteTokenRequestTransfer();
        $tokenTransfer->setToken('tokenToDelete');
        $tokenTransfer->setCustomerReference($customerWithOrders->getCustomerReference());

        // Act
        $sut->deletePaymentTokenById($tokenTransfer);

        // Assert
        $apiCallEntity = VsyWorldlineApiLogQuery::create()->filterByApiMethod(WorldlineConstants::WORLDLINE_API_METHOD_DELETE_TOKEN)->find()->getFirst();
        self::assertNotNull($apiCallEntity);

        $tokenEntity = VsyWorldlineTokenQuery::create()->findOneByIdToken($tokenTransfer1->getIdToken());
        self::assertNotNull($tokenEntity);
        self::assertNotNull($tokenEntity->getDeletedAt());

        $tokenEntity = VsyWorldlineTokenQuery::create()->findOneByIdToken($tokenTransfer5->getIdToken());
        self::assertNotNull($tokenEntity);
        self::assertNotNull($tokenEntity->getDeletedAt());

        $tokenEntity = VsyWorldlineTokenQuery::create()->findOneByIdToken($tokenTransfer2->getIdToken());
        self::assertNotNull($tokenEntity);
        self::assertNull($tokenEntity->getDeletedAt());

        $tokenEntity = VsyWorldlineTokenQuery::create()->findOneByIdToken($tokenTransfer3->getIdToken());
        self::assertNotNull($tokenEntity);
        self::assertNull($tokenEntity->getDeletedAt());

        $tokenEntity = VsyWorldlineTokenQuery::create()->findOneByIdToken($tokenTransfer4->getIdToken());
        self::assertNotNull($tokenEntity);
        self::assertNull($tokenEntity->getDeletedAt());

        $tokenEntity = VsyWorldlineTokenQuery::create()->findOneByIdToken($tokenTransfer6->getIdToken());
        self::assertNotNull($tokenEntity);
        self::assertNull($tokenEntity->getDeletedAt());

        $tokenEntity = VsyWorldlineTokenQuery::create()->findOneByIdToken($tokenTransfer7->getIdToken());
        self::assertNotNull($tokenEntity);
        self::assertNull($tokenEntity->getDeletedAt());

    }

    public function testDeletePaymentTokenByIdWithCustomerIdAndIdTokenSendsADeleteTokenCallToWorldlineAndMarksCorrectTokensAsDeleted()
    {
        // Arrange
        $limitOfTokensToDelete = 5;
        VsyWorldlineTokenQuery::create()->filterByDeletedAt(comparison: Criteria::ISNOTNULL)->delete(); // make sure there isn't already a token that should be deleted;

        $this->tester->setConfig(WorldlineConstants::WORLDLINE_LIMIT_OF_DELETED_TOKENS_TO_REMOVE_AT_A_TIME, $limitOfTokensToDelete);

        $customerWithOrders = $this->tester->createCustomerTransfer(static::TEST_USERNAME, static::TEST_PASSWORD);

        $customerWithOrders2 = $this->tester->createCustomerTransfer(static::TEST_USERNAME . '_2', static::TEST_PASSWORD);

        $tokenTransfer1 = $this->tester->havePaymentTokenWithThreeDSecureDataForCustomer($customerWithOrders, [WorldlineCreditCardTokenTransfer::DELETED_AT => null, WorldlineCreditCardTokenTransfer::TOKEN => 'tokenToDelete']);
        $tokenTransfer2 = $this->tester->havePaymentTokenWithThreeDSecureDataForCustomer($customerWithOrders, [WorldlineCreditCardTokenTransfer::DELETED_AT => null]);
        $tokenTransfer3 = $this->tester->havePaymentTokenWithThreeDSecureDataForCustomer($customerWithOrders, [WorldlineCreditCardTokenTransfer::DELETED_AT => null]);
        $tokenTransfer4 = $this->tester->havePaymentTokenWithThreeDSecureDataForCustomer($customerWithOrders, [WorldlineCreditCardTokenTransfer::DELETED_AT => null]);
        $tokenTransfer5 = $this->tester->havePaymentTokenWithThreeDSecureDataForCustomer($customerWithOrders2, [WorldlineCreditCardTokenTransfer::DELETED_AT => null,  WorldlineCreditCardTokenTransfer::TOKEN => 'tokenToDelete']);
        $tokenTransfer6 = $this->tester->havePaymentTokenWithThreeDSecureDataForCustomer($customerWithOrders, [WorldlineCreditCardTokenTransfer::DELETED_AT => null]);
        $tokenTransfer7 = $this->tester->havePaymentTokenWithThreeDSecureDataForCustomer($customerWithOrders, [WorldlineCreditCardTokenTransfer::DELETED_AT => null]);

        $apiLogger = $this->getMockBuilder(WorldlineApiCallLoggerInterface::class)->getMock();

        $wlConnection = new TestingConnection(
            $apiLogger,
            $this->getMockConnectionResponse(204, array('Content-Type' => 'application/json'), null),
        );
        $businessFactoryMock = $this->createBusinessFactoryMock($wlConnection);

        $apiCallEntity = VsyWorldlineApiLogQuery::create()
            ->filterByApiMethod(WorldlineConstants::WORLDLINE_API_METHOD_DELETE_TOKEN)
            ->delete();

        $sut = $this->createFacadeMock($wlConnection, $businessFactoryMock);

        $tokenTransfer = new WorldlineDeleteTokenRequestTransfer();
        $tokenTransfer->setIdToken($tokenTransfer1->getIdToken());
        $tokenTransfer->setIdCustomer($customerWithOrders->getIdCustomer());

        // Act
        $sut->deletePaymentTokenById($tokenTransfer);

        // Assert
        $apiCallEntity = VsyWorldlineApiLogQuery::create()->filterByApiMethod(WorldlineConstants::WORLDLINE_API_METHOD_DELETE_TOKEN)->find()->getFirst();
        self::assertNotNull($apiCallEntity);

        $tokenEntity = VsyWorldlineTokenQuery::create()->findOneByIdToken($tokenTransfer1->getIdToken());
        self::assertNotNull($tokenEntity);
        self::assertNotNull($tokenEntity->getDeletedAt());

        $tokenEntity = VsyWorldlineTokenQuery::create()->findOneByIdToken($tokenTransfer5->getIdToken());
        self::assertNotNull($tokenEntity);
        self::assertNotNull($tokenEntity->getDeletedAt());

        $tokenEntity = VsyWorldlineTokenQuery::create()->findOneByIdToken($tokenTransfer2->getIdToken());
        self::assertNotNull($tokenEntity);
        self::assertNull($tokenEntity->getDeletedAt());

        $tokenEntity = VsyWorldlineTokenQuery::create()->findOneByIdToken($tokenTransfer3->getIdToken());
        self::assertNotNull($tokenEntity);
        self::assertNull($tokenEntity->getDeletedAt());

        $tokenEntity = VsyWorldlineTokenQuery::create()->findOneByIdToken($tokenTransfer4->getIdToken());
        self::assertNotNull($tokenEntity);
        self::assertNull($tokenEntity->getDeletedAt());

        $tokenEntity = VsyWorldlineTokenQuery::create()->findOneByIdToken($tokenTransfer6->getIdToken());
        self::assertNotNull($tokenEntity);
        self::assertNull($tokenEntity->getDeletedAt());

        $tokenEntity = VsyWorldlineTokenQuery::create()->findOneByIdToken($tokenTransfer7->getIdToken());
        self::assertNotNull($tokenEntity);
        self::assertNull($tokenEntity->getDeletedAt());
    }

    public function testFindPaymentTokenByIdReturnsTheTokenIfItExists()
    {
        // Arrange
        VsyWorldlineTokenQuery::create()->filterByDeletedAt(comparison: Criteria::ISNOTNULL)->delete(); // make sure there isn't already a token that should be deleted;

        $customerWithOrders = $this->tester->createCustomerTransfer(static::TEST_USERNAME, static::TEST_PASSWORD);

        $tokenTransfer1 = $this->tester->havePaymentTokenWithThreeDSecureDataForCustomer(
            $customerWithOrders,
            [WorldlineCreditCardTokenTransfer::DELETED_AT => null, WorldlineCreditCardTokenTransfer::TOKEN => 'tokenToDelete']
        );
        $apiLogger = $this->getMockBuilder(WorldlineApiCallLoggerInterface::class)->getMock();

        $wlConnection = new TestingConnection(
            $apiLogger,
            $this->getMockConnectionResponse(204, array('Content-Type' => 'application/json'), null),
        );
        $businessFactoryMock = $this->createBusinessFactoryMock($wlConnection);

        $sut = $this->createFacadeMock($wlConnection, $businessFactoryMock);

        $tokenTransfer = new WorldlineDeleteTokenRequestTransfer();
        $tokenTransfer->setIdToken($tokenTransfer1->getIdToken());
        $tokenTransfer->setIdCustomer($customerWithOrders->getIdCustomer());

        // Act
        /** @var WorldlineCreditCardTokenTransfer $tokenTransfer */
        $tokenTransfer = $sut->findPaymentTokenById($tokenTransfer->getIdToken());

        self::assertTrue($tokenTransfer instanceof WorldlineCreditCardTokenTransfer);
        self::assertSame($tokenTransfer1->getIdToken(), $tokenTransfer->getIdToken());
        self::assertSame($tokenTransfer1->getToken(), $tokenTransfer->getToken());
    }

    /**
     * @return void
     */
    public function testGetPaymentTokensReturnsSuccessfulContainingTokensIfThereAreTokens(): void
    {
        // prepare
        $unitUnderTest = new WorldlineFacade();
        $customerWithOrders = $this->tester->createCustomerTransfer(static::TEST_USERNAME, static::TEST_PASSWORD);
        $idCustomerWithOrders = $customerWithOrders->getIdCustomer();
        $referenceCustomerWithOrders = $customerWithOrders->getCustomerReference();
        $worldlinePaymentTokenRequestTransfer = (new WorldlinePaymentTokenRequestTransfer())
            ->setCustomerReference($referenceCustomerWithOrders);
        $vsyWorldlineTokenEntity = $this->createTokenInDatabase($idCustomerWithOrders);

        // execute
        $result = $unitUnderTest->getPaymentTokens($worldlinePaymentTokenRequestTransfer);

        // verify
        self::assertNotNull($result);
        self::assertTrue($result->getIsSuccessful());
        self::assertEquals($referenceCustomerWithOrders, $result->getCustomerReference());
        self::assertEquals(1, $result->getTokens()->count());
        self::assertEquals($vsyWorldlineTokenEntity->getToken(), $result->getTokens()[0]->getToken());
        self::assertEquals($vsyWorldlineTokenEntity->getPaymentMethodKey(), $result->getTokens()[0]->getPaymentMethodKey());
        self::assertEquals($vsyWorldlineTokenEntity->getHolderName(), $result->getTokens()[0]->getHolderName());
        self::assertEquals($vsyWorldlineTokenEntity->getObfuscatedCardNumber(), $result->getTokens()[0]->getObfuscatedCardNumber());
        self::assertEquals($vsyWorldlineTokenEntity->getExpiryMonth(), $result->getTokens()[0]->getExpiryMonth());
        self::assertEquals($vsyWorldlineTokenEntity->getFkCustomer(), $result->getTokens()[0]->getFkCustomer());
    }

    /**
     * @return void
     */
    public function testGetPaymentTokensReturnsSuccessfulWithoutTokensWhenThereAreNoTokens(): void
    {
        // prepare
        $unitUnderTest = new WorldlineFacade();
        $customerWithOrders = $this->tester->createCustomerTransfer(static::TEST_USERNAME, static::TEST_PASSWORD);
        $idCustomerWithOrders = $customerWithOrders->getIdCustomer();
        $referenceCustomerWithOrders = $customerWithOrders->getCustomerReference();
        $worldlinePaymentTokenRequestTransfer = (new WorldlinePaymentTokenRequestTransfer())
            ->setCustomerReference($referenceCustomerWithOrders);

        // execute
        $result = $unitUnderTest->getPaymentTokens($worldlinePaymentTokenRequestTransfer);

        // verify
        self::assertNotNull($result);
        self::assertTrue($result->getIsSuccessful());
        self::assertEquals($referenceCustomerWithOrders, $result->getCustomerReference());
        self::assertEquals(0, $result->getTokens()->count());
    }

    /**
     * @return void
     */
    public function testGetPaymentTokensReturnsUnsuccessfulWhenThereCustomerIsInvalid(): void
    {
        // prepare
        $unitUnderTest = new WorldlineFacade();
        $referenceCustomer = 'diesenKundenGibtsNicht123';
        $worldlinePaymentTokenRequestTransfer = (new WorldlinePaymentTokenRequestTransfer())
            ->setCustomerReference($referenceCustomer);

        // execute
        $result = $unitUnderTest->getPaymentTokens($worldlinePaymentTokenRequestTransfer);

        // verify
        self::assertNotNull($result);
        self::assertFalse($result->getIsSuccessful());
        self::assertEquals($referenceCustomer, $result->getCustomerReference());
        self::assertEquals(0, $result->getTokens()->count());
    }

    /**
     * @param int|null $fkCustomerWithOrders
     *
     * @return \Orm\Zed\Worldline\Persistence\VsyWorldlineToken
     */
    public function createTokenInDatabase(?int $fkCustomerWithOrders): VsyWorldlineToken
    {
        $vsyWorldlineThreeDSecureResultEntity = VsyWorldlineThreeDSecureResultQuery::create()
            ->filterByAcsTransactionId("acsTransaction123")
            ->findOneOrCreate();
        $vsyWorldlineThreeDSecureResultEntity->save();

        $spyPaymentProvider = SpyPaymentProviderQuery::create()
            ->findOneOrCreate();
        $spyPaymentProvider->save();

        $spyPaymentMethodEntity = SpyPaymentMethodQuery::create()
            ->filterByPaymentMethodKey('paymentMethodKey')
            ->filterByName('surpriseMeWithAName123')
            ->filterByFkPaymentProvider($spyPaymentProvider->getIdPaymentProvider())
            ->findOneOrCreate();
        $spyPaymentMethodEntity->save();

        $vsyWorldlineTokenEntity = VsyWorldlineTokenQuery::create()
            ->filterByToken("4711-0815-1234-5678")
            ->findOneOrCreate();
        $vsyWorldlineTokenEntity
            ->setPaymentMethodKey($spyPaymentMethodEntity->getPaymentMethodKey())
            ->setUpdatedAt(new DateTime())
            ->setFkCustomer($fkCustomerWithOrders)
            ->setFkInitialThreeDSecureResult($vsyWorldlineThreeDSecureResultEntity->getIdThreeDSecureResult())
            ->setInitialSchemeTransactionId($fkCustomerWithOrders . '_schemeTransactionId');
        $vsyWorldlineTokenEntity->save();

        return $vsyWorldlineTokenEntity;
    }

    /**
     * @param \Ingenico\Connect\Sdk\Connection $connection
     * @param array|null $onlyMethods
     *
     * @return \ValanticSpryker\Zed\Worldline\Business\WorldlineBusinessFactory
     */
    public function createBusinessFactoryMock(
        Connection $connection,
        ?array $onlyMethods = []
    ): WorldlineBusinessFactory {
        $onlyMethods[] = 'createWorldlineConnection';

        /** @var \PHPUnit\Framework\MockObject\MockObject|\ValanticSpryker\Zed\Worldline\Business\WorldlineBusinessFactory $businessFactoryMock */
        $businessFactoryMock = $this
            ->getMockBuilder(WorldlineBusinessFactory::class)
            ->onlyMethods($onlyMethods)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->getMock();

        $businessFactoryMock->setConfig(new WorldlineConfig());
        $businessFactoryMock->setEntityManager(new WorldlineEntityManager());
        $businessFactoryMock->setRepository(new WorldlineRepository());
        $businessFactoryMock->setQueryContainer(new WorldlineQueryContainer());

        $container = new Container();
        $worldlineConnectorDependencyProvider = new WorldlineDependencyProvider();
        $worldlineConnectorDependencyProvider->provideBusinessLayerDependencies($container);
        $businessFactoryMock->setContainer($container);

        $businessFactoryMock
            ->expects($this->any())
            ->method('createWorldlineConnection')
            ->willReturn($connection);

        return $businessFactoryMock;
    }

    /**
     * @param \Ingenico\Connect\Sdk\Connection $worldlineConnection
     * @param \ValanticSpryker\Zed\Worldline\Business\WorldlineBusinessFactory|null $worldlineConnectorBusinessFactory
     *
     * @return \ValanticSpryker\Zed\Worldline\Business\WorldlineFacadeInterface
     */
    public function createFacadeMock(
        Connection $worldlineConnection,
        ?WorldlineBusinessFactory $worldlineConnectorBusinessFactory = null
    ): WorldlineFacadeInterface {
        if (!$worldlineConnectorBusinessFactory) {
            $worldlineConnectorBusinessFactory = $this->createBusinessFactoryMock($worldlineConnection);
        }

        $facade = $this
            ->getMockBuilder(WorldlineFacade::class)
            ->onlyMethods(['getFactory'])->getMock();
        $facade->expects($this->any())
            ->method('getFactory')
            ->willReturn($worldlineConnectorBusinessFactory);
        $facade->setRepository(new WorldlineRepository());

        return $facade;
    }

    /**
     * @param int $httpStatusCode
     * @param array<string> $headers
     * @param string $body
     *
     * @return \Ingenico\Connect\Sdk\ConnectionResponse
     */
    public function getMockConnectionResponse($httpStatusCode, $headers = [], $body = '{}'): ConnectionResponse
    {
        $connectionResponse = $this->getMockBuilder(ConnectionResponse::class)->getMock();

        $connectionResponse->method('getHttpStatusCode')->willReturn($httpStatusCode);
        $connectionResponse->method('getHeaders')->willReturn($headers);
        $returnMap = [];
        foreach ($headers as $key => $value) {
            $returnMap[] = [$key, $value];
        }
        $connectionResponse->method('getHeaderValue')->willReturnMap($returnMap);
        $connectionResponse->method('getBody')->willReturn($body);

        return $connectionResponse;
    }

    /**
     * @param \Generated\Shared\Transfer\SaveOrderTransfer $saveOrderTransfer
     * @param \Generated\Shared\Transfer\QuoteTransfer $quote
     * @param string $expectedHostedCheckoutId
     * @param string $initialTransactionStatus
     * @param string|null $initialTransactionStatusCategory
     *
     * @return void
     */
    protected function haveHostedCheckout(
        SaveOrderTransfer $saveOrderTransfer,
        QuoteTransfer $quote,
        string $expectedHostedCheckoutId,
        string $initialTransactionStatus,
        ?string $initialTransactionStatusCategory
    ): void {
        $this->tester->haveHostedCheckout(
            $saveOrderTransfer,
            $quote,
            [],
            [
                WorldlinePaymentHostedCheckoutTransfer::HOSTED_CHECKOUT_ID => $expectedHostedCheckoutId,
                WorldlinePaymentHostedCheckoutTransfer::PARTIAL_REDIRECT_URL => 'https://some.secure.url',
                WorldlinePaymentHostedCheckoutTransfer::RETURNMAC => Uuid::uuid(),
                WorldlinePaymentHostedCheckoutTransfer::CREATED_AT => (new DateTime())->setTimestamp(time() - 8000)->format('Y-m-d H:i:s'),
                // 8000 > 7200 (number of seconds in 2 hours)
                WorldlinePaymentHostedCheckoutTransfer::UPDATED_AT => (new DateTime())->setTimestamp(time() - 8000)->format('Y-m-d H:i:s'),
            ],
            [
                PaymentWorldlineTransactionStatusTransfer::STATUS => $initialTransactionStatus,
                PaymentWorldlineTransactionStatusTransfer::STATUS_CATEGORY => $initialTransactionStatusCategory,
                PaymentWorldlineTransactionStatusTransfer::STATUS_CODE => null,
                PaymentWorldlineTransactionStatusTransfer::FK_WORLDLINE_API_LOG => null,
                PaymentWorldlineTransactionStatusTransfer::FK_WORLDLINE_REST_LOG => null,
                PaymentWorldlineTransactionStatusTransfer::AMOUNT => $quote->getTotals()->getGrandTotal(),
                PaymentWorldlineTransactionStatusTransfer::TRANSACTION_TYPE => 'payment',
                PaymentWorldlineTransactionStatusTransfer::STATUS_CODE_CHANGE_DATE_TIME => (new DateTime())->setTimestamp(time() - 5)->format(
                    'YmdHis',
                ), // 5 seconds ago

            ],
        );
    }

    /**
     * @param \Generated\Shared\Transfer\SaveOrderTransfer $saveOrderTransfer
     * @param \Generated\Shared\Transfer\QuoteTransfer $quote
     * @param string $expectedPaymentId
     * @param string $expectedHostedCheckoutId
     * @param string $initialTransactionStatus
     * @param string|null $initialTransactionStatusCategory
     *
     * @return void
     */
    protected function haveHostedCheckoutWithPaymentId(
        SaveOrderTransfer $saveOrderTransfer,
        QuoteTransfer $quote,
        string $expectedPaymentId,
        string $expectedHostedCheckoutId,
        string $initialTransactionStatus,
        ?string $initialTransactionStatusCategory
    ): void {
        $this->tester->haveHostedCheckout(
            $saveOrderTransfer,
            $quote,
            [
                PaymentWorldlineTransfer::PAYMENT_ID => $expectedPaymentId,
            ],
            [
                WorldlinePaymentHostedCheckoutTransfer::HOSTED_CHECKOUT_ID => $expectedHostedCheckoutId,
                WorldlinePaymentHostedCheckoutTransfer::PARTIAL_REDIRECT_URL => 'https://some.secure.url',
                WorldlinePaymentHostedCheckoutTransfer::RETURNMAC => Uuid::uuid(),
                WorldlinePaymentHostedCheckoutTransfer::CREATED_AT => (new DateTime())->setTimestamp(time() - 8000)->format('Y-m-d H:i:s'),
                // 8000 > 7200 (number of seconds in 2 hours)
                WorldlinePaymentHostedCheckoutTransfer::UPDATED_AT => (new DateTime())->setTimestamp(time() - 8000)->format('Y-m-d H:i:s'),
            ],
            [
                PaymentWorldlineTransactionStatusTransfer::STATUS => $initialTransactionStatus,
                PaymentWorldlineTransactionStatusTransfer::STATUS_CATEGORY => $initialTransactionStatusCategory,
                PaymentWorldlineTransactionStatusTransfer::STATUS_CODE => null,
                PaymentWorldlineTransactionStatusTransfer::FK_WORLDLINE_API_LOG => null,
                PaymentWorldlineTransactionStatusTransfer::FK_WORLDLINE_REST_LOG => null,
                PaymentWorldlineTransactionStatusTransfer::AMOUNT => $quote->getTotals()->getGrandTotal(),
                PaymentWorldlineTransactionStatusTransfer::TRANSACTION_TYPE => 'payment',
                PaymentWorldlineTransactionStatusTransfer::STATUS_CODE_CHANGE_DATE_TIME => (new DateTime())->setTimestamp(time() - 5)->format(
                    'YmdHis',
                ), // 5 seconds ago
            ],
        );
    }

    /**
     * @param string $exampleFileToLoad
     * @param string $expectedPaymentId
     * @param string|null $orderReference
     *
     * @return \Generated\Shared\Transfer\QueueSendMessageTransfer
     */
    protected function createPaymentEventQueueMessage(
        string $exampleFileToLoad,
        string $expectedPaymentId,
        ?string $orderReference
    ): QueueSendMessageTransfer {
        $queueMessage = new QueueSendMessageTransfer();
        $headers = [

        ];
        $queueMessage->setHeaders($headers);

        $pathToResponses = __DIR__ . $exampleFileToLoad;
        $body = file_get_contents($pathToResponses);

        $bodyArray = json_decode($body, true);

        $bodyArray['payment']['id'] = $expectedPaymentId;
        $bodyArray['payment']['paymentOutput']['references']['merchantReference'] = $orderReference;

        $body = json_encode($bodyArray, JSON_PRETTY_PRINT);

        $queueMessage->setBody($body);

        return $queueMessage;
    }

    /**
     * @param string $exampleFileToLoad
     * @param string $expectedTokenId
     *
     * @return \Generated\Shared\Transfer\QueueSendMessageTransfer
     */
    private function createTokenEventQueueMessage(string $exampleFileToLoad, string $expectedTokenId): QueueSendMessageTransfer
    {
        $queueMessage = new QueueSendMessageTransfer();
        $headers = [

        ];
        $queueMessage->setHeaders($headers);

        $pathToResponses = __DIR__ . $exampleFileToLoad;
        $body = file_get_contents($pathToResponses);

        $bodyArray = json_decode($body, true);

        $bodyArray['token']['id'] = $expectedTokenId;

        $body = json_encode($bodyArray, JSON_PRETTY_PRINT);

        $queueMessage->setBody($body);

        return $queueMessage;
    }

    /**
     * @return \Generated\Shared\Transfer\PaymentMethodsTransfer
     */
    protected function getPaymentMethodsTransfer(): PaymentMethodsTransfer
    {
        $paymentProviderTransfer = (new PaymentProviderTransfer())
            ->setName(WorldlineConfig::PROVIDER_NAME);

        $paymentMethodsTransfer = new PaymentMethodsTransfer();
        $paymentMethodsTransfer->addMethod(
            (new PaymentMethodTransfer())
                ->setName('PayPal')
                ->setPaymentMethodKey(WorldlineConfig::PAYMENT_METHOD_PAYPAL)
                ->setPaymentProvider($paymentProviderTransfer),
        )
            ->addMethod(
                (new PaymentMethodTransfer())
                    ->setName('Visa')
                    ->setPaymentMethodKey(WorldlineConfig::PAYMENT_METHOD_CREDIT_CARD_VISA)
                    ->setPaymentProvider($paymentProviderTransfer),
            )
            ->addMethod(
                (new PaymentMethodTransfer())
                    ->setName('Visa')
                    ->setPaymentMethodKey(WorldlineConfig::PAYMENT_METHOD_CREDIT_CARD_MASTER_CARD)
                    ->setPaymentProvider($paymentProviderTransfer),
            );

        return $paymentMethodsTransfer;
    }
}
