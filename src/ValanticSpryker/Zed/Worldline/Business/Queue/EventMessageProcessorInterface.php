<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\Queue;

use Generated\Shared\Transfer\QueueReceiveMessageTransfer;

interface EventMessageProcessorInterface
{
    /**
     * @param \Generated\Shared\Transfer\QueueReceiveMessageTransfer $queueReceiveMessageTransfer
     *
     * @return \Generated\Shared\Transfer\QueueReceiveMessageTransfer
     */
    public function processEventMessage(QueueReceiveMessageTransfer $queueReceiveMessageTransfer): QueueReceiveMessageTransfer;
}
