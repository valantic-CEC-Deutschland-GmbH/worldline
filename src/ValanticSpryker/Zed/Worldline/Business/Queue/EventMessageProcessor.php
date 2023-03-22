<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\Queue;

use Exception;
use Generated\Shared\Transfer\QueueReceiveMessageTransfer;
use Generated\Shared\Transfer\WebhookEventTransfer;
use Ingenico\Connect\Sdk\Domain\Webhooks\WebhooksEvent;
use Ingenico\Connect\Sdk\Webhooks\WebhooksHelper;
use ValanticSpryker\Zed\Worldline\Business\Mapper\WebhookEventMapperInterface;
use ValanticSpryker\Zed\Worldline\Business\Writer\WorldlineWriterInterface;
use ValanticSpryker\Zed\Worldline\WorldlineConfig;
use ValanticSpryker\Zed\WorldlineWebhook\WorldlineWebhookConfig;
use Spryker\Service\UtilEncoding\UtilEncodingServiceInterface;
use Spryker\Shared\Log\LoggerTrait;
use Spryker\Zed\Event\Business\Exception\MessageTypeNotFoundException;
use Throwable;

class EventMessageProcessor implements EventMessageProcessorInterface
{
    use LoggerTrait;

    /**
     * @param \ValanticSpryker\Zed\Worldline\Business\Writer\WorldlineWriterInterface $worldlineWriter
     * @param \Ingenico\Connect\Sdk\Webhooks\WebhooksHelper $webhooksHelper
     * @param \ValanticSpryker\Zed\Worldline\Business\Mapper\WebhookEventMapperInterface $webhookEventMapper
     * @param \ValanticSpryker\Zed\Worldline\WorldlineConfig $worldlineConfig
     * @param array<\ValanticSpryker\Zed\Worldline\Business\EventProcessor\EventProcessorInterface> $worldlineEventProcessors
     * @param \Spryker\Service\UtilEncoding\UtilEncodingServiceInterface $utilEncodingService
     */
    public function __construct(protected WorldlineWriterInterface $worldlineWriter, private WebhooksHelper $webhooksHelper, private WebhookEventMapperInterface $webhookEventMapper, private WorldlineConfig $worldlineConfig, private array $worldlineEventProcessors, private UtilEncodingServiceInterface $utilEncodingService)
    {
    }

