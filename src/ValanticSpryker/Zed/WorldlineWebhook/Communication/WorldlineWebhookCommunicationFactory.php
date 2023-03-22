<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\WorldlineWebhook\Communication;

use ValanticSpryker\Zed\WorldlineWebhook\Communication\EventListener\WorldlineWebhookControllerEventListener;
use ValanticSpryker\Zed\WorldlineWebhook\Communication\EventListener\WorldlineWebhookControllerEventListenerInterface;
use ValanticSpryker\Zed\WorldlineWebhook\Communication\Formatter\FormatterInterface;
use ValanticSpryker\Zed\WorldlineWebhook\Communication\Formatter\JsonFormatter;
use ValanticSpryker\Zed\WorldlineWebhook\Communication\Router\WorldlineWebhookRouter;
use ValanticSpryker\Zed\WorldlineWebhook\Communication\Transformer\Transformer;
use ValanticSpryker\Zed\WorldlineWebhook\Communication\Transformer\TransformerInterface;
use ValanticSpryker\Zed\WorldlineWebhook\WorldlineWebhookDependencyProvider;
use Spryker\Service\UtilEncoding\UtilEncodingServiceInterface;
use Spryker\Zed\Kernel\Communication\AbstractCommunicationFactory;
use Symfony\Component\Routing\RouterInterface;

/**
 * @method \ValanticSpryker\Zed\WorldlineWebhook\Business\WorldlineWebhookFacadeInterface getFacade()
 * @method \ValanticSpryker\Zed\WorldlineWebhook\WorldlineWebhookConfig getConfig()
 */
class WorldlineWebhookCommunicationFactory extends AbstractCommunicationFactory
{
    /**
     * @return \Symfony\Component\Routing\RouterInterface
     */
    public function createWorldlineWebhookRouter(): RouterInterface
    {
        return new WorldlineWebhookRouter($this->getConfig());
    }

    /**
     * @return \ValanticSpryker\Zed\WorldlineWebhook\Communication\EventListener\WorldlineWebhookControllerEventListenerInterface
     */
    public function createApiControllerEventListener(): WorldlineWebhookControllerEventListenerInterface
    {
        return new WorldlineWebhookControllerEventListener(
            $this->createTransformer(),
            $this->getFacade(),
            $this->getUtilEncodingService(),
        );
    }

    /**
     * @return \ValanticSpryker\Zed\WorldlineWebhook\Communication\Transformer\TransformerInterface
     */
    private function createTransformer(): TransformerInterface
    {
        return new Transformer(
            $this->createJsonFormatter(),
            $this->getConfig(),
            $this->getUtilEncodingService(),
        );
    }

    /**
     * @return \ValanticSpryker\Zed\WorldlineWebhook\Communication\Formatter\FormatterInterface
     */
    public function createJsonFormatter(): FormatterInterface
    {
        return new JsonFormatter($this->getUtilEncodingService());
    }

    /**
     * @return \Spryker\Service\UtilEncoding\UtilEncodingServiceInterface
     */
    private function getUtilEncodingService(): UtilEncodingServiceInterface
    {
        return $this->getProvidedDependency(WorldlineWebhookDependencyProvider::SERVICE_UTIL_ENCODING);
    }
}
