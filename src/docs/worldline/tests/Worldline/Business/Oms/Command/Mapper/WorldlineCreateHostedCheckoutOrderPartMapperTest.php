<?php

declare(strict_types = 1);

namespace ValanticSprykerTest\Zed\Worldline\Business\Oms\Command\Mapper;

use Generated\Shared\Transfer\AddressTransfer;
use Generated\Shared\Transfer\OrderTransfer;
use Generated\Shared\Transfer\PaymentWorldlineTransfer;
use Generated\Shared\Transfer\SaveOrderTransfer;
use Generated\Shared\Transfer\WorldlineAddressPersonalTransfer;
use Generated\Shared\Transfer\WorldlineCreateHostedCheckoutTransfer;
use Orm\Zed\Sales\Persistence\SpySalesOrderItemQuery;
use Orm\Zed\Sales\Persistence\SpySalesShipmentQuery;
use ValanticSpryker\Zed\Worldline\Business\Oms\Command\Mapper\WorldlineCreateHostedCheckoutOrderPartMapper;
use ValanticSprykerTest\Shared\Base\AbstractTest;
use ValanticSprykerTest\Zed\Worldline\WorldlineBusinessTester;

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
 * @group WorldlineCreateHostedCheckoutOrderPartMapperTest
 * Add your own group annotations below this line
 */
class WorldlineCreateHostedCheckoutOrderPartMapperTest extends AbstractTest
{
    /**
     * @var \ValanticSprykerTest\Zed\Worldline\WorldlineBusinessTester
     */
    protected WorldlineBusinessTester $tester;

    /**
     * @var string
     */
    protected const TEST_USERNAME = 'test username';

    /**
     * @var string
     */
    protected const TEST_PASSWORD = 'change123';

    private WorldlineCreateHostedCheckoutOrderPartMapper $unitUnderTest;

    private PaymentWorldlineTransfer $paymentWorldlineTransfer;

    private OrderTransfer $orderTransfer;

    /**
     * @return void
     */
    public function init(): void
    {
        $customerTransfer = $this->tester->createCustomerTransfer(static::TEST_USERNAME, static::TEST_PASSWORD);

        $quoteTransfer = $this->tester->createQuoteTransferWithTwoProductsHavingShippingAddresses($customerTransfer);
        $saveOrderTransfer = $this->tester->createOrderTransferWithPlugins($quoteTransfer);
        $this->updateItemEntitiesWithShipment($saveOrderTransfer);

        $this->paymentWorldlineTransfer = $this->tester->haveHostedCheckout($saveOrderTransfer, $quoteTransfer);

        $this->orderTransfer = (new OrderTransfer())
            ->setOrderReference($saveOrderTransfer->getOrderReference())
            ->setCustomerReference($customerTransfer->getCustomerReference());
        $this->orderTransfer = $this->tester->getLocator()->sales()->facade()->getCustomerOrderByOrderReference($this->orderTransfer);
    }

