<?php

declare(strict_types = 1);

namespace ValanticSprykerTest\Zed\Worldline\Business\Writer;

use Generated\Shared\Transfer\OrderTransfer;
use Generated\Shared\Transfer\WorldlineCreateHostedCheckoutResponseTransfer;
use Orm\Zed\Worldline\Persistence\VsyWorldlineTokenQuery;
use ValanticSpryker\Zed\Worldline\Business\Timestamp\WorldlineTimestampConverter;
use ValanticSpryker\Zed\Worldline\Business\Writer\WorldlineWriter;
use ValanticSpryker\Zed\Worldline\Persistence\WorldlineEntityManager;
use ValanticSpryker\Zed\Worldline\Persistence\WorldlineQueryContainer;
use ValanticSpryker\Zed\Worldline\WorldlineConfig;
use ValanticSprykerTest\Shared\Base\AbstractTest;
use ValanticSprykerTest\Zed\Worldline\WorldlineBusinessTester;
use Spryker\Zed\Sales\Business\SalesFacade;

/**
 * Auto-generated group annotations
 *
 * @group ValanticSprykerTest
 * @group Zed
 * @group Worldline
 * @group Business
 * @group Writer
 * @group WorldlineWriterTest
 * Add your own group annotations below this line
 * @group Worldline
 */
