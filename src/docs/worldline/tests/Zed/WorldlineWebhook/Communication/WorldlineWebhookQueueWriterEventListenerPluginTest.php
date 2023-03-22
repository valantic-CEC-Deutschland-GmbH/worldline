<?php

declare(strict_types = 1);

namespace ValanticSprykerTest\Zed\WorldlineWebhook\Communication;

use Generated\Shared\Transfer\WorldlineWebhookRequestTransfer;
use Generated\Shared\Transfer\WorldlineWebhookResponseTransfer;
use ValanticSpryker\Shared\WorldlineWebhook\WorldlineWebhookConstants;
use ValanticSpryker\Zed\WorldlineWebhook\Communication\Plugin\EventListener\WorldlineWebhookQueueWriterEventListenerPlugin;
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
 * @group WorldlineWebhookQueueWriterEventListenerPluginTest
 * Add your own group annotations below this line
 * @group Worldline
 */
class WorldlineWebhookQueueWriterEventListenerPluginTest extends AbstractTest
{
    protected WorldlineWebhookCommunicationTester $tester;

    public function testPaymentCreatedEventIsCorrectlyInsertedIntoTheCorrectQueue()
    {
        // Arrange
        $sut = new WorldlineWebhookQueueWriterEventListenerPlugin();

        $apiRequestTransfer = new WorldlineWebhookRequestTransfer();
        $headers = [
            WorldlineWebhookConfig::HEADER_X_GCS_SIGNATURE => 'some_signature',
            WorldlineWebhookConfig::HEADER_X_GCS_KEYID => 'some_key_id',
        ];
        $apiRequestTransfer->setHeaderData($headers);

        $pathToResponses = __DIR__ . '/../_data/payment_created_event.json';
        $body = file_get_contents($pathToResponses);
        $requestData = [$body];

        $apiRequestTransfer->setRequestData($requestData);
        $apiRequestTransfer->setRequestType('POST');

        $apiResponseTransfer = new WorldlineWebhookResponseTransfer();
        $this->tester->cleanupInMemoryQueue();

        // ACT
        $apiResponseTransfer = $sut->handleEvent($apiRequestTransfer, $apiResponseTransfer);

        // Assert
        self::assertSame(200, $apiResponseTransfer->getCode());
        $messages = $this->tester->getQueueClient()->receiveMessages(WorldlineWebhookConstants::WORLDLINE_WEBHOOK_EVENT_QUEUE_NAME);

        self::assertCount(1, $messages);
        self::assertNotNull($messages[0]->getQueueMessage()->getHeaders());
        $headers = $messages[0]->getQueueMessage()->getHeaders();

        self::assertSame('some_signature', $headers[WorldlineWebhookConfig::HEADER_X_GCS_SIGNATURE]);
        self::assertSame('some_key_id', $headers[WorldlineWebhookConfig::HEADER_X_GCS_KEYID]);

        $body = $messages[0]->getQueueMessage()->getBody();
        $bodyArray = json_decode($body, true, 512);
        self::assertSame('payment.created', $bodyArray['type']);
        self::assertNotNull($bodyArray['payment']);
    }

