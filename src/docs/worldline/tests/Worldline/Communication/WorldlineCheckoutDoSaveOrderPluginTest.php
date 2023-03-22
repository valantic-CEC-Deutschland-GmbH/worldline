<?php

declare(strict_types = 1);

namespace ValanticSprykerTest\Zed\Worldline\Communication;

use Generated\Shared\DataBuilder\QuoteBuilder;
use Generated\Shared\Transfer\CustomerTransfer;
use Generated\Shared\Transfer\PaymentTransfer;
use Generated\Shared\Transfer\PaymentWorldlineTransfer;
use Generated\Shared\Transfer\ProductConcreteTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\SaveOrderTransfer;
use Generated\Shared\Transfer\TotalsTransfer;
use Generated\Shared\Transfer\WorldlinePaymentHostedCheckoutTransfer;
use Orm\Zed\Worldline\Persistence\VsyPaymentWorldlineHostedCheckoutQuery;
use Orm\Zed\Worldline\Persistence\VsyPaymentWorldlineQuery;
use ValanticSpryker\Zed\Worldline\Communication\Plugin\Checkout\WorldlineCheckoutDoSaveOrderPlugin;
use ValanticSpryker\Zed\Worldline\WorldlineConfig;
use ValanticSprykerTest\Shared\Base\AbstractTest;
use ValanticSprykerTest\Zed\Worldline\WorldlineCommunicationTester;

/**
 * Auto-generated group annotations
 *
 * @group ValanticSprykerTest
 * @group Zed
 * @group Worldline
 * @group Communication
 * @group WorldlineCheckoutDoSaveOrderPluginTest
 * Add your own group annotations below this line
 */
