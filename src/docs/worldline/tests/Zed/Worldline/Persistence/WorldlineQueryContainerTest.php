<?php

declare(strict_types = 1);

namespace ValanticSprykerTest\Zed\Worldline\Persistence;

use ValanticSpryker\Zed\Worldline\Persistence\WorldlineQueryContainer;
use ValanticSprykerTest\Shared\Base\AbstractTest;
use ValanticSprykerTest\Zed\Worldline\WorldlinePersistenceTester;

/**
 * Auto-generated group annotations
 *
 * @group ValanticSprykerTest
 * @group Zed
 * @group Worldline
 * @group Persistence
 * @group WorldlineQueryContainerTest
 * Add your own group annotations below this line
 * @group Worldline
 */
class WorldlineQueryContainerTest extends AbstractTest
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
     * @var \ValanticSprykerTest\Zed\Worldline\WorldlinePersistenceTester
     */
    protected WorldlinePersistenceTester $tester;

    public function testGetExistingTokensReturnsCorrectStringWhen2TokensAreSavedForTheRespectiveCustomer()
    {
        // Arrange
        $customerWithOrders = $this->tester->createCustomerTransfer(static::TEST_USERNAME, static::TEST_PASSWORD);

        $token1Transfer = $this->tester->havePaymentTokenWithThreeDSecureDataForCustomer($customerWithOrders);
        $token2Transfer = $this->tester->havePaymentTokenWithThreeDSecureDataForCustomer($customerWithOrders);

        // Act
        $actualTokensString = (new WorldlineQueryContainer())->getExistingTokens($customerWithOrders->getIdCustomer());

        // Assert
        self::assertSame($token1Transfer->getToken() . ',' . $token2Transfer->getToken(), $actualTokensString);
    }

    public function testGetExistingTokensReturnsEmptyStringWhenNoTokensAreSavedForTheRespectiveCustomer()
    {
        // Arrange
        $customerWithOrders = $this->tester->createCustomerTransfer(static::TEST_USERNAME, static::TEST_PASSWORD);

        // Act
        $actualTokensString = (new WorldlineQueryContainer())->getExistingTokens($customerWithOrders->getIdCustomer());

        // Assert
        self::assertSame('', $actualTokensString);
    }
}
