<?php

declare(strict_types = 1);

namespace ValanticSprykerTest\Zed\Worldline\Business;

use DateInterval;
use DateTime;
use Faker\Provider\Uuid;
use Generated\Shared\Transfer\PaymentWorldlineTransactionStatusTransfer;
use Generated\Shared\Transfer\PaymentWorldlineTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\SaveOrderTransfer;
use Generated\Shared\Transfer\WorldlinePaymentHostedCheckoutTransfer;
use Ingenico\Connect\Sdk\Connection;
use Ingenico\Connect\Sdk\ConnectionResponse;
use Orm\Zed\Oms\Persistence\SpyOmsOrderItemStateQuery;
use Orm\Zed\Sales\Persistence\SpySalesOrderQuery;
use ValanticSpryker\Shared\Worldline\WorldlineConstants;
use ValanticSpryker\Zed\Oms\Business\OmsFacade;
use ValanticSpryker\Zed\Worldline\Business\Logger\WorldlineApiCallLoggerInterface;
use ValanticSpryker\Zed\Worldline\Business\WorldlineBusinessFactory;
use ValanticSpryker\Zed\Worldline\Business\WorldlineFacade;
use ValanticSpryker\Zed\Worldline\Business\WorldlineFacadeInterface;
use ValanticSpryker\Zed\Worldline\Persistence\WorldlineEntityManager;
use ValanticSpryker\Zed\Worldline\Persistence\WorldlineRepository;
use ValanticSpryker\Zed\Worldline\WorldlineConfig;
use ValanticSpryker\Zed\Worldline\WorldlineDependencyProvider;
use ValanticSprykerTest\Shared\Base\AbstractTest;
use ValanticSprykerTest\Zed\Worldline\WorldlineBusinessTester;
use Spryker\Zed\Kernel\Container;

/**
 * Auto-generated group annotations
 *
 * @group ValanticSprykerTest
 * @group Zed
 * @group Worldline
 * @group Business
 * @group WorldlineOmsProcessTest
 * Add your own group annotations below this line
 * @group Worldline
 */
class WorldlineOmsProcessTest extends AbstractTest
{
    /**
     * @var string
     */
    protected const TEST_USERNAME = 'test username';

    /**
     * @var string
     */
    protected const TEST_PASSWORD = 'change123';

    /**
     * @var string
     */
    public const HOSTED_CHECKOUT_ID = 'HostedCheckout112';

    /**
     * @var string
     */
    private const HOSTED_CHECKOUT_STATUS_PENDING = 'hosted checkout status pending';

    /**
     * @var string
     */
    private const STATUS_HOSTED_CHECKOUT_STATUS_FAILED = 'hosted checkout status cancelled';

    /**
     * @var string
     */
    private const PAYMENT_PENDING = 'payment pending';

    /**
     * @var string
     */
    private const READY_FOR_ORDER_EXPORT = 'ready for order export';

    /**
     * @var string
     */
    private const PAYMENT_CANCELLED = 'payment cancelled';

    /**
     * @var string
     */
    private const PAYMENT_REJECTED = 'payment rejected';

    /**
     * @var string
     */
    private const WAIT_FOR_PAYMENT_STATUS = 'wait for payment status';

    /**
     * @var string
     */
    private const CAPTURE_PENDING = 'capture pending';

    /**
     * @var string
     */
    private const CAPTURE_TIMED_OUT = 'capture timed out';

    /**
     * @var string
     */
    private const CAPTURE_RESTARTED = 'capture restarted';

    /**
     * @var \ValanticSprykerTest\Zed\Worldline\WorldlineBusinessTester
     */
    protected WorldlineBusinessTester $tester;

    /**
     * @return void
     */
    public function testTransitionFromHostedCheckoutStatusPendingToHostedCheckoutStatusFailedDueToTimeout(): void
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

        $expectedHostedCheckoutId = static::HOSTED_CHECKOUT_ID;

        $this->haveHostedCheckoutWithPaymentId(
            $saveOrderTransfer,
            $quote,
            null,
            $expectedHostedCheckoutId,
            WorldlineConstants::STATUS_HOSTED_CHECKOUT_CREATED,
            null,
        );

        $idSalesOrder = $saveOrderTransfer->getIdSalesOrder();
        $salesOrderEntity = SpySalesOrderQuery::create()->findOneByIdSalesOrder($idSalesOrder);
        $orderItems = $salesOrderEntity->getItems();

        foreach ($orderItems as $orderItem) {
            $this->tester->setItemState($orderItem->getIdSalesOrderItem(), self::HOSTED_CHECKOUT_STATUS_PENDING);
        }