    /**
     * @param \Generated\Shared\Transfer\QueueReceiveMessageTransfer $queueReceiveMessageTransfer
     *
     * @return \Generated\Shared\Transfer\QueueReceiveMessageTransfer
     */
    public function processEventMessage(QueueReceiveMessageTransfer $queueReceiveMessageTransfer): QueueReceiveMessageTransfer
    {
        if (!$queueReceiveMessageTransfer->getQueueMessage()) {
            return $queueReceiveMessageTransfer;
        }

        $headers = $this->setHeaders($queueReceiveMessageTransfer);

        $body = $queueReceiveMessageTransfer->getQueueMessage()->getBody();
        $queueReceiveMessageTransfer->setAcknowledge(false);
        $webhookEvent = $this->getWebhookEvent($body, $headers, $queueReceiveMessageTransfer);
        if ($queueReceiveMessageTransfer->getHasError() || $queueReceiveMessageTransfer->getReject()) {
            return $queueReceiveMessageTransfer;
        }
        try {
            $webhookEventTransfer = $this->webhookEventMapper->mapWebhooksEventToWebhookEventTransfer($webhookEvent);

            $this->processWebhookEvent($webhookEventTransfer);
        } catch (Exception $ex) {
            $this->rejectMessage($queueReceiveMessageTransfer, 'Failed to process message. ' . $ex->getMessage());
        } catch (Throwable $ex) {
            $this->rejectMessage($queueReceiveMessageTransfer, 'Failed to process message. ' . $ex->getMessage());
        }

        return $queueReceiveMessageTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\WebhookEventTransfer $webhookEventTransfer
     *
     * @return void
     */
    protected function processWebhookEvent(WebhookEventTransfer $webhookEventTransfer): void
    {
        foreach ($this->worldlineEventProcessors as $eventProcessor) {
            $eventProcessor->processEvent($webhookEventTransfer);
        }
    }

    /**
     * @param \Generated\Shared\Transfer\QueueReceiveMessageTransfer $queueReceiveMessageTransfer
     *
     * @return array
     */
    private function setHeaders(QueueReceiveMessageTransfer $queueReceiveMessageTransfer): array
    {
        if (!$queueReceiveMessageTransfer->getQueueMessage()) {
            return [];
        }

        $headerData = $queueReceiveMessageTransfer->getQueueMessage()->getHeaders();

        $headers = [];
        foreach ($headerData as $key => $headerDatum) {
            if (is_array($headerDatum)) {
                $headers[$key] = $headerDatum[0];
            } else {
                $headers[$key] = $headerDatum;
            }
        }

        return $headers;
    }

    /**
     * @param string|null $body
     * @param array $headers
     * @param \Generated\Shared\Transfer\QueueReceiveMessageTransfer $queueReceiveMessageTransfer
     *
     * @return \Ingenico\Connect\Sdk\Domain\Webhooks\WebhooksEvent
     */
    private function getWebhookEvent(
        ?string $body,
        array $headers,
        QueueReceiveMessageTransfer $queueReceiveMessageTransfer
    ): WebhooksEvent {
        $webhookEvent = new WebhooksEvent();

        try {
            $webhookEvent = $this->webhooksHelper->unmarshal($body, $headers);
            $queueReceiveMessageTransfer->setAcknowledge(true);
        } catch (Exception $exception) {
            $this->getLogger()->debug('unmarshal webhook event failed.');
            if ($this->worldlineConfig->getIsDevelopmentWithoutWorldlineBackend()) {
                $this->getLogger()->debug('Am on dev machine assume unencrypted body');
                $webhookEvent->fromJson($body);
                $queueReceiveMessageTransfer->setAcknowledge(true);
            } else {
                $this->rejectMessage($queueReceiveMessageTransfer, 'Failed marshaling webhook message.');
                $this->getLogger()->error('Exception occurred while trying to validate webhook event: ' . $exception->getMessage());
            }
        }

        return $webhookEvent;
    }

    /**
     * @param \Generated\Shared\Transfer\QueueReceiveMessageTransfer $queueReceiveMessageTransfer
     * @param string $errorMessage
     *
     * @return void
     */
    protected function rejectMessage(QueueReceiveMessageTransfer $queueReceiveMessageTransfer, string $errorMessage): void
    {
        $this->setMessage($queueReceiveMessageTransfer, 'errorMessage', $errorMessage);
        $queueReceiveMessageTransfer->setAcknowledge(false);
        $queueReceiveMessageTransfer->setReject(true);
        $queueReceiveMessageTransfer->setHasError(true);
        $queueReceiveMessageTransfer->setRoutingKey(WorldlineWebhookConfig::EVENT_ROUTING_KEY_ERROR);
    }

    /**
     * @param \Generated\Shared\Transfer\QueueReceiveMessageTransfer $queueReceiveMessageTransfer
     * @param string $messageType
     * @param string $errorMessage
     *
     * @throws \Spryker\Zed\Event\Business\Exception\MessageTypeNotFoundException
     *
     * @return void
     */
    private function setMessage(QueueReceiveMessageTransfer $queueReceiveMessageTransfer, string $messageType, string $errorMessage): void
    {
        if (!$messageType) {
            throw new MessageTypeNotFoundException('message type is not defined');
        }

        $queueMessageBody = $this->utilEncodingService->decodeJson($queueReceiveMessageTransfer->getQueueMessage()->getBody(), true);
        $queueMessageBody[$messageType] = $errorMessage;
        $queueReceiveMessageTransfer->getQueueMessage()->setBody($this->utilEncodingService->encodeJson($queueMessageBody));
    }
}