    public function testPaymentPendingCaptureEventIsCorrectlyInsertedIntoTheCorrectQueue()
    {
        // Arrange
        $sut = new WorldlineWebhookQueueWriterEventListenerPlugin();

        $apiRequestTransfer = new WorldlineWebhookRequestTransfer();
        $headers = [
            WorldlineWebhookConfig::HEADER_X_GCS_SIGNATURE => 'some_signature',
            WorldlineWebhookConfig::HEADER_X_GCS_KEYID => 'some_key_id',
        ];
        $apiRequestTransfer->setHeaderData($headers);

        $pathToResponses = __DIR__ . '/../_data/payment_pending_capture_event.json';
        $body = file_get_contents($pathToResponses);
        $requestData = [$body];

        $apiRequestTransfer->setRequestData($requestData);
        $apiRequestTransfer->setRequestType('POST');

        $apiResponseTransfer = new WorldlineWebhookResponseTransfer();
        $this->tester->cleanupInMemoryQueue();

        // ACT
        $apiResponseTransfer = $sut->handleEvent($apiRequestTransfer, $apiResponseTransfer);

        // Assert
        self::assertSame(200, $apiResponseTransfer->getCode());
        $messages = $this->tester->getQueueClient()->receiveMessages(WorldlineWebhookConstants::WORLDLINE_WEBHOOK_EVENT_QUEUE_NAME);

        self::assertCount(1, $messages);
        self::assertNotNull($messages[0]->getQueueMessage()->getHeaders());
        $headers = $messages[0]->getQueueMessage()->getHeaders();

        self::assertSame('some_signature', $headers[WorldlineWebhookConfig::HEADER_X_GCS_SIGNATURE]);
        self::assertSame('some_key_id', $headers[WorldlineWebhookConfig::HEADER_X_GCS_KEYID]);

        $body = $messages[0]->getQueueMessage()->getBody();
        $bodyArray = json_decode($body, true, 512);
        self::assertSame('payment.pending_capture', $bodyArray['type']);
        self::assertNotNull($bodyArray['payment']);
        self::assertSame('PENDING_CAPTURE', $bodyArray['payment']['status']);
    }

    public function testPaymentCaptureRequestedEventIsCorrectlyInsertedIntoTheCorrectQueue()
    {
        // Arrange
        $sut = new WorldlineWebhookQueueWriterEventListenerPlugin();

        $apiRequestTransfer = new WorldlineWebhookRequestTransfer();
        $headers = [
            WorldlineWebhookConfig::HEADER_X_GCS_SIGNATURE => 'some_signature',
            WorldlineWebhookConfig::HEADER_X_GCS_KEYID => 'some_key_id',
        ];
        $apiRequestTransfer->setHeaderData($headers);

        $pathToResponses = __DIR__ . '/../_data/payment_capture_requested_event.json';
        $body = file_get_contents($pathToResponses);
        $requestData = [$body];

        $apiRequestTransfer->setRequestData($requestData);
        $apiRequestTransfer->setRequestType('POST');

        $apiResponseTransfer = new WorldlineWebhookResponseTransfer();
        $this->tester->cleanupInMemoryQueue();

        // ACT
        $apiResponseTransfer = $sut->handleEvent($apiRequestTransfer, $apiResponseTransfer);

        // Assert
        self::assertSame(200, $apiResponseTransfer->getCode());
        $messages = $this->tester->getQueueClient()->receiveMessages(WorldlineWebhookConstants::WORLDLINE_WEBHOOK_EVENT_QUEUE_NAME);

        self::assertCount(1, $messages);
        self::assertNotNull($messages[0]->getQueueMessage()->getHeaders());
        $headers = $messages[0]->getQueueMessage()->getHeaders();

        self::assertSame('some_signature', $headers[WorldlineWebhookConfig::HEADER_X_GCS_SIGNATURE]);
        self::assertSame('some_key_id', $headers[WorldlineWebhookConfig::HEADER_X_GCS_KEYID]);

        $body = $messages[0]->getQueueMessage()->getBody();
        $bodyArray = json_decode($body, true, 512);
        self::assertSame('payment.capture_requested', $bodyArray['type']);
        self::assertNotNull($bodyArray['payment']);
        self::assertSame('CAPTURE_REQUESTED', $bodyArray['payment']['status']);
        self::assertSame('PENDING_CONNECT_OR_3RD_PARTY', $bodyArray['payment']['statusOutput']['statusCategory']);
    }

