<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\WorldlineWebhook\Business\Queue;

use ValanticSpryker\Zed\WorldlineWebhook\Communication\Plugin\Queue\WorldlineQueueMessageProcessorPluginInterface;

class WorldlineWebhookQueueMessageProcessor implements WorldlineWebhookQueueMessageProcessorInterface
{
    /**
     * @param \ValanticSpryker\Zed\WorldlineWebhook\Communication\Plugin\Queue\WorldlineQueueMessageProcessorPluginInterface $worldlineQueueMessageProcessorPluginInterface
     */
    public function __construct(private WorldlineQueueMessageProcessorPluginInterface $worldlineQueueMessageProcessorPluginInterface)
    {
    }

    /**
     * @param array<\Generated\Shared\Transfer\QueueReceiveMessageTransfer> $queueMessageTransfers
     *
     * @return array<\Generated\Shared\Transfer\QueueReceiveMessageTransfer>
     */
    public function processMessages(array $queueMessageTransfers): array
    {
        foreach ($queueMessageTransfers as $queueMessage) {
            $this->worldlineQueueMessageProcessorPluginInterface->processMessage($queueMessage);
        }

        return $queueMessageTransfers;
    }
}
