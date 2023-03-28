<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\WorldlineWebhook\Business;

use Spryker\Client\Queue\QueueClientInterface;
use Spryker\Zed\Kernel\Business\AbstractBusinessFactory;
use ValanticSpryker\Zed\WorldlineWebhook\Business\Dispatcher\WorldlineWebhookEventDispatcher;
use ValanticSpryker\Zed\WorldlineWebhook\Business\Dispatcher\WorldlineWebhookEventDispatcherInterface;
use ValanticSpryker\Zed\WorldlineWebhook\Business\Filter\WorldlineWebhookRequestTransferFilter;
use ValanticSpryker\Zed\WorldlineWebhook\Business\Filter\WorldlineWebhookRequestTransferFilterInterface;
use ValanticSpryker\Zed\WorldlineWebhook\Business\Queue\WorldlineWebhookEventQueueWriter;
use ValanticSpryker\Zed\WorldlineWebhook\Business\Queue\WorldlineWebhookEventQueueWriterInterface;
use ValanticSpryker\Zed\WorldlineWebhook\Business\Queue\WorldlineWebhookQueueMessageProcessor;
use ValanticSpryker\Zed\WorldlineWebhook\Business\Queue\WorldlineWebhookQueueMessageProcessorInterface;
use ValanticSpryker\Zed\WorldlineWebhook\Communication\Plugin\Queue\WorldlineQueueMessageProcessorPluginInterface;
use ValanticSpryker\Zed\WorldlineWebhook\WorldlineWebhookDependencyProvider;

/**
 * @method \ValanticSpryker\Zed\WorldlineWebhook\WorldlineWebhookConfig getConfig()
 */
class WorldlineWebhookBusinessFactory extends AbstractBusinessFactory
{
    /**
     * @return \ValanticSpryker\Zed\WorldlineWebhook\Business\Filter\WorldlineWebhookRequestTransferFilterInterface
     */
    public function createRequestTransferFilter(): WorldlineWebhookRequestTransferFilterInterface
    {
        return new WorldlineWebhookRequestTransferFilter($this->getRequestTransferFilterPlugins());
    }

    /**
     * @return array<\ValanticSpryker\Zed\WorldlineWebhook\Communication\Plugin\Filter\WorldlineWebhookRequestTransferFilterPluginInterface>
     */
    private function getRequestTransferFilterPlugins(): array
    {
        return $this->getProvidedDependency(WorldlineWebhookDependencyProvider::PLUGINS_WORLDLINE_WEBHOOK_REQUEST_TRANSFER_FILTER);
    }

    /**
     * @return \ValanticSpryker\Zed\WorldlineWebhook\Business\Dispatcher\WorldlineWebhookEventDispatcherInterface
     */
    public function createWebhookEventDispatcher(): WorldlineWebhookEventDispatcherInterface
    {
        return new WorldlineWebhookEventDispatcher($this->getEventListenerPlugins());
    }

    /**
     * @return array<\ValanticSpryker\Zed\WorldlineWebhook\Communication\Plugin\EventListener\WorldlineWebhookEventListenerPluginInterface>
     */
    private function getEventListenerPlugins(): array
    {
        return $this->getProvidedDependency(WorldlineWebhookDependencyProvider::PLUGINS_WEBHOOK_EVENT_LISTENER);
    }

    /**
     * @return \ValanticSpryker\Zed\WorldlineWebhook\Business\Queue\WorldlineWebhookEventQueueWriterInterface
     */
    public function createWebhookEventQueueWriter(): WorldlineWebhookEventQueueWriterInterface
    {
        return new WorldlineWebhookEventQueueWriter($this->getQueueClient());
    }

    /**
     * @return \Spryker\Client\Queue\QueueClientInterface
     */
    private function getQueueClient(): QueueClientInterface
    {
        return $this->getProvidedDependency(WorldlineWebhookDependencyProvider::CLIENT_QUEUE);
    }

    /**
     * @return \ValanticSpryker\Zed\WorldlineWebhook\Business\Queue\WorldlineWebhookQueueMessageProcessorInterface
     */
    public function createQueueMessageProcessor(): WorldlineWebhookQueueMessageProcessorInterface
    {
        return new WorldlineWebhookQueueMessageProcessor($this->getQueueMessageProcessorPlugin());
    }

    /**
     * @return \ValanticSpryker\Zed\WorldlineWebhook\Communication\Plugin\Queue\WorldlineQueueMessageProcessorPluginInterface
     */
    private function getQueueMessageProcessorPlugin(): WorldlineQueueMessageProcessorPluginInterface
    {
        return $this->getProvidedDependency(WorldlineWebhookDependencyProvider::PLUGIN_WEBHOOK_QUEUE_PROCESSOR);
    }
}