    public function testPaymentCapturedEventIsCorrectlyInsertedIntoTheCorrectQueue()
    {
        // Arrange
        $sut = new WorldlineWebhookQueueWriterEventListenerPlugin();

        $apiRequestTransfer = new WorldlineWebhookRequestTransfer();
        $headers = [
            WorldlineWebhookConfig::HEADER_X_GCS_SIGNATURE => 'some_signature',
            WorldlineWebhookConfig::HEADER_X_GCS_KEYID => 'some_key_id',
        ];
        $apiRequestTransfer->setHeaderData($headers);

        $pathToResponses = __DIR__ . '/../_data/payment_captured_event.json';
        $body = file_get_contents($pathToResponses);
        $requestData = [$body];

        $apiRequestTransfer->setRequestData($requestData);
        $apiRequestTransfer->setRequestType('POST');

        $apiResponseTransfer = new WorldlineWebhookResponseTransfer();
        $this->tester->cleanupInMemoryQueue();

        // ACT
        $apiResponseTransfer = $sut->handleEvent($apiRequestTransfer, $apiResponseTransfer);

        // Assert
        self::assertSame(200, $apiResponseTransfer->getCode());
        $messages = $this->tester->getQueueClient()->receiveMessages(WorldlineWebhookConstants::WORLDLINE_WEBHOOK_EVENT_QUEUE_NAME);

        self::assertCount(1, $messages);
        self::assertNotNull($messages[0]->getQueueMessage()->getHeaders());
        $headers = $messages[0]->getQueueMessage()->getHeaders();

        self::assertSame('some_signature', $headers[WorldlineWebhookConfig::HEADER_X_GCS_SIGNATURE]);
        self::assertSame('some_key_id', $headers[WorldlineWebhookConfig::HEADER_X_GCS_KEYID]);

        $body = $messages[0]->getQueueMessage()->getBody();
        $bodyArray = json_decode($body, true, 512);
        self::assertSame('payment.captured', $bodyArray['type']);
        self::assertNotNull($bodyArray['payment']);
        self::assertSame('CAPTURED', $bodyArray['payment']['status']);
        self::assertSame('COMPLETED', $bodyArray['payment']['statusOutput']['statusCategory']);
    }

    public function testPaymentPaidEventIsCorrectlyInsertedIntoTheCorrectQueue()
    {
        // Arrange
        $sut = new WorldlineWebhookQueueWriterEventListenerPlugin();

        $apiRequestTransfer = new WorldlineWebhookRequestTransfer();
        $headers = [
            WorldlineWebhookConfig::HEADER_X_GCS_SIGNATURE => 'some_signature',
            WorldlineWebhookConfig::HEADER_X_GCS_KEYID => 'some_key_id',
        ];
        $apiRequestTransfer->setHeaderData($headers);

        $pathToResponses = __DIR__ . '/../_data/payment_paid_event.json';
        $body = file_get_contents($pathToResponses);
        $requestData = [$body];

        $apiRequestTransfer->setRequestData($requestData);
        $apiRequestTransfer->setRequestType('POST');

        $apiResponseTransfer = new WorldlineWebhookResponseTransfer();
        $this->tester->cleanupInMemoryQueue();

        // ACT
        $apiResponseTransfer = $sut->handleEvent($apiRequestTransfer, $apiResponseTransfer);

        // Assert
        self::assertSame(200, $apiResponseTransfer->getCode());
        $messages = $this->tester->getQueueClient()->receiveMessages(WorldlineWebhookConstants::WORLDLINE_WEBHOOK_EVENT_QUEUE_NAME);

        self::assertCount(1, $messages);
        self::assertNotNull($messages[0]->getQueueMessage()->getHeaders());
        $headers = $messages[0]->getQueueMessage()->getHeaders();

        self::assertSame('some_signature', $headers[WorldlineWebhookConfig::HEADER_X_GCS_SIGNATURE]);
        self::assertSame('some_key_id', $headers[WorldlineWebhookConfig::HEADER_X_GCS_KEYID]);

        $body = $messages[0]->getQueueMessage()->getBody();
        $bodyArray = json_decode($body, true, 512);
        self::assertSame('payment.paid', $bodyArray['type']);
        self::assertNotNull($bodyArray['payment']);
        self::assertSame('PAID', $bodyArray['payment']['status']);
        self::assertSame('COMPLETED', $bodyArray['payment']['statusOutput']['statusCategory']);
    }
}
