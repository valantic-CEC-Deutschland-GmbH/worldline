<?php
namespace ValanticSprykerTest\Zed\Worldline;

use Codeception\Actor;
use Generated\Shared\DataBuilder\ItemBuilder;
use Generated\Shared\DataBuilder\QuoteBuilder;
use Generated\Shared\DataBuilder\TotalsBuilder;
use Generated\Shared\Transfer\AddressTransfer;
use Generated\Shared\Transfer\CurrencyTransfer;
use Generated\Shared\Transfer\CustomerTransfer;
use Generated\Shared\Transfer\ExpenseTransfer;
use Generated\Shared\Transfer\ItemTransfer;
use Generated\Shared\Transfer\PaymentTransfer;
use Generated\Shared\Transfer\PaymentWorldlineTransfer;
use Generated\Shared\Transfer\PriceProductTransfer;
use Generated\Shared\Transfer\ProductConcreteTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\SaveOrderTransfer;
use Generated\Shared\Transfer\ShipmentTransfer;
use Generated\Shared\Transfer\TaxTotalTransfer;
use Generated\Shared\Transfer\TotalsTransfer;
use Generated\Shared\Transfer\WorldlinePaymentHostedCheckoutTransfer;
use ValanticSpryker\Shared\Shipment\ShipmentConfig;
use ValanticSpryker\Zed\Worldline\Communication\Plugin\Checkout\WorldlineCheckoutDoSaveOrderPlugin;
use ValanticSpryker\Zed\Worldline\WorldlineConfig;
use Spryker\Zed\ProductBundle\Communication\Plugin\Checkout\ProductBundleOrderSaverPlugin;
use Spryker\Zed\Sales\Communication\Plugin\Checkout\OrderTotalsSaverPlugin;
use Spryker\Zed\SalesPayment\Communication\Plugin\Checkout\SalesPaymentCheckoutDoSaveOrderPlugin;
use Spryker\Zed\Shipment\Communication\Plugin\Checkout\OrderShipmentSavePlugin;
use Spryker\Zed\Shipment\Communication\Plugin\Checkout\SalesOrderShipmentSavePlugin;


/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause()
 * @SuppressWarnings(PHPMD)
*/
class WorldlineBusinessTester extends Actor
{
    use _generated\WorldlineBusinessTesterActions;

    /**
     * Define custom actions here
     */
    public const STATE_MACHINE_PROCESS_NAME = 'LRDelayedPayment01';

    public const TEST_GRAND_TOTAL = 1000;



    /**
     * @return \Generated\Shared\Transfer\SaveOrderTransfer
     */
    public function createOrderTransfer(QuoteTransfer $quote): SaveOrderTransfer
    {
        return $this->haveOrderFromQuote($quote, $this->createStateMachine(static::STATE_MACHINE_PROCESS_NAME));
    }

    /**
     * @return \Generated\Shared\Transfer\SaveOrderTransfer
     */
    public function createOrderTransferWithPlugins(QuoteTransfer $quote): SaveOrderTransfer
    {
        return $this->haveOrderFromQuote($quote, $this->createStateMachine(static::STATE_MACHINE_PROCESS_NAME),
            [
                new OrderTotalsSaverPlugin(),
                new SalesOrderShipmentSavePlugin(),
                new ProductBundleOrderSaverPlugin(),
                new SalesPaymentCheckoutDoSaveOrderPlugin(),
                new WorldlineCheckoutDoSaveOrderPlugin(),
            ]);

    }

    /**
     * @return string
     */
    public function createStateMachine(string $testStateMachineProcessName): string
    {
        $this->configureTestStateMachine([$testStateMachineProcessName], '/data/config/Zed/oms/');

        return $testStateMachineProcessName;
    }

    /**
     * @param string $name
     * @param string $password
     *
     * @return \Generated\Shared\Transfer\CustomerTransfer
     */
    public function createCustomerTransfer(string $name, string $password): CustomerTransfer
    {
        $customerTransfer = $this->haveCustomer([
            CustomerTransfer::USERNAME => $name,
            CustomerTransfer::PASSWORD => $password,
            CustomerTransfer::NEW_PASSWORD => $password,
        ]);

        return $this->confirmCustomer($customerTransfer);
    }

    /**
     * @return \Generated\Shared\Transfer\PaymentTransfer
     */
    public function createPaymentTransfer(): PaymentTransfer
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
    private function createWorldlinePaymentHostedCheckoutTransfer(): WorldlinePaymentHostedCheckoutTransfer
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

    public function createProductTransfer(): ProductConcreteTransfer
    {
        return $this->haveProduct();
    }

