<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\WorldlineWebhook\Communication\Plugin\Queue;

use Generated\Shared\Transfer\QueueReceiveMessageTransfer;

interface WorldlineQueueMessageProcessorPluginInterface
{
    /**
     * @param \Generated\Shared\Transfer\QueueReceiveMessageTransfer $queueMessage
     *
     * @return \Generated\Shared\Transfer\QueueReceiveMessageTransfer
     */
    public function processMessage(QueueReceiveMessageTransfer $queueMessage): QueueReceiveMessageTransfer;
}
