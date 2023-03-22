<?php

declare(strict_types = 1);

namespace PyzTest\Glue\PaymentTokensRestApi\Processor;

use Generated\Shared\Transfer\WorldlineDeleteTokenRequestTransfer;
use Generated\Shared\Transfer\WorldlineDeleteTokenResponseTransfer;
use Generated\Shared\Transfer\WorldlineErrorItemTransfer;
use Pyz\Client\PaymentTokens\PaymentTokensClientInterface;
use Pyz\Glue\PaymentTokensRestApi\Processor\Mapper\RestPaymentTokensMapper;
use Pyz\Glue\PaymentTokensRestApi\Processor\PaymentTokensDeleter;
use Pyz\Glue\PaymentTokensRestApi\Processor\ResponseBuilder\PaymentTokensRestResponseBuilder;
use Pyz\Glue\PaymentTokensRestApi\Validation\RestApiError;
use PyzTest\Glue\PaymentTokensRestApi\PaymentTokensRestApiTester;
use PyzTest\Shared\Base\AbstractTest;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceBuilder;

/**
 * Auto-generated group annotations
 *
 * @group PyzTest
 * @group Glue
 * @group PaymentTokensRestApi
 * @group Processor
 * @group PaymentTokensDeleterTest
 * Add your own group annotations below this line
 */
class PaymentTokensDeleterTest extends AbstractTest
{
    /**
     * @var \PyzTest\Glue\PaymentTokensRestApi\PaymentTokensRestApiTester
     */
    protected PaymentTokensRestApiTester $tester;

    public function testDeletePaymentTokenByIdReturnsEmptyResponseWhenSuccessful()
    {
        // Arrange
        $paymentTokensClientMock = $this->getMockBuilder(PaymentTokensClientInterface::class)->getMock();
        $paymentTokensClientMock->method('deletePaymentTokenById')->willReturn(
            (new WorldlineDeleteTokenResponseTransfer())
                ->setIsSuccess(true)
                ->setHttpStatusCode(200),
        );

        $deleteRequest = (new WorldlineDeleteTokenRequestTransfer())->setIdToken('someTokenId');

        // Act
        $restResponse = (new PaymentTokensDeleter(
            $paymentTokensClientMock,
            new PaymentTokensRestResponseBuilder(
                new RestResourceBuilder(),
                new RestApiError(),
                new RestPaymentTokensMapper(),
            ),
        ))->deletePaymentTokenById($deleteRequest);

        // Assert
        self::assertSame(204, $restResponse->getStatus());
        self::assertSame(0, count($restResponse->getResources()));

    }

    public function testDeletePaymentTokenByIdReturnsErrorResponseWhenTokenWasNotFound()
    {
        // Arrange
        $paymentTokensClientMock = $this->getMockBuilder(PaymentTokensClientInterface::class)->getMock();
        $paymentTokensClientMock->method('deletePaymentTokenById')->willReturn(
            (new WorldlineDeleteTokenResponseTransfer())
                ->setIsSuccess(false)
                ->setHttpStatusCode(404)
                ->addError(
                    (new WorldlineErrorItemTransfer())
                        ->setCode('404')
                        ->setMessage('Token not found.'),
                ),
        );

        $deleteRequest = (new WorldlineDeleteTokenRequestTransfer())->setIdToken('someTokenId');

        // Act
        $restResponse = (new PaymentTokensDeleter($paymentTokensClientMock,
            new PaymentTokensRestResponseBuilder(
                new RestResourceBuilder(),
                new RestApiError(),
                new RestPaymentTokensMapper(),
            ),
        ))->deletePaymentTokenById($deleteRequest);

        // Assert
        self::assertSame(404, $restResponse->getStatus());
        self::assertSame(1, count($restResponse->getErrors()));
        self::assertSame('404', $restResponse->getErrors()[0]->getCode());
    }
}