class WorldlineCheckoutDoSaveOrderPluginTest extends AbstractTest
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
     * @var int
     */
    protected const TEST_GRAND_TOTAL = 1;

    /**
     * @var \ValanticSprykerTest\Zed\Worldline\WorldlineCommunicationTester
     */
    protected WorldlineCommunicationTester $tester;

    /**
     * @return void
     */
    public function testSaveOrderSavesTheCorrectValuesInTheCorrectDBTables(): void
    {
        // Arrange

        $customerWithOrders = $this->createCustomerTransfer(static::TEST_USERNAME, static::TEST_PASSWORD);
        $quote = $this->createQuoteTransfer($customerWithOrders, [$this->createProductTransfer()], $this->createPaymentTransfer()->toArray());
        $saveOrderTransfer = $this->createOrderTransfer($quote);

        // Act
        (new WorldlineCheckoutDoSaveOrderPlugin())->saveOrder($quote, $saveOrderTransfer);

        // Assert
        $worldlinePaymentEntity = VsyPaymentWorldlineQuery::create()
            ->findOneByFkSalesOrder($saveOrderTransfer->getIdSalesOrder());

        self::assertNotNull($worldlinePaymentEntity);
        self::assertSame(WorldlineConfig::PAYMENT_METHOD_CREDIT_CARD_VISA, $worldlinePaymentEntity->getPaymentMethod());

        $worldlinePaymentHostedCheckoutEntity = VsyPaymentWorldlineHostedCheckoutQuery::create()
            ->findOneByFkPaymentWorldline($worldlinePaymentEntity->getIdPaymentWorldline());

        self::assertNotNull($worldlinePaymentHostedCheckoutEntity);
        self::assertSame('http://some.url?orderReference=' . $saveOrderTransfer->getOrderReference(), $worldlinePaymentHostedCheckoutEntity->getReturnUrl());
        self::assertSame(24, $worldlinePaymentHostedCheckoutEntity->getCustomerColorDepth());
        self::assertTrue($worldlinePaymentHostedCheckoutEntity->getCustomerJavaEnabled());
        self::assertSame('de_DE', $worldlinePaymentHostedCheckoutEntity->getCustomerBrowserLocale());
        self::assertSame('de_DE', $worldlinePaymentHostedCheckoutEntity->getCustomerSelectedLocale());
        self::assertSame('255.255.255.255', $worldlinePaymentHostedCheckoutEntity->getCustomerIpAddress());
        self::assertSame('Berlin', $worldlinePaymentHostedCheckoutEntity->getCustomerTimezone());
        self::assertSame(60, $worldlinePaymentHostedCheckoutEntity->getCustomerTimezoneOffset());
        self::assertSame('1024', $worldlinePaymentHostedCheckoutEntity->getCustomerScreenHeight());
        self::assertSame('800', $worldlinePaymentHostedCheckoutEntity->getCustomerScreenWidth());
        self::assertSame('Mozilla/5.0 (Linux; Android 12; SM-S906N Build/QP1A.190711.020; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/80.0.3987.119 Mobile Safari/537.36', $worldlinePaymentHostedCheckoutEntity->getCustomerUserAgent());
    }

    /**
     * @return \Generated\Shared\Transfer\SaveOrderTransfer
     */
    protected function createOrderTransfer($quote): SaveOrderTransfer
    {
        return $this->tester->haveOrderFromQuote($quote, $this->createStateMachine());
    }

    /**
     * @return string
     */
    protected function createStateMachine(): string
    {
        $testStateMachineProcessName = 'DummyPayment01';
        $this->tester->configureTestStateMachine([$testStateMachineProcessName]);

        return $testStateMachineProcessName;
    }

    /**
     * @param string $name
     * @param string $password
     *
     * @return \Generated\Shared\Transfer\CustomerTransfer
     */
    protected function createCustomerTransfer(string $name, string $password): CustomerTransfer
    {
        $customerTransfer = $this->tester->haveCustomer([
            CustomerTransfer::USERNAME => $name,
            CustomerTransfer::PASSWORD => $password,
            CustomerTransfer::NEW_PASSWORD => $password,
        ]);

        return $this->tester->confirmCustomer($customerTransfer);
    }

    /**
     * @return \Generated\Shared\Transfer\PaymentTransfer
     */
    protected function createPaymentTransfer(): PaymentTransfer
    {
        return (new PaymentTransfer())
            ->setPaymentSelection(WorldlineConfig::PAYMENT_METHOD_CREDIT_CARD_VISA)
            ->setPaymentWorldline((new PaymentWorldlineTransfer())->setPaymentHostedCheckout($this->createWorldlinePaymentHostedCheckoutTransfer()))
            ->setPaymentProvider(WorldlineConfig::PROVIDER_NAME)
            ->setPaymentMethod('Visa');
    }

    /**
     * @return \Generated\Shared\Transfer\WorldlinePaymentHostedCheckoutTransfer
     */
    protected function createWorldlinePaymentHostedCheckoutTransfer(): WorldlinePaymentHostedCheckoutTransfer
    {
        return (new WorldlinePaymentHostedCheckoutTransfer())
            ->setReturnURL('http://some.url')
            ->setCustomerBrowserLocale('de_DE')
            ->setCustomerJavaEnabled(true)
            ->setCustomerIpAddress('255.255.255.255')
            ->setCustomerColorDepth(24)
            ->setCustomerSelectedLocale('de_DE')
            ->setCustomerUserAgent('Mozilla/5.0 (Linux; Android 12; SM-S906N Build/QP1A.190711.020; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/80.0.3987.119 Mobile Safari/537.36')
            ->setCustomerScreenHeight('1024')
            ->setCustomerScreenWidth('800')
            ->setCustomerTimezone('Berlin')
            ->setCustomerTimezoneOffset(60);
    }

    /**
     * @return \Generated\Shared\Transfer\ProductConcreteTransfer
     */
    private function createProductTransfer(): ProductConcreteTransfer
    {
        return $this->tester->haveProduct();
    }

    /**
     * @param \Generated\Shared\Transfer\CustomerTransfer $customerTransfer
     * @param array $productTransfers
     * @param array $paymentTransferData
     *
     * @return \Generated\Shared\Transfer\QuoteTransfer
     */
    private function createQuoteTransfer(
        CustomerTransfer $customerTransfer,
        array $productTransfers,
        array $paymentTransferData
    ): QuoteTransfer {
        return (new QuoteBuilder())
            ->withItem($productTransfers)
            ->withCustomer([CustomerTransfer::CUSTOMER_REFERENCE => $customerTransfer->getCustomerReference()])
            ->withTotals([TotalsTransfer::GRAND_TOTAL => static::TEST_GRAND_TOTAL])
            ->withShippingAddress()
            ->withBillingAddress()
            ->withCurrency()
            ->withPayment($paymentTransferData)
            ->build();
    }
}
