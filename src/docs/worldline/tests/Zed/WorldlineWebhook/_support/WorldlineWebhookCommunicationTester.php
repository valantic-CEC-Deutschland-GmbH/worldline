<?php

declare(strict_types=1);

namespace ValanticSprykerTest\Zed\WorldlineWebhook;

use Codeception\Actor;
use Generated\Shared\DataBuilder\QuoteBuilder;
use Generated\Shared\Transfer\CustomerTransfer;
use Generated\Shared\Transfer\PaymentTransfer;
use Generated\Shared\Transfer\PaymentWorldlineTransfer;
use Generated\Shared\Transfer\ProductConcreteTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\SaveOrderTransfer;
use Generated\Shared\Transfer\TotalsTransfer;
use Generated\Shared\Transfer\WorldlinePaymentHostedCheckoutTransfer;
use ValanticSpryker\Zed\Worldline\WorldlineConfig;

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
 * @method void pause($vars = [])
 * @SuppressWarnings(PHPMD)
*/
class WorldlineWebhookCommunicationTester extends Actor
{
    use _generated\WorldlineWebhookCommunicationTesterActions;

    /**
     * Define custom actions here
     */
    public const STATE_MACHINE_PROCESS_NAME = 'LRDelayedPayment01';

    public const TEST_GRAND_TOTAL = 1111;

    /**
     * @return \Generated\Shared\Transfer\SaveOrderTransfer
     */
    public function createOrderTransfer(QuoteTransfer $quote): SaveOrderTransfer
    {
        return $this->haveOrderFromQuote($quote, $this->createStateMachine(static::STATE_MACHINE_PROCESS_NAME));
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
     * @param array $productTransfers
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
}
