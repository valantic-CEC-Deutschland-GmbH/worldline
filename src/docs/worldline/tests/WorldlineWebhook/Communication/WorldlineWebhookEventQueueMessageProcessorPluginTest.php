<?php

declare(strict_types = 1);

namespace ValanticSprykerTest\Zed\WorldlineWebhook\Communication;

use DateTime;
use Faker\Provider\Uuid;
use Generated\Shared\Transfer\PaymentWorldlineTransactionStatusTransfer;
use Generated\Shared\Transfer\PaymentWorldlineTransfer;
use Generated\Shared\Transfer\QueueReceiveMessageTransfer;
use Generated\Shared\Transfer\QueueSendMessageTransfer;
use Generated\Shared\Transfer\WorldlinePaymentHostedCheckoutTransfer;
use Orm\Zed\Worldline\Persistence\VsyPaymentWorldlineTransactionStatusLog;
use Orm\Zed\Worldline\Persistence\VsyPaymentWorldlineTransactionStatusLogQuery;
use Orm\Zed\Worldline\Persistence\VsyWorldlineRestLogQuery;
use Orm\Zed\Worldline\Persistence\VsyWorldlineRestReceiveLogQuery;
use ValanticSpryker\Shared\Worldline\WorldlineConstants;
use ValanticSpryker\Shared\WorldlineWebhook\WorldlineWebhookConstants;
use ValanticSpryker\Zed\WorldlineWebhook\Communication\Plugin\Queue\WorldlineWebhookEventQueueMessageProcessorPlugin;
use ValanticSpryker\Zed\WorldlineWebhook\WorldlineWebhookConfig;
use ValanticSprykerTest\Shared\Base\AbstractTest;
use ValanticSprykerTest\Zed\WorldlineWebhook\WorldlineWebhookCommunicationTester;

/**
 * Auto-generated group annotations
 *
 * @group ValanticSprykerTest
 * @group Zed
 * @group WorldlineWebhook
 * @group Communication
 * @group WorldlineWebhookEventQueueMessageProcessorPluginTest
 * Add your own group annotations below this line
 * @group Worldline
 */
class WorldlineWebhookEventQueueMessageProcessorPluginTest extends AbstractTest
{
    private const HOSTED_CHECKOUT_ID = '15c09dac-bf44-486a-af6b-edfd8680a166';

    /**
     * @var string
     */
    protected const TEST_USERNAME = 'test username';

    /**
     * @var string
     */
    protected const TEST_PASSWORD = 'change123';

    const EVENT_ID = 'new eventId';

    protected WorldlineWebhookCommunicationTester $tester;

    public function testProcessMessagesHandlesEventMessagesInWebhookQueueCorrectly()
    {
        // Arrange
        $customerWithOrders = $this->tester->createCustomerTransfer(static::TEST_USERNAME, static::TEST_PASSWORD);
        $quote = $this->tester->createQuoteTransfer($customerWithOrders, [$this->tester->createProductTransfer()], $this->tester->createPaymentTransfer()->toArray());
        $saveOrderTransfer = $this->tester->createOrderTransfer($quote);

        $orderReference = $saveOrderTransfer->getOrderReference();

        $expectedHostedCheckoutId = static::HOSTED_CHECKOUT_ID;

        $paymentWorldlineTransfer = $this->tester->haveHostedCheckout($saveOrderTransfer, $quote, [
            PaymentWorldlineTransfer::PAYMENT_ID => 'expected_payment_id',
        ], [
            WorldlinePaymentHostedCheckoutTransfer::HOSTED_CHECKOUT_ID => $expectedHostedCheckoutId,
            WorldlinePaymentHostedCheckoutTransfer::PARTIAL_REDIRECT_URL => 'https://some.secure.url',
            WorldlinePaymentHostedCheckoutTransfer::RETURNMAC => Uuid::uuid(),
            WorldlinePaymentHostedCheckoutTransfer::CREATED_AT => (new DateTime())->setTimestamp(time()-8000)->format('Y-m-d H:i:s'), // 8000 > 7200 (number of seconds in 2 hours)
            WorldlinePaymentHostedCheckoutTransfer::UPDATED_AT => (new DateTime())->setTimestamp(time()-8000)->format('Y-m-d H:i:s'),
        ],
        [
            PaymentWorldlineTransactionStatusTransfer::STATUS => WorldlineConstants::STATUS_HOSTED_CHECKOUT_STATUS_PAYMENT_CREATED,
            PaymentWorldlineTransactionStatusTransfer::STATUS_CATEGORY => null,
            PaymentWorldlineTransactionStatusTransfer::STATUS_CODE => null,
            PaymentWorldlineTransactionStatusTransfer::FK_WORLDLINE_API_LOG => null,
            PaymentWorldlineTransactionStatusTransfer::FK_WORLDLINE_REST_LOG => null,
            PaymentWorldlineTransactionStatusTransfer::AMOUNT => $quote->getTotals()->getGrandTotal(),
            PaymentWorldlineTransactionStatusTransfer::TRANSACTION_TYPE => 'payment',
        ]);
        $queueMessage = new QueueSendMessageTransfer();
        $headers = [
            WorldlineWebhookConfig::HEADER_X_GCS_SIGNATURE => ['signature'],
            WorldlineWebhookConfig::HEADER_X_GCS_KEYID => ['2eb70980-d15e-4661-99b5-9c37440f27fb'],
            'content-type' => ['application/json'],
        ];
        $queueMessage->setHeaders($headers);

        $pathToResponses = __DIR__ . '/../_data/payment_created_event.json';
        $body = file_get_contents($pathToResponses);

        $bodyArray = json_decode($body, true);

        $bodyArray['payment']['id'] = 'expected_payment_id';
        $bodyArray['payment']['paymentOutput']['references']['merchantReference'] = $orderReference;

        $bodyArray['id'] = self::EVENT_ID;

        $body = json_encode($bodyArray, JSON_PRETTY_PRINT);

        $queueMessage->setBody($body);

        $queueReceiveMessageTransfer = new QueueReceiveMessageTransfer();
        $queueReceiveMessageTransfer->setQueueMessage($queueMessage);

        // Act
        (new WorldlineWebhookEventQueueMessageProcessorPlugin())->processMessages([$queueReceiveMessageTransfer]);

        // ASSERT
        $restReceiveLogEntity = VsyWorldlineRestReceiveLogQuery::create()->findOneByEventId(self::EVENT_ID);
        self::assertNotNull($restReceiveLogEntity);

        $restLogEntity = VsyWorldlineRestLogQuery::create()->findOneByEventId(self::EVENT_ID);

        $transactionStatusLogEntity = VsyPaymentWorldlineTransactionStatusLogQuery::create()->findOneByFkWorldlineRestLog($restLogEntity->getIdWorldlineRestLog());
        self::assertNotNull($transactionStatusLogEntity);
    }
}