    /**
     * Note: we have to link shipment(address) to the items on our own
     * because order-creation does not do this
     * (state machine would do so; but that is hard to set up in tests)
     *
     * @param \Generated\Shared\Transfer\SaveOrderTransfer $saveOrderTransfer
     *
     * @return void
     */
    private function updateItemEntitiesWithShipment(SaveOrderTransfer $saveOrderTransfer): void
    {
        foreach ($saveOrderTransfer->getOrderItems() as $saveOrderItemTransfer) {
            $spySalesShipment = (SpySalesShipmentQuery::create())
                ->filterByIdSalesShipment($saveOrderItemTransfer->getShipment()->getIdSalesShipment())
                ->findOneOrCreate();
            $spySalesShipment
                ->setFkSalesOrder($saveOrderTransfer->getIdSalesOrder())
                ->setFkSalesOrderAddress($saveOrderItemTransfer->getShipment()->getShippingAddress()->getIdSalesOrderAddress())
                ->setCarrierName($saveOrderItemTransfer->getShipment()->getCarrier()->getName());
            $spySalesShipment->save();

            $spySalesOrderItem = (SpySalesOrderItemQuery::create())
                ->filterByIdSalesOrderItem($saveOrderItemTransfer->getIdSalesOrderItem())
                ->findOne();
            $spySalesOrderItem->setFkSalesShipment($spySalesShipment->getIdSalesShipment());
            $spySalesOrderItem->save();
        }
    }

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->init();
        $this->unitUnderTest = new WorldlineCreateHostedCheckoutOrderPartMapper();
    }

    /**
     * @return void
     */
    public function testThatGetWorldlineOrderTransferSetsDeliveryAddressToSameAsBillingIfThereAreNoItemAddresses(): void
    {
        // Prepare
        $worldlineCreateHostedCheckoutTransfer = new WorldlineCreateHostedCheckoutTransfer();
        foreach ($this->orderTransfer->getItems() as $orderItemTransfer) {
            $orderItemTransfer->getShipment()->setShippingAddress(null);
        }

        // execute
        $result = $this->unitUnderTest->map($worldlineCreateHostedCheckoutTransfer, $this->orderTransfer, $this->paymentWorldlineTransfer);

        // verify
        $worldlineShippingTransfer = $result->getOrder()->getShipping();
        self::assertEquals('same-as-billing', $worldlineShippingTransfer->getAddressIndicator());
        $shippingAddress = $result->getOrder()->getShipping()->getAddress();
        self::assertNull($shippingAddress);
    }

    /**
     * @return void
     */
    public function testThatGetWorldlineOrderTransferSetsDeliveryAddressToSameAsBillingIfItemAddressMatches(): void
    {
        // Prepare
        $worldlineCreateHostedCheckoutTransfer = new WorldlineCreateHostedCheckoutTransfer();
        foreach ($this->orderTransfer->getItems() as $orderItemTransfer) {
            $orderItemTransfer->getShipment()->setShippingAddress($this->orderTransfer->getBillingAddress());
        }

        // execute
        $result = $this->unitUnderTest->map($worldlineCreateHostedCheckoutTransfer, $this->orderTransfer, $this->paymentWorldlineTransfer);

        // verify
        $worldlineShippingTransfer = $result->getOrder()->getShipping();
        self::assertEquals('same-as-billing', $worldlineShippingTransfer->getAddressIndicator());
        $worldlineShippingAddress = $result->getOrder()->getShipping()->getAddress();
        self::assertNull($worldlineShippingAddress);
    }

    /**
     * @return void
     */
    public function testThatGetWorldlineOrderTransferSetsDeliveryAddressSetsToFirstItemsShippingAddress(): void
    {
        // Prepare
        $worldlineCreateHostedCheckoutTransfer = new WorldlineCreateHostedCheckoutTransfer();
        $orderItemsWithShippingAddress = $this->orderTransfer->getItems();

        // execute
        $result = $this->unitUnderTest->map($worldlineCreateHostedCheckoutTransfer, $this->orderTransfer, $this->paymentWorldlineTransfer);

        // verify
        $worldlineShippingTransfer = $result->getOrder()->getShipping();
        self::assertEquals('different-than-billing', $worldlineShippingTransfer->getAddressIndicator());
        $worldlineShippingAddress = $result->getOrder()->getShipping()->getAddress();
        $shippingAddressOfFirstOrderItem = $orderItemsWithShippingAddress[0]->getShipment()->getShippingAddress();
        $this->assertThatWorldlineOrderMatches($worldlineShippingAddress, $shippingAddressOfFirstOrderItem);
    }

    /**
     * @return void
     */
    public function testThatGetWorldlineOrderTransferSetsDeliveryAddressTraversesItemsIfFirstItemsIfFirstItemHasNoShippingAddress(): void
    {
        // Prepare
        $worldlineCreateHostedCheckoutTransfer = new WorldlineCreateHostedCheckoutTransfer();
        ($this->orderTransfer->getItems()[0])->getShipment()->setShippingAddress(null);

        // execute
        $result = $this->unitUnderTest->map($worldlineCreateHostedCheckoutTransfer, $this->orderTransfer, $this->paymentWorldlineTransfer);

        // verify
        $worldlineShippingTransfer = $result->getOrder()->getShipping();
        self::assertEquals('different-than-billing', $worldlineShippingTransfer->getAddressIndicator());
        $worldlineShippingAddress = $result->getOrder()->getShipping()->getAddress();
        $shippingAddressOfSecondOrderItem = $this->orderTransfer->getItems()[1]->getShipment()->getShippingAddress();
        $this->assertThatWorldlineOrderMatches($worldlineShippingAddress, $shippingAddressOfSecondOrderItem);
    }

    /**
     * @param \Generated\Shared\Transfer\WorldlineAddressPersonalTransfer $worldlineShippingAddress
     * @param \Generated\Shared\Transfer\AddressTransfer|null $orderItemShippingAddress
     *
     * @return void
     */
    private function assertThatWorldlineOrderMatches(WorldlineAddressPersonalTransfer $worldlineShippingAddress, ?AddressTransfer $orderItemShippingAddress): void
    {
        self::assertNotNull($worldlineShippingAddress);
        self::assertEquals($orderItemShippingAddress->getFirstName(), $worldlineShippingAddress->getName()->getFirstname());
        self::assertEquals($orderItemShippingAddress->getLastName(), $worldlineShippingAddress->getName()->getSurname());
        self::assertEquals($orderItemShippingAddress->getCity(), $worldlineShippingAddress->getCity());
        self::assertEquals($orderItemShippingAddress->getAddress1(), $worldlineShippingAddress->getStreet());
        self::assertEquals($orderItemShippingAddress->getAddress2(), $worldlineShippingAddress->getHouseNumber());
        self::assertEquals($orderItemShippingAddress->getZipCode(), $worldlineShippingAddress->getZip());
        self::assertEquals($orderItemShippingAddress->getIso2Code(), $worldlineShippingAddress->getCountryCode());
    }
}
