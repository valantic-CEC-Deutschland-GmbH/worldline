<?php

declare(strict_types = 1);

namespace ValanticSprykerTest\Zed\Worldline\Business\Oms\Command\Mapper;

use ArrayObject;
use Generated\Shared\Transfer\CustomerTransfer;
use Generated\Shared\Transfer\OrderTransfer;
use Generated\Shared\Transfer\PaymentTransfer;
use Generated\Shared\Transfer\PaymentWorldlineTransfer;
use Generated\Shared\Transfer\WorldlineCreateHostedCheckoutTransfer;
use Generated\Shared\Transfer\WorldlinePaymentHostedCheckoutTransfer;
use Spryker\Shared\Kernel\Store;
use ValanticSpryker\Zed\Worldline\Business\Oms\Command\Mapper\WorldlineCreateHostedCheckoutHostedCheckoutSpecificInputPartMapper;
use ValanticSpryker\Zed\Worldline\Persistence\WorldlineQueryContainerInterface;
use ValanticSpryker\Zed\Worldline\WorldlineConfig;
use ValanticSprykerTest\Shared\Base\AbstractTest;

/**
 * Auto-generated group annotations
 *
 * @group ValanticSprykerTest
 * @group Zed
 * @group Worldline
 * @group Business
 * @group Oms
 * @group Command
 * @group Mapper
 * @group WorldlineCreateHostedCheckoutHostedCheckoutSpecificInputPartMapperTest
 * Add your own group annotations below this line
 */
class WorldlineCreateHostedCheckoutHostedCheckoutSpecificInputPartMapperTest extends AbstractTest
{
    public function testMapReturnsWorldlineCreateHostedCheckoutTransferWithHostSpecificInputContainingTheCorrectTokens()
    {
        // Arrange
        $expectedTokensString = 'token1,token2,token3';
        $worldlindQueryMock = $this->getMockBuilder(WorldlineQueryContainerInterface::class)->disableOriginalConstructor()->getMock();
        $worldlindQueryMock->method('getExistingTokens')->willReturn($expectedTokensString);
        $unitUnderTest = new WorldlineCreateHostedCheckoutHostedCheckoutSpecificInputPartMapper(new WorldlineConfig(), $worldlindQueryMock);

        $paymentWorldlineTransfer = new PaymentWorldlineTransfer();
        $paymentWorldlineTransfer->setPaymentHostedCheckout((new WorldlinePaymentHostedCheckoutTransfer())->setReturnUrl('http://some.url'));

        Store::getInstance()->setCurrentLocale('de_DE');

        $orderTransfer = (new OrderTransfer())
            ->setCustomer(
                (new CustomerTransfer())->setIdCustomer(666),
            )
            ->setPayments(new ArrayObject([
                (new PaymentTransfer())
                    ->setPaymentMethod('Visa')
                    ->setPaymentProvider('Worldline'),
            ]));

        $createHostedCheckoutRequestTransfer = new WorldlineCreateHostedCheckoutTransfer();

        // Act
        $unitUnderTest->map($createHostedCheckoutRequestTransfer, $orderTransfer, $paymentWorldlineTransfer);

        self::assertSame($expectedTokensString, $createHostedCheckoutRequestTransfer->getHostedCheckoutSpecificInput()->getTokens());
    }

    public function testMapReturnsWorldlineCreateHostedCheckoutTransferWithHostSpecificInputContainingEmptyTokens()
    {
        // Arrange
        $expectedTokensString = '';
        $worldlindQueryMock = $this->getMockBuilder(WorldlineQueryContainerInterface::class)->disableOriginalConstructor()->getMock();
        $worldlindQueryMock->method('getExistingTokens')->willReturn($expectedTokensString);
        $unitUnderTest = new WorldlineCreateHostedCheckoutHostedCheckoutSpecificInputPartMapper(new WorldlineConfig(), $worldlindQueryMock);

        $paymentWorldlineTransfer = new PaymentWorldlineTransfer();
        $paymentWorldlineTransfer->setPaymentHostedCheckout((new WorldlinePaymentHostedCheckoutTransfer())->setReturnUrl('http://some.url'));

        Store::getInstance()->setCurrentLocale('de_DE');

        $orderTransfer = (new OrderTransfer())
            ->setCustomer(
                (new CustomerTransfer())->setIdCustomer(666),
            )
            ->setPayments(new ArrayObject([
                (new PaymentTransfer())
                    ->setPaymentMethod('Visa')
                    ->setPaymentProvider('Worldline'),
            ]));

        $createHostedCheckoutRequestTransfer = new WorldlineCreateHostedCheckoutTransfer();

        // Act
        $unitUnderTest->map($createHostedCheckoutRequestTransfer, $orderTransfer, $paymentWorldlineTransfer);

        self::assertSame($expectedTokensString, $createHostedCheckoutRequestTransfer->getHostedCheckoutSpecificInput()->getTokens());
    }
}
