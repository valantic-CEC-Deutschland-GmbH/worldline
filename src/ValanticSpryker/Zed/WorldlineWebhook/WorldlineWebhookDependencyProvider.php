<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\WorldlineWebhook;

use Spryker\Client\Queue\QueueClientInterface;
use Spryker\Service\UtilEncoding\UtilEncodingServiceInterface;
use Spryker\Zed\Kernel\AbstractBundleDependencyProvider;
use Spryker\Zed\Kernel\Container;
use ValanticSpryker\Zed\Worldline\Communication\Plugin\Queue\WorldlineEventQueueMessageProcessorPlugin;
use ValanticSpryker\Zed\WorldlineWebhook\Communication\Plugin\EventListener\WorldlineWebhookGetRequestEventListenerPlugin;
use ValanticSpryker\Zed\WorldlineWebhook\Communication\Plugin\EventListener\WorldlineWebhookQueueWriterEventListenerPlugin;
use ValanticSpryker\Zed\WorldlineWebhook\Communication\Plugin\Queue\WorldlineQueueMessageProcessorPluginInterface;

/**
 * @method \ValanticSpryker\Zed\WorldlineWebhook\WorldlineWebhookConfig getConfig()
 */
class WorldlineWebhookDependencyProvider extends AbstractBundleDependencyProvider
{
    /**
     * @var string
     */
    public const PLUGINS_WORLDLINE_WEBHOOK_REQUEST_TRANSFER_FILTER = 'PLUGINS_WORLDLINE_WEBHOOK_REQUEST_TRANSFER_FILTER';

    /**
     * @var string
     */
    public const SERVICE_UTIL_ENCODING = 'SERVICE_UTIL_ENCODING';

    /**
     * @var string
     */
    public const PLUGINS_WEBHOOK_EVENT_LISTENER = 'PLUGINS_WEBHOOK_EVENT_LISTENER';

    /**
     * @var string
     */
    public const CLIENT_QUEUE = 'CLIENT_QUEUE';

    /**
     * @var string
     */
    public const PLUGIN_WEBHOOK_QUEUE_PROCESSOR = 'PLUGINS_WEBHOOK_QUEUE_PROCESSOR';

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    public function provideCommunicationLayerDependencies(Container $container): Container
    {
        $container = parent::provideCommunicationLayerDependencies($container);

        $container = $this->addUtilEncodingService($container);

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    public function provideBusinessLayerDependencies(Container $container): Container
    {
        $container = parent::provideBusinessLayerDependencies($container);

        $container = $this->addRequestFilterPlugins($container);
        $container = $this->addWebhookEventListenerPlugins($container);
        $container = $this->addWebhookQueueMessageProcessorPlugin($container);
        $container = $this->addQueueClient($container);

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    private function addRequestFilterPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_WORLDLINE_WEBHOOK_REQUEST_TRANSFER_FILTER, fn () => $this->getWebhookRequestTransferFilterPluginCollection());

        return $container;
    }

    /**
     * @return array<\ValanticSpryker\Zed\WorldlineWebhook\Communication\Plugin\Filter\WorldlineWebhookRequestTransferFilterPluginInterface>
     */
    private function getWebhookRequestTransferFilterPluginCollection(): array
    {
        return [];
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    private function addWebhookEventListenerPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_WEBHOOK_EVENT_LISTENER, fn () => $this->getWebhookEventListenerPluginCollection());

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    private function addUtilEncodingService(Container $container): Container
    {
        $container->set(
            static::SERVICE_UTIL_ENCODING,
            fn (Container $container): UtilEncodingServiceInterface => $container->getLocator()->utilEncoding()->service(),
        );

        return $container;
    }

    /**
     * @return array<\ValanticSpryker\Zed\WorldlineWebhook\Communication\Plugin\EventListener\WorldlineWebhookEventListenerPluginInterface>
     */
    private function getWebhookEventListenerPluginCollection(): array
    {
        return [
            new WorldlineWebhookQueueWriterEventListenerPlugin(),
            new WorldlineWebhookGetRequestEventListenerPlugin(),
        ];
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    private function addQueueClient(Container $container): Container
    {
        $container->set(
            static::CLIENT_QUEUE,
            fn (Container $container): QueueClientInterface => $container->getLocator()->queue()->client(),
        );

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    private function addWebhookQueueMessageProcessorPlugin(Container $container): Container
    {
        $container->set(static::PLUGIN_WEBHOOK_QUEUE_PROCESSOR, fn (): WorldlineQueueMessageProcessorPluginInterface => $this->getWebhookQueueProcessorPlugin());

        return $container;
    }

    /**
     * @return \ValanticSpryker\Zed\WorldlineWebhook\Communication\Plugin\Queue\WorldlineQueueMessageProcessorPluginInterface
     */
    private function getWebhookQueueProcessorPlugin(): WorldlineQueueMessageProcessorPluginInterface
    {
        return new WorldlineEventQueueMessageProcessorPlugin();
    }
}