    /**
     * @param \Generated\Shared\Transfer\CustomerTransfer $customerTransfer
     * @param array $paymentTransferData
     *
     * @return \Generated\Shared\Transfer\QuoteTransfer
     */
    public function createQuoteTransfer(
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

    /**
     * @param array $seed
     *
     * @return \Generated\Shared\Transfer\QuoteTransfer
     */
    private function createQuoteTransferInternal(array $seed = []): QuoteTransfer
    {
        /** @var \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer */
        $quoteTransfer = (new QuoteBuilder($seed))
            ->withItem()
            ->withTotals()
            ->withShippingAddress()
            ->withBillingAddress([
                AddressTransfer::COUNTRY => 'Germany',
            ])
            ->withPayment()
            ->withAnotherPayment()
            ->withExpense([
                ExpenseTransfer::TYPE => ShipmentConfig::SHIPMENT_EXPENSE_TYPE,
            ])
            ->build();

        return $quoteTransfer;
    }

    /**
     * @return \Generated\Shared\Transfer\QuoteTransfer
     */
    public function createQuoteTransferWithTwoProductsHavingShippingAddresses(CustomerTransfer $customerTransfer): QuoteTransfer
    {

        $productTransfer1 = $this->haveProduct();
        $productTransfer2 = $this->haveProduct();

        $payment = $this->createPaymentTransfer();
        $shipmentMethod = $this->haveShipmentMethod();
        $shipmentCarrier = $this->haveShipmentCarrier();

        $shippingAddress1 = $customerTransfer->getBillingAddress()[0];
        $shippingAddress2 = $this->haveCustomerAddress($customerTransfer);

        $quoteTransfer = $this->createQuoteTransferInternal([
            QuoteTransfer::CURRENCY => [CurrencyTransfer::CODE => 'EUR'],
            QuoteTransfer::CUSTOMER => $customerTransfer,
            QuoteTransfer::TOTALS => (new TotalsBuilder())->build(),
            QuoteTransfer::PAYMENTS => [
                [
                    PaymentTransfer::PAYMENT_SELECTION => 'worldlineCreditCardVisa',
                    PaymentTransfer::PAYMENT_PROVIDER => $payment->getPaymentProvider(),
                    PaymentTransfer::PAYMENT_METHOD => $payment->getPaymentMethod(),
                    PaymentTransfer::AMOUNT => 1000,
                    PaymentTransfer::PAYMENT_WORLDLINE => $this->createWordLinePaymentTransferArray(),
                ],
            ],
            QuoteTransfer::PAYMENT => [
                PaymentTransfer::PAYMENT_SELECTION => 'worldlineCreditCardVisa',
                PaymentTransfer::PAYMENT_PROVIDER => $payment->getPaymentProvider(),
                PaymentTransfer::PAYMENT_METHOD => $payment->getPaymentMethod(),
                PaymentTransfer::AMOUNT => 1000,
                PaymentTransfer::PAYMENT_WORLDLINE => $this->createWordLinePaymentTransferArray(),
            ],
            QuoteTransfer::ITEMS => [
                (new ItemBuilder([
                    ItemTransfer::SKU => $productTransfer1->getSku(),
                    ItemTransfer::SHIPMENT =>[
                        ShipmentTransfer::SHIPPING_ADDRESS => $shippingAddress1->toArray(),
                        ShipmentTransfer::CARRIER => $shipmentCarrier->toArray(),
                        ShipmentTransfer::METHOD => $shipmentMethod->toArray(),
                    ]
                ]))->build()->toArray(),
                (new ItemBuilder([
                    ItemTransfer::SKU => $productTransfer2->getSku(),
                    ItemTransfer::SHIPMENT =>[
                        ShipmentTransfer::SHIPPING_ADDRESS => $shippingAddress2->toArray(),
                        ShipmentTransfer::CARRIER => $shipmentCarrier->toArray(),
                        ShipmentTransfer::METHOD => $shipmentMethod->toArray(),
                    ]
                ]))->build()->toArray(),
            ],

        ]);

        //295
        return $quoteTransfer;
    }

    /**
     * @return array
     */
    protected function createWordLinePaymentTransferArray(): array
    {
        return [
            PaymentWorldlineTransfer::PAYMENT_METHOD => 'worldlineCreditCardVisa',
            PaymentWorldlineTransfer::PAYMENT_HOSTED_CHECKOUT => [
                WorldlinePaymentHostedCheckoutTransfer::RETURN_URL => 'http://some.url',
                WorldlinePaymentHostedCheckoutTransfer::CUSTOMER_IP_ADDRESS => '127.0.0.1',
                WorldlinePaymentHostedCheckoutTransfer::CUSTOMER_SELECTED_LOCALE => 'de_DE',
                WorldlinePaymentHostedCheckoutTransfer::CUSTOMER_TIMEZONE => 'CEST',
                WorldlinePaymentHostedCheckoutTransfer::CUSTOMER_USER_AGENT => 'Mozilla/5.0 (Linux; Android 12; SM-S906N Build/QP1A.190711.020; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/80.0.3987.119 Mobile Safari/537.36',
                WorldlinePaymentHostedCheckoutTransfer::CUSTOMER_COLOR_DEPTH => 24,
                WorldlinePaymentHostedCheckoutTransfer::CUSTOMER_JAVA_ENABLED => true,
                WorldlinePaymentHostedCheckoutTransfer::CUSTOMER_SCREEN_HEIGHT => '1024',
                WorldlinePaymentHostedCheckoutTransfer::CUSTOMER_SCREEN_WIDTH => '800',
                WorldlinePaymentHostedCheckoutTransfer::CUSTOMER_BROWSER_LOCALE => 'de_DE',
                WorldlinePaymentHostedCheckoutTransfer::CUSTOMER_TIMEZONE_OFFSET => 60,
            ],
        ];
    }

}