class WorldlineWriterTest extends AbstractTest
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
     * @var \ValanticSprykerTest\Zed\Worldline\WorldlineBusinessTester
     */
    protected WorldlineBusinessTester $tester;

    public function testSaveCreatedHostedCheckoutResponseMarksTokensAsDeletedWhenTheyArePassedInTheInvalidTokensArray()
    {
        // Array
        $customerTransfer = $this->tester->createCustomerTransfer(static::TEST_USERNAME, static::TEST_PASSWORD);

        $quote = $this->tester->createQuoteTransfer($customerTransfer, [$this->tester->createProductTransfer()], $this->tester->createPaymentTransfer()->toArray());
        $saveOrderTransfer = $this->tester->createOrderTransfer($quote);

        $orderReference = $saveOrderTransfer->getOrderReference();

        $tokenTransfer1 = $this->tester->havePaymentTokenWithThreeDSecureDataForCustomer($customerTransfer);
        $tokenTransfer2 = $this->tester->havePaymentTokenWithThreeDSecureDataForCustomer($customerTransfer);

        $createHostedCheckoutResponse = new WorldlineCreateHostedCheckoutResponseTransfer();
        $createHostedCheckoutResponse->setHostedCheckoutId('someId');
        $createHostedCheckoutResponse->setRETURNMAC('uuid');
        $createHostedCheckoutResponse->setInvalidTokens([$tokenTransfer1->getToken()]);
        $createHostedCheckoutResponse->setMerchantReference($orderReference);
        $createHostedCheckoutResponse->setIsSuccess(true);

        $orderTransfer = new OrderTransfer();
        $orderTransfer->setOrderReference($orderReference);
        $orderTransfer->setCustomerReference($customerTransfer->getCustomerReference());
        $orderTransfer = (new SalesFacade())->getCustomerOrderByOrderReference($orderTransfer);

        $worldlineConfig = new WorldlineConfig();

        // Act
        (new WorldlineWriter(
            new WorldlineEntityManager(),
            new WorldlineQueryContainer(),
            new WorldlineTimestampConverter($worldlineConfig),
            new WorldlineConfig(),
        ))->saveCreatedHostedCheckoutResponse($createHostedCheckoutResponse, $orderTransfer);

        // Assert
        $tokenEntity = VsyWorldlineTokenQuery::create()->filterByToken($tokenTransfer1->getToken())->filterByFkCustomer($customerTransfer->getIdCustomer())->findOne();
        self::assertNotNull($tokenEntity->getDeletedAt());

        $tokenEntity = VsyWorldlineTokenQuery::create()->filterByToken($tokenTransfer2->getToken())->filterByFkCustomer($customerTransfer->getIdCustomer())->findOne();
        self::assertNull($tokenEntity->getDeletedAt());
    }

    public function testSaveCreatedHostedCheckoutResponseMarksNoTokensAsDeletedWhenOnlyUnknownTokensArePassedInTheInvalidTokensArray()
    {
        // Array
        $customerTransfer = $this->tester->createCustomerTransfer(static::TEST_USERNAME, static::TEST_PASSWORD);

        $quote = $this->tester->createQuoteTransfer($customerTransfer, [$this->tester->createProductTransfer()], $this->tester->createPaymentTransfer()->toArray());
        $saveOrderTransfer = $this->tester->createOrderTransfer($quote);

        $orderReference = $saveOrderTransfer->getOrderReference();

        $tokenTransfer1 = $this->tester->havePaymentTokenWithThreeDSecureDataForCustomer($customerTransfer);
        $tokenTransfer2 = $this->tester->havePaymentTokenWithThreeDSecureDataForCustomer($customerTransfer);

        $createHostedCheckoutResponse = new WorldlineCreateHostedCheckoutResponseTransfer();
        $createHostedCheckoutResponse->setHostedCheckoutId('someId');
        $createHostedCheckoutResponse->setRETURNMAC('uuid');
        $createHostedCheckoutResponse->setInvalidTokens(['unknown']);
        $createHostedCheckoutResponse->setMerchantReference($orderReference);
        $createHostedCheckoutResponse->setIsSuccess(true);

        $orderTransfer = new OrderTransfer();
        $orderTransfer->setOrderReference($orderReference);
        $orderTransfer->setCustomerReference($customerTransfer->getCustomerReference());
        $orderTransfer = (new SalesFacade())->getCustomerOrderByOrderReference($orderTransfer);

        $worldlineConfig = new WorldlineConfig();

        // Act
        (new WorldlineWriter(
            new WorldlineEntityManager(),
            new WorldlineQueryContainer(),
            new WorldlineTimestampConverter($worldlineConfig),
            new WorldlineConfig(),
        ))->saveCreatedHostedCheckoutResponse($createHostedCheckoutResponse, $orderTransfer);

        // Assert
        $tokenEntity = VsyWorldlineTokenQuery::create()->filterByToken($tokenTransfer1->getToken())->filterByFkCustomer($customerTransfer->getIdCustomer())->findOne();
        self::assertNull($tokenEntity->getDeletedAt());

        $tokenEntity = VsyWorldlineTokenQuery::create()->filterByToken($tokenTransfer2->getToken())->filterByFkCustomer($customerTransfer->getIdCustomer())->findOne();
        self::assertNull($tokenEntity->getDeletedAt());
    }

    public function testSaveCreatedHostedCheckoutResponseMarksMultipleTokensAsDeletedWhenTheyArePassedInTheInvalidTokensArray()
    {
        // Array
        $customerTransfer = $this->tester->createCustomerTransfer(static::TEST_USERNAME, static::TEST_PASSWORD);

        $quote = $this->tester->createQuoteTransfer($customerTransfer, [$this->tester->createProductTransfer()], $this->tester->createPaymentTransfer()->toArray());
        $saveOrderTransfer = $this->tester->createOrderTransfer($quote);

        $orderReference = $saveOrderTransfer->getOrderReference();

        $tokenTransfer1 = $this->tester->havePaymentTokenWithThreeDSecureDataForCustomer($customerTransfer);
        $tokenTransfer2 = $this->tester->havePaymentTokenWithThreeDSecureDataForCustomer($customerTransfer);
        $tokenTransfer3 = $this->tester->havePaymentTokenWithThreeDSecureDataForCustomer($customerTransfer);

        $createHostedCheckoutResponse = new WorldlineCreateHostedCheckoutResponseTransfer();
        $createHostedCheckoutResponse->setHostedCheckoutId('someId');
        $createHostedCheckoutResponse->setRETURNMAC('uuid');
        $createHostedCheckoutResponse->setInvalidTokens([$tokenTransfer1->getToken(), $tokenTransfer3->getToken()]);
        $createHostedCheckoutResponse->setMerchantReference($orderReference);
        $createHostedCheckoutResponse->setIsSuccess(true);

        $orderTransfer = new OrderTransfer();
        $orderTransfer->setOrderReference($orderReference);
        $orderTransfer->setCustomerReference($customerTransfer->getCustomerReference());
        $orderTransfer = (new SalesFacade())->getCustomerOrderByOrderReference($orderTransfer);

        $worldlineConfig = new WorldlineConfig();

        // Act
        (new WorldlineWriter(
            new WorldlineEntityManager(),
            new WorldlineQueryContainer(),
            new WorldlineTimestampConverter($worldlineConfig),
            new WorldlineConfig(),
        ))->saveCreatedHostedCheckoutResponse($createHostedCheckoutResponse, $orderTransfer);

        // Assert
        $tokenEntity = VsyWorldlineTokenQuery::create()->filterByToken($tokenTransfer1->getToken())->filterByFkCustomer($customerTransfer->getIdCustomer())->findOne();
        self::assertNotNull($tokenEntity->getDeletedAt());

        $tokenEntity = VsyWorldlineTokenQuery::create()->filterByToken($tokenTransfer2->getToken())->filterByFkCustomer($customerTransfer->getIdCustomer())->findOne();
        self::assertNull($tokenEntity->getDeletedAt());

        $tokenEntity = VsyWorldlineTokenQuery::create()->filterByToken($tokenTransfer3->getToken())->filterByFkCustomer($customerTransfer->getIdCustomer())->findOne();
        self::assertNotNull($tokenEntity->getDeletedAt());
    }
}
