<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Communication\Plugin\Queue;

use Generated\Shared\Transfer\QueueReceiveMessageTransfer;
use Spryker\Shared\Log\LoggerTrait;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use ValanticSpryker\Zed\WorldlineWebhook\Communication\Plugin\Queue\WorldlineQueueMessageProcessorPluginInterface;

/**
 * @method \ValanticSpryker\Zed\Worldline\Business\WorldlineFacadeInterface getFacade()
 * @method \ValanticSpryker\Zed\Worldline\Communication\WorldlineCommunicationFactory getFactory()
 * @method \ValanticSpryker\Zed\Worldline\Persistence\WorldlineQueryContainerInterface getQueryContainer()
 * @method \ValanticSpryker\Zed\Worldline\WorldlineConfig getConfig()
 */
class WorldlineEventQueueMessageProcessorPlugin extends AbstractPlugin implements WorldlineQueueMessageProcessorPluginInterface
{
    use LoggerTrait;

    /**
     * @inheritDoc
     */
    public function processMessage(QueueReceiveMessageTransfer $queueMessage): QueueReceiveMessageTransfer
    {
        $this->getLogger()->debug('Processing webhook event.');

        return $this->getFacade()->processEventMessage($queueMessage);
    }
}