        // Act
        (new OmsFacade())->checkConditions([]);

        // Assert
        $salesOrderEntity = SpySalesOrderQuery::create()->findOneByIdSalesOrder($idSalesOrder);
        $orderItems = $salesOrderEntity->getItems();

        foreach ($orderItems as $orderItem) {
            self::assertSame(self::STATUS_HOSTED_CHECKOUT_STATUS_FAILED, $orderItem->getState()->getName());
        }
    }

    /**
     * @return void
     */
    public function testTransitionFromPaymentCreatedToReadyForOrderExport(): void
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

        $expectedHostedCheckoutId = static::HOSTED_CHECKOUT_ID;

        $this->haveHostedCheckout(
            $saveOrderTransfer,
            $quote,
            $expectedHostedCheckoutId,
            WorldlineConstants::STATUS_HOSTED_CHECKOUT_STATUS_PAYMENT_CREATED,
            null,
            10,
        );

        $this->haveHostedCheckout(
            $saveOrderTransfer,
            $quote,
            $expectedHostedCheckoutId,
            WorldlineConstants::STATUS_PAYMENT_PENDING_APPROVAL,
            null,
        );

        $idSalesOrder = $saveOrderTransfer->getIdSalesOrder();
        $salesOrderEntity = SpySalesOrderQuery::create()->findOneByIdSalesOrder($idSalesOrder);
        $orderItems = $salesOrderEntity->getItems();

        foreach ($orderItems as $orderItem) {
            $this->tester->setItemState($orderItem->getIdSalesOrderItem(), self::PAYMENT_PENDING);
        }

        // Act
        (new OmsFacade())->checkConditions([]);

        // Assert
        $salesOrderEntity = SpySalesOrderQuery::create()->findOneByIdSalesOrder($idSalesOrder);
        $orderItems = $salesOrderEntity->getItems();

        foreach ($orderItems as $orderItem) {
            self::assertSame(self::READY_FOR_ORDER_EXPORT, $orderItem->getState()->getName());
        }
    }

    /**
     * @return void
     */
    public function testTransitionFromPaymentCreatedToPaymentCancelled(): void
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

        $expectedHostedCheckoutId = static::HOSTED_CHECKOUT_ID;

        $this->haveHostedCheckout(
            $saveOrderTransfer,
            $quote,
            $expectedHostedCheckoutId,
            WorldlineConstants::STATUS_CANCELLED,
            null,
        );

        $idSalesOrder = $saveOrderTransfer->getIdSalesOrder();
        $salesOrderEntity = SpySalesOrderQuery::create()->findOneByIdSalesOrder($idSalesOrder);
        $orderItems = $salesOrderEntity->getItems();

        foreach ($orderItems as $orderItem) {
            $this->tester->setItemState($orderItem->getIdSalesOrderItem(), self::PAYMENT_PENDING);
        }

        // Act
        (new OmsFacade())->checkConditions([]);

        // Assert
        $salesOrderEntity = SpySalesOrderQuery::create()->findOneByIdSalesOrder($idSalesOrder);
        $orderItems = $salesOrderEntity->getItems();

        foreach ($orderItems as $orderItem) {
            self::assertSame(self::PAYMENT_CANCELLED, $orderItem->getState()->getName());
        }
    }

    /**
     * @return void
     */
    public function testTransitionFromPaymentCreatedToPaymentRejected(): void
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

        $expectedHostedCheckoutId = static::HOSTED_CHECKOUT_ID;

        $this->haveHostedCheckout(
            $saveOrderTransfer,
            $quote,
            $expectedHostedCheckoutId,
            WorldlineConstants::STATUS_REDIRECTED,
            WorldlineConstants::STATUS_CATEGORY_UNSUCCESSFUL,
        );

        $idSalesOrder = $saveOrderTransfer->getIdSalesOrder();
        $salesOrderEntity = SpySalesOrderQuery::create()->findOneByIdSalesOrder($idSalesOrder);
        $orderItems = $salesOrderEntity->getItems();

        foreach ($orderItems as $orderItem) {
            $this->tester->setItemState($orderItem->getIdSalesOrderItem(), self::PAYMENT_PENDING);
        }

        // Act
        (new OmsFacade())->checkConditions([]);

        // Assert
        $salesOrderEntity = SpySalesOrderQuery::create()->findOneByIdSalesOrder($idSalesOrder);
        $orderItems = $salesOrderEntity->getItems();

        foreach ($orderItems as $orderItem) {
            self::assertSame(self::PAYMENT_REJECTED, $orderItem->getState()->getName());
        }
    }

    /**
     * @return void
     */
    public function testTransitionFromPaymentCreatedToPaymentRejectedWhenStatusCodeIs150(): void
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
        $this->createFacadeMock($wlConnection, $businessFactoryMock);

        $expectedHostedCheckoutId = static::HOSTED_CHECKOUT_ID;

        $this->haveHostedCheckout(
            $saveOrderTransfer,
            $quote,
            $expectedHostedCheckoutId,
            WorldlineConstants::STATUS_CREATED,
            WorldlineConstants::STATUS_CATEGORY_UNSUCCESSFUL,
        );

        $idSalesOrder = $saveOrderTransfer->getIdSalesOrder();
        $salesOrderEntity = SpySalesOrderQuery::create()->findOneByIdSalesOrder($idSalesOrder);
        $orderItems = $salesOrderEntity->getItems();

        foreach ($orderItems as $orderItem) {
            $this->tester->setItemState($orderItem->getIdSalesOrderItem(), self::PAYMENT_PENDING);
        }

        // Act
        (new OmsFacade())->checkConditions([]);

        // Assert
        $salesOrderEntity = SpySalesOrderQuery::create()->findOneByIdSalesOrder($idSalesOrder);
        $orderItems = $salesOrderEntity->getItems();

        foreach ($orderItems as $orderItem) {
            self::assertSame(self::PAYMENT_REJECTED, $orderItem->getState()->getName());
        }
    }

    /**
     * @return void
     */
    public function testEventGetPaymentStatusIsTriggeredAndCorrectlySetsStatusInTransactionLog(): void
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
        $this->createFacadeMock($wlConnection, $businessFactoryMock);

        $expectedHostedCheckoutId = static::HOSTED_CHECKOUT_ID;

        $this->haveHostedCheckout(
            $saveOrderTransfer,
            $quote,
            $expectedHostedCheckoutId,
            WorldlineConstants::STATUS_REDIRECTED,
            WorldlineConstants::STATUS_CATEGORY_UNSUCCESSFUL,
        );

        $idSalesOrder = $saveOrderTransfer->getIdSalesOrder();
        $salesOrderEntity = SpySalesOrderQuery::create()->findOneByIdSalesOrder($idSalesOrder);
        $orderItems = $salesOrderEntity->getItems();

        $stateName = self::PAYMENT_PENDING;
        $eventName = 'get payment status';
        $dateTime = new DateTime('now');
        $dateTime->sub(DateInterval::createFromDateString('10 minutes'));
        foreach ($orderItems as $orderItem) {
            $this->tester->setItemState($orderItem->getIdSalesOrderItem(), $stateName);
            $orderItemStateQuery = new SpyOmsOrderItemStateQuery();
            $omsOrderItemStateEntity = $orderItemStateQuery->filterByName($stateName)->findOne();
            $omsEventTimeoutEntity = $this->tester->haveOmsEventTimeoutEntity([
                'fk_sales_order_item' => $orderItem->getIdSalesOrderItem(),
                'fk_oms_order_item_state' => $omsOrderItemStateEntity->getIdOmsOrderItemState(),
                'event' => $eventName,
                'timeout' => $dateTime,
            ]);
            $orderItem->addEventTimeout($omsEventTimeoutEntity);
            $orderItem->setState($omsOrderItemStateEntity);
            $salesOrderEntity->addItem($orderItem);
        }

        // Act
        (new OmsFacade())->checkTimeouts([]);

        // Assert
        $salesOrderEntity = SpySalesOrderQuery::create()->findOneByIdSalesOrder($idSalesOrder);
        $orderItems = $salesOrderEntity->getItems();

        foreach ($orderItems as $orderItem) {
            self::assertSame(self::WAIT_FOR_PAYMENT_STATUS, $orderItem->getState()->getName());
        }
    }

    /**
     * @return void
     */
    public function testTransitionFromCapturePendingToCaptureTimedOutWhenStatusStaysTheSameFor24Hours(): void
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
        $this->createFacadeMock($wlConnection, $businessFactoryMock);

        $expectedHostedCheckoutId = static::HOSTED_CHECKOUT_ID;
        $expectedPaymentId = 'expected_payment_id';

        $this->haveHostedCheckoutWithPaymentId(
            $saveOrderTransfer,
            $quote,
            $expectedPaymentId,
            $expectedHostedCheckoutId,
            WorldlineConstants::STATUS_CAPTURE_REQUESTED,
            WorldlineConstants::STATUS_CATEGORY_PENDING_CONNECT_OR_3RD_PARTY,
            25 * 60 * 60,
            // 25 Stunden in der Vergangenheit
        );

        $idSalesOrder = $saveOrderTransfer->getIdSalesOrder();
        $salesOrderEntity = SpySalesOrderQuery::create()->findOneByIdSalesOrder($idSalesOrder);
        $orderItems = $salesOrderEntity->getItems();

        foreach ($orderItems as $orderItem) {
            $this->tester->setItemState($orderItem->getIdSalesOrderItem(), self::CAPTURE_PENDING);
        }

        // Act
        (new OmsFacade())->checkConditions([]);

        // Assert
        $salesOrderEntity = SpySalesOrderQuery::create()->findOneByIdSalesOrder($idSalesOrder);
        $orderItems = $salesOrderEntity->getItems();

        foreach ($orderItems as $orderItem) {
            self::assertSame(self::CAPTURE_TIMED_OUT, $orderItem->getState()->getName());
        }
    }

    /**
     * @return void
     */
    public function testNotTransitionFromCapturePendingToCaptureTimedOutWhenStatusStaysTheSameFor24HoursButWasResetInTheLast24Hours(): void
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
        $this->createFacadeMock($wlConnection, $businessFactoryMock);

        $expectedHostedCheckoutId = static::HOSTED_CHECKOUT_ID;
        $expectedPaymentId = 'expected_payment_id';

        $idSalesOrder = $saveOrderTransfer->getIdSalesOrder();
        $salesOrderEntity = SpySalesOrderQuery::create()->findOneByIdSalesOrder($idSalesOrder);
        $orderItems = $salesOrderEntity->getItems();

        foreach ($orderItems as $orderItem) {
            $this->tester->setItemState($orderItem->getIdSalesOrderItem(), self::CAPTURE_RESTARTED);
        }

        $this->haveHostedCheckoutWithPaymentId(
            $saveOrderTransfer,
            $quote,
            $expectedPaymentId,
            $expectedHostedCheckoutId,
            WorldlineConstants::STATUS_CAPTURE_REQUESTED,
            WorldlineConstants::STATUS_CATEGORY_PENDING_CONNECT_OR_3RD_PARTY,
            25 * 60 * 60,
            // 25 hours in the past
        );

        foreach ($orderItems as $orderItem) {
            $this->tester->setItemState($orderItem->getIdSalesOrderItem(), self::CAPTURE_PENDING);
        }

        // Act
        (new OmsFacade())->checkConditions([]);

        // Assert
        $salesOrderEntity = SpySalesOrderQuery::create()->findOneByIdSalesOrder($idSalesOrder);
        $orderItems = $salesOrderEntity->getItems();

        foreach ($orderItems as $orderItem) {
            self::assertSame(self::CAPTURE_PENDING, $orderItem->getState()->getName());
        }
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

        return $facade;
    }

    /**
     * @param $httpStatusCode
     * @param $headers
     * @param $body
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
     * @param int $secondsInThePast
     *
     * @return void
     */
    protected function haveHostedCheckout(
        SaveOrderTransfer $saveOrderTransfer,
        QuoteTransfer $quote,
        string $expectedHostedCheckoutId,
        string $initialTransactionStatus,
        ?string $initialTransactionStatusCategory,
        int $secondsInThePast = 5
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
                PaymentWorldlineTransactionStatusTransfer::STATUS_CODE_CHANGE_DATE_TIME => (new DateTime())->setTimestamp(time() - $secondsInThePast)->format(
                    'YmdHis',
                ), // 5 seconds ago

            ],
        );
    }

    /**
     * @param \Generated\Shared\Transfer\SaveOrderTransfer $saveOrderTransfer
     * @param \Generated\Shared\Transfer\QuoteTransfer $quote
     * @param string|null $expectedPaymentId
     * @param string $expectedHostedCheckoutId
     * @param string $initialTransactionStatus
     * @param string|null $initialTransactionStatusCategory
     *
     * @return void
     */
    protected function haveHostedCheckoutWithPaymentId(
        SaveOrderTransfer $saveOrderTransfer,
        QuoteTransfer $quote,
        ?string $expectedPaymentId,
        string $expectedHostedCheckoutId,
        string $initialTransactionStatus,
        ?string $initialTransactionStatusCategory,
        int $secondsInThePast = 5
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
                PaymentWorldlineTransactionStatusTransfer::STATUS_CODE_CHANGE_DATE_TIME => (new DateTime())->setTimestamp(time() - $secondsInThePast)->format(
                    'YmdHis',
                ), // 5 seconds ago
            ],
        );
    }
}
