<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business;

use Ingenico\Connect\Sdk\Connection;
use Ingenico\Connect\Sdk\Webhooks\InMemorySecretKeyStore;
use Ingenico\Connect\Sdk\Webhooks\WebhooksHelper;
use Spryker\Service\UtilEncoding\UtilEncodingServiceInterface;
use Spryker\Zed\Customer\Business\CustomerFacadeInterface;
use Spryker\Zed\Kernel\Business\AbstractBusinessFactory;
use Spryker\Zed\Oms\Business\OmsFacadeInterface;
use Spryker\Zed\Payment\Business\PaymentFacadeInterface;
use ValanticSpryker\Zed\Worldline\Business\ApiCallHandler\ApiCallHandlerInterface;
use ValanticSpryker\Zed\Worldline\Business\ApiCallHandler\HostedCheckout\CreateHostedCheckoutApiCallHandler;
use ValanticSpryker\Zed\Worldline\Business\ApiCallHandler\HostedCheckout\GetHostedCheckoutStatusApiCallHandler;
use ValanticSpryker\Zed\Worldline\Business\ApiCallHandler\PaymentProducts\ApprovePaymentApiCallHandler;
use ValanticSpryker\Zed\Worldline\Business\ApiCallHandler\PaymentProducts\CancelPaymentApiCallHandler;
use ValanticSpryker\Zed\Worldline\Business\ApiCallHandler\PaymentProducts\CapturePaymentApiCallHandler;
use ValanticSpryker\Zed\Worldline\Business\ApiCallHandler\PaymentProducts\GetPaymentApiCallHandler;
use ValanticSpryker\Zed\Worldline\Business\ApiCallHandler\PaymentProducts\GetPaymentProductsApiCallHandler;
use ValanticSpryker\Zed\Worldline\Business\ApiCallHandler\Refund\CreateRefundApiCallHandler;
use ValanticSpryker\Zed\Worldline\Business\ApiCallHandler\Refund\GetRefundApiCallHandler;
use ValanticSpryker\Zed\Worldline\Business\ApiCallHandler\Token\DeleteTokenApiCallHandler;
use ValanticSpryker\Zed\Worldline\Business\Client\WorldlineClient;
use ValanticSpryker\Zed\Worldline\Business\Client\WorldlineClientInterface;
use ValanticSpryker\Zed\Worldline\Business\Connection\WorldlineConnection;
use ValanticSpryker\Zed\Worldline\Business\EventProcessor\EventProcessorInterface;
use ValanticSpryker\Zed\Worldline\Business\EventProcessor\PaymentEventProcessor;
use ValanticSpryker\Zed\Worldline\Business\EventProcessor\TokenEventProcessor;
use ValanticSpryker\Zed\Worldline\Business\Filter\PaymentMethodsFilter;
use ValanticSpryker\Zed\Worldline\Business\Filter\PaymentMethodsFilterInterface;
use ValanticSpryker\Zed\Worldline\Business\Logger\ApiErrorLogger;
use ValanticSpryker\Zed\Worldline\Business\Logger\ApiSettingsLogger;
use ValanticSpryker\Zed\Worldline\Business\Logger\ApprovePaymentApiLogger;
use ValanticSpryker\Zed\Worldline\Business\Logger\CreateHostedCheckoutApiLogger;
use ValanticSpryker\Zed\Worldline\Business\Logger\DeleteTokenApiLogger;
use ValanticSpryker\Zed\Worldline\Business\Logger\FkPaymentWorldlineApiLogger;
use ValanticSpryker\Zed\Worldline\Business\Logger\GetHostedCheckoutStatusLogger;
use ValanticSpryker\Zed\Worldline\Business\Logger\GetPaymentApiLogger;
use ValanticSpryker\Zed\Worldline\Business\Logger\GetPaymentProductsApiLogger;
use ValanticSpryker\Zed\Worldline\Business\Logger\HttpStatusApiLogger;
use ValanticSpryker\Zed\Worldline\Business\Logger\WorldlineApiCallLogger;
use ValanticSpryker\Zed\Worldline\Business\Logger\WorldlineApiCallLoggerInterface;
use ValanticSpryker\Zed\Worldline\Business\Logger\WorldlineApiLogger;
use ValanticSpryker\Zed\Worldline\Business\Logger\WorldlineApiLoggerInterface;
use ValanticSpryker\Zed\Worldline\Business\Logger\WorldlineApiLoggerPluginInterface;
use ValanticSpryker\Zed\Worldline\Business\Mapper\HostedCheckoutMapper;
use ValanticSpryker\Zed\Worldline\Business\Mapper\HostedCheckoutMapperInterface;
use ValanticSpryker\Zed\Worldline\Business\Mapper\PaymentProductsMapper;
use ValanticSpryker\Zed\Worldline\Business\Mapper\PaymentProductsMapperInterface;
use ValanticSpryker\Zed\Worldline\Business\Mapper\PaymentQuoteMapper;
use ValanticSpryker\Zed\Worldline\Business\Mapper\PaymentQuoteMapperInterface;
use ValanticSpryker\Zed\Worldline\Business\Mapper\RefundsMapper;
use ValanticSpryker\Zed\Worldline\Business\Mapper\RefundsMapperInterface;
use ValanticSpryker\Zed\Worldline\Business\Mapper\WebhookEventMapper;
use ValanticSpryker\Zed\Worldline\Business\Mapper\WebhookEventMapperInterface;
use ValanticSpryker\Zed\Worldline\Business\Mapper\WorldlineMapperInterface;
use ValanticSpryker\Zed\Worldline\Business\Mapper\WorldlineTokenMapper;
use ValanticSpryker\Zed\Worldline\Business\Oms\Command\CommandHandlerInterface;
use ValanticSpryker\Zed\Worldline\Business\Oms\Command\GenericCommandHandler;
use ValanticSpryker\Zed\Worldline\Business\Oms\Command\Mapper\ApprovePaymentCommandMapper;
use ValanticSpryker\Zed\Worldline\Business\Oms\Command\Mapper\CreateHostedCheckoutCommandMapper;
use ValanticSpryker\Zed\Worldline\Business\Oms\Command\Mapper\GetHostedCheckoutStatusCommandMapper;
use ValanticSpryker\Zed\Worldline\Business\Oms\Command\Mapper\GetPaymentStatusCommandMapper;
use ValanticSpryker\Zed\Worldline\Business\Oms\Command\Mapper\WorldlineCommandMapperInterface;
use ValanticSpryker\Zed\Worldline\Business\Oms\Command\Mapper\WorldlineCreateHostedCheckoutCardPaymentSpecificInputPartMapper;
use ValanticSpryker\Zed\Worldline\Business\Oms\Command\Mapper\WorldlineCreateHostedCheckoutFkPaymentWorldlinePartMapper;
use ValanticSpryker\Zed\Worldline\Business\Oms\Command\Mapper\WorldlineCreateHostedCheckoutFraudFieldsPartMapper;
use ValanticSpryker\Zed\Worldline\Business\Oms\Command\Mapper\WorldlineCreateHostedCheckoutHostedCheckoutSpecificInputPartMapper;
use ValanticSpryker\Zed\Worldline\Business\Oms\Command\Mapper\WorldlineCreateHostedCheckoutOrderPartMapper;
use ValanticSpryker\Zed\Worldline\Business\Oms\Command\Mapper\WorldlineCreateHostedCheckoutPartMapperInterface;
use ValanticSpryker\Zed\Worldline\Business\Oms\Command\Saver\ApprovePaymentCommandSaver;
use ValanticSpryker\Zed\Worldline\Business\Oms\Command\Saver\CreateHostedCheckoutCommandSaver;
use ValanticSpryker\Zed\Worldline\Business\Oms\Command\Saver\GetHostedCheckoutStatusCommandSaver;
use ValanticSpryker\Zed\Worldline\Business\Oms\Command\Saver\GetPaymentStatusCommandSaver;
use ValanticSpryker\Zed\Worldline\Business\Oms\Command\Saver\WorldlineCommandSaverInterface;
use ValanticSpryker\Zed\Worldline\Business\Oms\Condition\HostedCheckoutCreatedChecker;
use ValanticSpryker\Zed\Worldline\Business\Oms\Condition\HostedCheckoutCreatedCheckerInterface;
use ValanticSpryker\Zed\Worldline\Business\Oms\Condition\HostedCheckoutFailedChecker;
use ValanticSpryker\Zed\Worldline\Business\Oms\Condition\HostedCheckoutFailedCheckerInterface;
use ValanticSpryker\Zed\Worldline\Business\Oms\Condition\HostedCheckoutPaymentWasCreatedChecker;
use ValanticSpryker\Zed\Worldline\Business\Oms\Condition\HostedCheckoutPaymentWasCreatedCheckerInterface;
use ValanticSpryker\Zed\Worldline\Business\Oms\Condition\HostedCheckoutStatusIsCancelledChecker;
use ValanticSpryker\Zed\Worldline\Business\Oms\Condition\HostedCheckoutStatusIsCancelledCheckerInterface;
use ValanticSpryker\Zed\Worldline\Business\Oms\Condition\HostedCheckoutStatusTimeoutChecker;
use ValanticSpryker\Zed\Worldline\Business\Oms\Condition\HostedCheckoutStatusTimeoutCheckerInterface;
use ValanticSpryker\Zed\Worldline\Business\Oms\Condition\PaymentCancelledChecker;
use ValanticSpryker\Zed\Worldline\Business\Oms\Condition\PaymentCancelledCheckerInterface;
use ValanticSpryker\Zed\Worldline\Business\Oms\Condition\PaymentCapturedChecker;
use ValanticSpryker\Zed\Worldline\Business\Oms\Condition\PaymentCapturedCheckerInterface;
use ValanticSpryker\Zed\Worldline\Business\Oms\Condition\PaymentCaptureRejectedChecker;
use ValanticSpryker\Zed\Worldline\Business\Oms\Condition\PaymentCaptureRejectedCheckerInterface;
use ValanticSpryker\Zed\Worldline\Business\Oms\Condition\PaymentCaptureTimedOutChecker;
use ValanticSpryker\Zed\Worldline\Business\Oms\Condition\PaymentCaptureTimedOutCheckerInterface;
use ValanticSpryker\Zed\Worldline\Business\Oms\Condition\PaymentGuaranteedChecker;
use ValanticSpryker\Zed\Worldline\Business\Oms\Condition\PaymentGuaranteedCheckerInterface;
use ValanticSpryker\Zed\Worldline\Business\Oms\Condition\PaymentRejectedChecker;
use ValanticSpryker\Zed\Worldline\Business\Oms\Condition\PaymentRejectedCheckerInterface;
use ValanticSpryker\Zed\Worldline\Business\Order\OrderHydrator;
use ValanticSpryker\Zed\Worldline\Business\Order\OrderHydratorInterface;
use ValanticSpryker\Zed\Worldline\Business\Order\OrderManager;
use ValanticSpryker\Zed\Worldline\Business\Order\OrderManagerInterface;
use ValanticSpryker\Zed\Worldline\Business\Payment\Hook\PostSaveHook;
use ValanticSpryker\Zed\Worldline\Business\Payment\Hook\PostSaveHookInterface;
use ValanticSpryker\Zed\Worldline\Business\Queue\EventMessageProcessor;
use ValanticSpryker\Zed\Worldline\Business\Queue\EventMessageProcessorInterface;
use ValanticSpryker\Zed\Worldline\Business\Reader\WorldlineReader;
use ValanticSpryker\Zed\Worldline\Business\Reader\WorldlineReaderInterface;
use ValanticSpryker\Zed\Worldline\Business\Timestamp\WorldlineTimestampConverter;
use ValanticSpryker\Zed\Worldline\Business\Timestamp\WorldlineTimestampConverterInterface;
use ValanticSpryker\Zed\Worldline\Business\Token\DeletedWorldlineTokenRemover;
use ValanticSpryker\Zed\Worldline\Business\Token\DeletedWorldlineTokenRemoverInterface;
use ValanticSpryker\Zed\Worldline\Business\Token\Reader\TokenReader;
use ValanticSpryker\Zed\Worldline\Business\Token\Reader\TokenReaderInterface;
use ValanticSpryker\Zed\Worldline\Business\Token\WorldlinePaymentTokenDeleter;
use ValanticSpryker\Zed\Worldline\Business\Token\WorldlinePaymentTokenDeleterInterface;
use ValanticSpryker\Zed\Worldline\Business\Writer\WorldlineWriter;
use ValanticSpryker\Zed\Worldline\Business\Writer\WorldlineWriterInterface;
use ValanticSpryker\Zed\Worldline\WorldlineDependencyProvider;

/**
 * @method \ValanticSpryker\Zed\Worldline\WorldlineConfig getConfig()
 * @method \ValanticSpryker\Zed\Worldline\Persistence\WorldlineEntityManagerInterface getEntityManager()
 * @method \ValanticSpryker\Zed\Worldline\Persistence\WorldlineRepositoryInterface getRepository()
 * @method \ValanticSpryker\Zed\Worldline\Persistence\WorldlineQueryContainerInterface getQueryContainer()
 */
class WorldlineBusinessFactory extends AbstractBusinessFactory
{
    /**
     * @return \ValanticSpryker\Zed\Worldline\Business\ApiCallHandler\ApiCallHandlerInterface
     */
    public function createCreateHostedCheckoutApiCallHandler(): ApiCallHandlerInterface
    {
        return new CreateHostedCheckoutApiCallHandler(
            $this->createWorldlineClient(),
            $this->createHostedCheckoutMapper(),
            $this->createWorldlineApiLogger(),
        );
    }

    /**
     * @return \ValanticSpryker\Zed\Worldline\Business\ApiCallHandler\ApiCallHandlerInterface
     */
    public function createGetHostedCheckoutStatusApiCallHandler(): ApiCallHandlerInterface
    {
        return new GetHostedCheckoutStatusApiCallHandler(
            $this->createWorldlineClient(),
            $this->createHostedCheckoutMapper(),
            $this->createWorldlineApiLogger(),
        );
    }

    /**
     * @return \ValanticSpryker\Zed\Worldline\Business\ApiCallHandler\ApiCallHandlerInterface
     */
    public function createGetPaymentProductsApiCallHandler(): ApiCallHandlerInterface
    {
        return new GetPaymentProductsApiCallHandler(
            $this->createWorldlineClient(),
            $this->createPaymentProductsMapper(),
            $this->createWorldlineApiLogger(),
        );
    }

    /**
     * @return \ValanticSpryker\Zed\Worldline\Business\ApiCallHandler\ApiCallHandlerInterface
     */
    public function createGetPaymentApiCallHandler(): ApiCallHandlerInterface
    {
        return new GetPaymentApiCallHandler(
            $this->createWorldlineClient(),
            $this->createPaymentProductsMapper(),
            $this->createWorldlineApiLogger(),
        );
    }

    /**
     * @return \ValanticSpryker\Zed\Worldline\Business\ApiCallHandler\ApiCallHandlerInterface
     */
    public function createCancelPaymentApiCallHandler(): ApiCallHandlerInterface
    {
        return new CancelPaymentApiCallHandler(
            $this->createWorldlineClient(),
            $this->createPaymentProductsMapper(),
            $this->createWorldlineApiLogger(),
        );
    }

    /**
     * @return \ValanticSpryker\Zed\Worldline\Business\ApiCallHandler\ApiCallHandlerInterface
     */
    public function createCapturePaymentApiCallHandler(): ApiCallHandlerInterface
    {
        return new CapturePaymentApiCallHandler(
            $this->createWorldlineClient(),
            $this->createPaymentProductsMapper(),
            $this->createWorldlineApiLogger(),
        );
    }

    /**
     * @return \ValanticSpryker\Zed\Worldline\Business\ApiCallHandler\ApiCallHandlerInterface
     */
    public function createApprovePaymentApiCallHandler(): ApiCallHandlerInterface
    {
        return new ApprovePaymentApiCallHandler(
            $this->createWorldlineClient(),
            $this->createPaymentProductsMapper(),
            $this->createWorldlineApiLogger(),
        );
    }

    /**
     * Public so we can override it for testing
     *
     * @return \Ingenico\Connect\Sdk\Connection
     */
    public function createWorldlineConnection(): Connection
    {
        return new WorldlineConnection($this->createApiCallLogger(), $this->getConfig()->getConnectTimeout(), $this->getConfig()->getReadTimeout());
    }

    /**
     * @return \ValanticSpryker\Zed\Worldline\Business\Client\WorldlineClientInterface
     */
    private function createWorldlineClient(): WorldlineClientInterface
    {
        return new WorldlineClient(
            $this->getConfig(),
            $this->createWorldlineConnection(),
        );
    }

    /**
     * @return \ValanticSpryker\Zed\Worldline\Business\Logger\WorldlineApiLoggerInterface
     */
    private function createWorldlineApiLogger(): WorldlineApiLoggerInterface
    {
        return new WorldlineApiLogger($this->getWorldlineApiLoggerPlugins());
    }

    /**
     * @return array<\ValanticSpryker\Zed\Worldline\Business\Logger\WorldlineApiLoggerPluginInterface>
     */
    private function getWorldlineApiLoggerPlugins(): array
    {
        return [
            $this->createApiErrorLogger(),
            $this->createApiSettingsLogger(),
            $this->createHttpStatusApiLogger(),
            $this->createFkPaymentWorldlineLogger(),
            $this->createGetHostedCheckoutStatusLogger(),
            $this->createCreateHostedCheckoutLogger(),
            $this->createGetPaymentProductsApiLogger(),
            $this->createGetPaymentApiLogger(),
            $this->createApprovePaymentApiLogger(),
            $this->createDeleteTokenApiLogger(),
        ];
    }

    /**
     * @return \ValanticSpryker\Zed\Worldline\Business\Logger\WorldlineApiCallLoggerInterface
     */
    private function createApiCallLogger(): WorldlineApiCallLoggerInterface
    {
        return new WorldlineApiCallLogger($this->getEntityManager());
    }

    /**
     * @return \ValanticSpryker\Zed\Worldline\Business\ApiCallHandler\ApiCallHandlerInterface
     */
    public function createRefundApiCallHandler(): ApiCallHandlerInterface
    {
        return new CreateRefundApiCallHandler(
            $this->createWorldlineClient(),
            $this->createRefundsMapper(),
            $this->createWorldlineApiLogger(),
        );
    }

    /**
     * @return \ValanticSpryker\Zed\Worldline\Business\ApiCallHandler\ApiCallHandlerInterface
     */
    public function createGetRefundApiCallHandler(): ApiCallHandlerInterface
    {
        return new GetRefundApiCallHandler(
            $this->createWorldlineClient(),
            $this->createRefundsMapper(),
            $this->createWorldlineApiLogger(),
        );
    }

    /**
     * @return \ValanticSpryker\Zed\Worldline\Business\Mapper\HostedCheckoutMapperInterface
     */
    private function createHostedCheckoutMapper(): HostedCheckoutMapperInterface
    {
        return new HostedCheckoutMapper();
    }

    /**
     * @return \ValanticSpryker\Zed\Worldline\Business\Mapper\PaymentProductsMapperInterface
     */
    private function createPaymentProductsMapper(): PaymentProductsMapperInterface
    {
        return new PaymentProductsMapper();
    }

    /**
     * @return \ValanticSpryker\Zed\Worldline\Business\Mapper\RefundsMapperInterface
     */
    private function createRefundsMapper(): RefundsMapperInterface
    {
        return new RefundsMapper();
    }

    /**
     * @return \ValanticSpryker\Zed\Worldline\Business\Logger\WorldlineApiLoggerPluginInterface
     */
    public function createApiSettingsLogger(): WorldlineApiLoggerPluginInterface
    {
        return new ApiSettingsLogger($this->getConfig());
    }

    /**
     * @return \ValanticSpryker\Zed\Worldline\Business\Order\OrderManagerInterface
     */
    public function createOrderManager(): OrderManagerInterface
    {
        return new OrderManager($this->getConfig());
    }

    /**
     * @return \ValanticSpryker\Zed\Worldline\Business\Filter\PaymentMethodsFilterInterface
     */
    public function createPaymentMethodsFilter(): PaymentMethodsFilterInterface
    {
        return new PaymentMethodsFilter(
            $this->createGetPaymentProductsApiCallHandler(),
            $this->createPaymentProductsMapper(),
            $this->getConfig(),
        );
    }

    /**
     * @return \ValanticSpryker\Zed\Worldline\Business\Oms\Command\CommandHandlerInterface
     */
    public function createCreateHostedCheckoutCommandHandler(): CommandHandlerInterface
    {
        return new GenericCommandHandler(
            $this->createCreateHostedCheckoutCommandMapper(),
            $this->createCreateHostedCheckoutApiCallHandler(),
            $this->createCreateHostedCheckoutCommandSaver(),
        );
    }

    /**
     * @return \ValanticSpryker\Zed\Worldline\Business\Oms\Command\CommandHandlerInterface
     */
    public function createGetHostedCheckoutStatusCommandHandler(): CommandHandlerInterface
    {
        return new GenericCommandHandler(
            $this->createGetHostedCheckoutStatusCommandMapper(),
            $this->createGetHostedCheckoutStatusApiCallHandler(),
            $this->createGetHostedCheckoutStatusCommandSaver(),
        );
    }

    /**
     * @return \ValanticSpryker\Zed\Worldline\Business\Mapper\PaymentQuoteMapperInterface
     */
    public function createQuoteMapper(): PaymentQuoteMapperInterface
    {
        return new PaymentQuoteMapper();
    }

    /**
     * @return \ValanticSpryker\Zed\Worldline\Business\Oms\Command\Mapper\WorldlineCommandMapperInterface
     */
    private function createCreateHostedCheckoutCommandMapper(): WorldlineCommandMapperInterface
    {
        return new CreateHostedCheckoutCommandMapper(
            $this->createWorldlineReader(),
            $this->getCreateHostedCheckoutPartMappers(),
        );
    }

    /**
     * @return \ValanticSpryker\Zed\Worldline\Business\Oms\Command\Mapper\WorldlineCommandMapperInterface
     */
    private function createGetHostedCheckoutStatusCommandMapper(): WorldlineCommandMapperInterface
    {
        return new GetHostedCheckoutStatusCommandMapper($this->createWorldlineReader());
    }

    /**
     * @return \ValanticSpryker\Zed\Worldline\Business\Reader\WorldlineReaderInterface
     */
    private function createWorldlineReader(): WorldlineReaderInterface
    {
        return new WorldlineReader($this->getRepository());
    }

    /**
     * @return \ValanticSpryker\Zed\Worldline\Business\Oms\Command\Saver\WorldlineCommandSaverInterface
     */
    private function createCreateHostedCheckoutCommandSaver(): WorldlineCommandSaverInterface
    {
        return new CreateHostedCheckoutCommandSaver($this->createWorldlineWriter());
    }

    /**
     * @return \ValanticSpryker\Zed\Worldline\Business\Oms\Command\Saver\WorldlineCommandSaverInterface
     */
    private function createGetHostedCheckoutStatusCommandSaver(): WorldlineCommandSaverInterface
    {
        return new GetHostedCheckoutStatusCommandSaver($this->createWorldlineWriter());
    }

    /**
     * @return array<\ValanticSpryker\Zed\Worldline\Business\Oms\Command\Mapper\WorldlineCreateHostedCheckoutPartMapperInterface>
     */
    private function getCreateHostedCheckoutPartMappers(): array
    {
        return [
            $this->createHostedCheckoutFkPaymentWorldlinePartMapper(),
            $this->createCreateHostedCheckoutHostedCheckoutSpecificInputPartMapper(),
            $this->createCheckoutCardPaymentSpecificInputPartMapper(),
            $this->createHostedCheckoutFraudFieldsPartMapper(),
            $this->createHostedCheckoutOrderMapper(),
        ];
    }

    /**
     * @return \ValanticSpryker\Zed\Worldline\Business\Oms\Command\Mapper\WorldlineCreateHostedCheckoutHostedCheckoutSpecificInputPartMapper
     */
    protected function createCreateHostedCheckoutHostedCheckoutSpecificInputPartMapper(): WorldlineCreateHostedCheckoutHostedCheckoutSpecificInputPartMapper
    {
        return new WorldlineCreateHostedCheckoutHostedCheckoutSpecificInputPartMapper($this->getConfig(), $this->getQueryContainer());
    }

    /**
     * @return \ValanticSpryker\Zed\Worldline\Business\Oms\Command\Mapper\WorldlineCreateHostedCheckoutCardPaymentSpecificInputPartMapper
     */
    protected function createCheckoutCardPaymentSpecificInputPartMapper(): WorldlineCreateHostedCheckoutCardPaymentSpecificInputPartMapper
    {
        return new WorldlineCreateHostedCheckoutCardPaymentSpecificInputPartMapper($this->getConfig());
    }

    /**
     * @return \ValanticSpryker\Zed\Worldline\Business\Oms\Command\Mapper\WorldlineCreateHostedCheckoutFraudFieldsPartMapper
     */
    protected function createHostedCheckoutFraudFieldsPartMapper(): WorldlineCreateHostedCheckoutFraudFieldsPartMapper
    {
        return new WorldlineCreateHostedCheckoutFraudFieldsPartMapper($this->getConfig());
    }

    /**
     * @return \ValanticSpryker\Zed\Worldline\Business\Oms\Command\Mapper\WorldlineCreateHostedCheckoutOrderPartMapper
     */
    protected function createHostedCheckoutOrderMapper(): WorldlineCreateHostedCheckoutOrderPartMapper
    {
        return new WorldlineCreateHostedCheckoutOrderPartMapper();
    }

    /**
     * @return \ValanticSpryker\Zed\Worldline\Business\Mapper\WorldlineMapperInterface
     */
    private function createTokenMapper(): WorldlineMapperInterface
    {
        return new WorldlineTokenMapper();
    }

    /**
     * @return \ValanticSpryker\Zed\Worldline\Business\Writer\WorldlineWriterInterface
     */
    private function createWorldlineWriter(): WorldlineWriterInterface
    {
        return new WorldlineWriter($this->getEntityManager(), $this->getQueryContainer(), $this->createWorldlineTimestampConverter(), $this->getConfig());
    }

    /**
     * @return \ValanticSpryker\Zed\Worldline\Business\Payment\Hook\PostSaveHookInterface
     */
    public function createPostSaveHook(): PostSaveHookInterface
    {
        return new PostSaveHook($this->createWorldlineReader());
    }

    /**
     * @return \ValanticSpryker\Zed\Worldline\Business\Oms\Command\Mapper\WorldlineCreateHostedCheckoutPartMapperInterface
     */
    private function createHostedCheckoutFkPaymentWorldlinePartMapper(): WorldlineCreateHostedCheckoutPartMapperInterface
    {
        return new WorldlineCreateHostedCheckoutFkPaymentWorldlinePartMapper();
    }

    /**
     * @return \ValanticSpryker\Zed\Worldline\Business\Logger\WorldlineApiLoggerPluginInterface
     */
    public function createFkPaymentWorldlineLogger(): WorldlineApiLoggerPluginInterface
    {
        return new FkPaymentWorldlineApiLogger();
    }

    /**
     * @return \ValanticSpryker\Zed\Worldline\Business\Oms\Condition\HostedCheckoutCreatedCheckerInterface
     */
    public function createHostedCheckoutCreatedChecker(): HostedCheckoutCreatedCheckerInterface
    {
        return new HostedCheckoutCreatedChecker($this->createWorldlineReader());
    }

    /**
     * @return \ValanticSpryker\Zed\Worldline\Business\Oms\Condition\HostedCheckoutFailedCheckerInterface
     */
    public function createHostedCheckoutFailedChecker(): HostedCheckoutFailedCheckerInterface
    {
        return new HostedCheckoutFailedChecker($this->createWorldlineReader());
    }

    /**
     * @return \ValanticSpryker\Zed\Worldline\Business\Logger\WorldlineApiLoggerPluginInterface
     */
    public function createCreateHostedCheckoutLogger(): WorldlineApiLoggerPluginInterface
    {
        return new CreateHostedCheckoutApiLogger();
    }

    /**
     * @return \ValanticSpryker\Zed\Worldline\Business\Logger\WorldlineApiLoggerPluginInterface
     */
    public function createHttpStatusApiLogger(): WorldlineApiLoggerPluginInterface
    {
        return new HttpStatusApiLogger();
    }

    /**
     * @return \ValanticSpryker\Zed\Worldline\Business\Logger\WorldlineApiLoggerPluginInterface
     */
    public function createGetPaymentProductsApiLogger(): WorldlineApiLoggerPluginInterface
    {
        return new GetPaymentProductsApiLogger();
    }

    /**
     * @return \ValanticSpryker\Zed\Worldline\Business\Logger\WorldlineApiLoggerPluginInterface
     */
    public function createGetHostedCheckoutStatusLogger(): WorldlineApiLoggerPluginInterface
    {
        return new GetHostedCheckoutStatusLogger($this->createWorldlineTimestampConverter());
    }

    /**
     * @return \ValanticSpryker\Zed\Worldline\Business\Logger\WorldlineApiLoggerPluginInterface
     */
    private function createGetPaymentApiLogger(): WorldlineApiLoggerPluginInterface
    {
        return new GetPaymentApiLogger($this->createWorldlineTimestampConverter());
    }

    /**
     * @return \ValanticSpryker\Zed\Worldline\Business\Logger\WorldlineApiLoggerPluginInterface
     */
    private function createApprovePaymentApiLogger(): WorldlineApiLoggerPluginInterface
    {
        return new ApprovePaymentApiLogger($this->createWorldlineTimestampConverter());
    }

    /**
     * @return \ValanticSpryker\Zed\Worldline\Business\Logger\WorldlineApiLoggerPluginInterface
     */
    private function createDeleteTokenApiLogger(): WorldlineApiLoggerPluginInterface
    {
        return new DeleteTokenApiLogger();
    }

    /**
     * @return \ValanticSpryker\Zed\Worldline\Business\Logger\WorldlineApiLoggerPluginInterface
     */
    public function createApiErrorLogger(): WorldlineApiLoggerPluginInterface
    {
        return new ApiErrorLogger();
    }

    /**
     * @return \ValanticSpryker\Zed\Worldline\Business\Oms\Condition\HostedCheckoutStatusTimeoutCheckerInterface
     */
    public function createHostedCheckoutStatusTimeoutChecker(): HostedCheckoutStatusTimeoutCheckerInterface
    {
        return new HostedCheckoutStatusTimeoutChecker($this->createWorldlineReader(), $this->getConfig());
    }

    /**
     * @return \ValanticSpryker\Zed\Worldline\Business\Oms\Condition\HostedCheckoutPaymentWasCreatedCheckerInterface
     */
    public function createHostedCheckoutPaymentCreatedChecker(): HostedCheckoutPaymentWasCreatedCheckerInterface
    {
        return new HostedCheckoutPaymentWasCreatedChecker($this->createWorldlineReader());
    }

    /**
     * @return \ValanticSpryker\Zed\Worldline\Business\Oms\Condition\HostedCheckoutStatusIsCancelledCheckerInterface
     */
    public function createHostedCheckoutStatusCancelledChecker(): HostedCheckoutStatusIsCancelledCheckerInterface
    {
        return new HostedCheckoutStatusIsCancelledChecker($this->createWorldlineReader());
    }

    /**
     * @return \ValanticSpryker\Zed\Worldline\Business\Order\OrderHydratorInterface
     */
    public function createHostedCheckoutOrderHydrator(): OrderHydratorInterface
    {
        return new OrderHydrator(
            $this->createWorldlineReader(),
            $this->createGetHostedCheckoutStatusCommandHandler(),
        );
    }

    /**
     * @return \ValanticSpryker\Zed\Worldline\Business\Queue\EventMessageProcessorInterface
     */
    public function createEventMessageProcessor(): EventMessageProcessorInterface
    {
        return new EventMessageProcessor(
            $this->createWorldlineWriter(),
            $this->createWebhooksHelper(),
            $this->createWebhookEventMapper(),
            $this->getConfig(),
            $this->getEventProcessors(),
            $this->getUtilEncodingService(),
        );
    }

    /**
     * @return \Ingenico\Connect\Sdk\Webhooks\WebhooksHelper
     */
    private function createWebhooksHelper(): WebhooksHelper
    {
        return new WebhooksHelper($this->createMemorySecretKeyStore());
    }

    /**
     * @return \ValanticSpryker\Zed\Worldline\Business\Mapper\WebhookEventMapperInterface
     */
    private function createWebhookEventMapper(): WebhookEventMapperInterface
    {
        return new WebhookEventMapper();
    }

    /**
     * @return \Ingenico\Connect\Sdk\Webhooks\InMemorySecretKeyStore
     */
    private function createMemorySecretKeyStore(): InMemorySecretKeyStore
    {
        return new InMemorySecretKeyStore($this->getConfig()->getSecretKeyStore());
    }

    /**
     * @return array<\ValanticSpryker\Zed\Worldline\Business\EventProcessor\EventProcessorInterface>
     */
    private function getEventProcessors(): array
    {
        return [
            $this->createPaymentEventProcessor(),
            $this->createTokenEventProcessor(),
        ];
    }

    /**
     * @return \ValanticSpryker\Zed\Worldline\Business\EventProcessor\EventProcessorInterface
     */
    private function createPaymentEventProcessor(): EventProcessorInterface
    {
        return new PaymentEventProcessor($this->createWorldlineWriter());
    }

    /**
     * @return \ValanticSpryker\Zed\Worldline\Business\EventProcessor\EventProcessorInterface
     */
    private function createTokenEventProcessor(): EventProcessorInterface
    {
        return new TokenEventProcessor($this->createWorldlineWriter());
    }

    /**
     * @return \ValanticSpryker\Zed\Worldline\Business\Oms\Condition\PaymentGuaranteedCheckerInterface
     */
    public function createPaymentGuaranteedChecker(): PaymentGuaranteedCheckerInterface
    {
        return new PaymentGuaranteedChecker($this->createWorldlineReader());
    }

    /**
     * @return \ValanticSpryker\Zed\Worldline\Business\Oms\Condition\PaymentCancelledCheckerInterface
     */
    public function createPaymentCancelledChecker(): PaymentCancelledCheckerInterface
    {
        return new PaymentCancelledChecker($this->createWorldlineReader());
    }

    /**
     * @return \ValanticSpryker\Zed\Worldline\Business\Oms\Condition\PaymentRejectedCheckerInterface
     */
    public function createPaymentRejectedChecker(): PaymentRejectedCheckerInterface
    {
        return new PaymentRejectedChecker($this->createWorldlineReader());
    }

    /**
     * @return \ValanticSpryker\Zed\Worldline\Business\Oms\Command\CommandHandlerInterface
     */
    public function createGetPaymentStatusCommandHandler(): CommandHandlerInterface
    {
        return new GenericCommandHandler(
            $this->createGetPaymentStatusCommandMapper(),
            $this->createGetPaymentApiCallHandler(),
            $this->createGetPaymentStatusCommandSaver(),
        );
    }

    /**
     * @return \ValanticSpryker\Zed\Worldline\Business\Oms\Command\Mapper\WorldlineCommandMapperInterface
     */
    private function createGetPaymentStatusCommandMapper(): WorldlineCommandMapperInterface
    {
        return new GetPaymentStatusCommandMapper($this->createWorldlineReader());
    }

    /**
     * @return \ValanticSpryker\Zed\Worldline\Business\Oms\Command\Saver\WorldlineCommandSaverInterface
     */
    private function createGetPaymentStatusCommandSaver(): WorldlineCommandSaverInterface
    {
        return new GetPaymentStatusCommandSaver();
    }

    /**
     * @return \ValanticSpryker\Zed\Worldline\Business\Oms\Command\CommandHandlerInterface
     */
    public function createApprovePaymentCommandHandler(): CommandHandlerInterface
    {
        return new GenericCommandHandler(
            $this->createApprovePaymentCommandMapper(),
            $this->createApprovePaymentApiCallHandler(),
            $this->createApprovePaymentCommandSaver(),
        );
    }

    /**
     * @return \ValanticSpryker\Zed\Worldline\Business\Oms\Command\Mapper\WorldlineCommandMapperInterface
     */
    private function createApprovePaymentCommandMapper(): WorldlineCommandMapperInterface
    {
        return new ApprovePaymentCommandMapper($this->createWorldlineReader());
    }

    /**
     * @return \ValanticSpryker\Zed\Worldline\Business\Oms\Command\Saver\WorldlineCommandSaverInterface
     */
    private function createApprovePaymentCommandSaver(): WorldlineCommandSaverInterface
    {
        return new ApprovePaymentCommandSaver();
    }

    /**
     * @return \ValanticSpryker\Zed\Worldline\Business\Oms\Condition\PaymentCapturedCheckerInterface
     */
    public function createPaymentCapturedChecker(): PaymentCapturedCheckerInterface
    {
        return new PaymentCapturedChecker($this->createWorldlineReader());
    }

    /**
     * @return \ValanticSpryker\Zed\Worldline\Business\Oms\Condition\PaymentCaptureRejectedCheckerInterface
     */
    public function createPaymentCaptureRejectedChecker(): PaymentCaptureRejectedCheckerInterface
    {
        return new PaymentCaptureRejectedChecker($this->createWorldlineReader());
    }

    /**
     * @return \ValanticSpryker\Zed\Worldline\Business\Timestamp\WorldlineTimestampConverterInterface
     */
    private function createWorldlineTimestampConverter(): WorldlineTimestampConverterInterface
    {
        return new WorldlineTimestampConverter($this->getConfig());
    }

    /**
     * @return \Spryker\Service\UtilEncoding\UtilEncodingServiceInterface
     */
    private function getUtilEncodingService(): UtilEncodingServiceInterface
    {
        return $this->getProvidedDependency(WorldlineDependencyProvider::SERVICE_UTIL_ENCODING);
    }

    /**
     * @return \ValanticSpryker\Zed\Worldline\Business\Oms\Condition\PaymentCaptureTimedOutCheckerInterface
     */
    public function createPaymentCaptureTimedOutChecker(): PaymentCaptureTimedOutCheckerInterface
    {
        return new PaymentCaptureTimedOutChecker($this->createWorldlineReader(), $this->getOmsFacade(), $this->getConfig());
    }

    /**
     * @return \Spryker\Zed\Oms\Business\OmsFacadeInterface
     */
    private function getOmsFacade(): OmsFacadeInterface
    {
        return $this->getProvidedDependency(WorldlineDependencyProvider::FACADE_OMS);
    }

    /**
     * @return \Spryker\Zed\Payment\Business\PaymentFacadeInterface
     */
    private function getPaymentFacade(): PaymentFacadeInterface
    {
        return $this->getProvidedDependency(WorldlineDependencyProvider::FACADE_PAYMENT);
    }

    /**
     * @return \Spryker\Zed\Customer\Business\CustomerFacadeInterface
     */
    private function getCustomerFacade(): CustomerFacadeInterface
    {
        return $this->getProvidedDependency(WorldlineDependencyProvider::FACADE_CUSTOMER);
    }

    /**
     * @return \ValanticSpryker\Zed\Worldline\Business\Token\Reader\TokenReaderInterface
     */
    public function createTokenReader(): TokenReaderInterface
    {
        return new TokenReader(
            $this->getPaymentFacade(),
            $this->getCustomerFacade(),
            $this->getRepository(),
        );
    }

    /**
     * @return \ValanticSpryker\Zed\Worldline\Business\Token\DeletedWorldlineTokenRemoverInterface
     */
    public function createDeletedWorldlineTokenRemover(): DeletedWorldlineTokenRemoverInterface
    {
        return new DeletedWorldlineTokenRemover($this->getConfig(), $this->getEntityManager());
    }

    /**
     * @return \ValanticSpryker\Zed\Worldline\Business\Token\WorldlinePaymentTokenDeleterInterface
     */
    public function createPaymentTokenDeleter(): WorldlinePaymentTokenDeleterInterface
    {
        return new WorldlinePaymentTokenDeleter(
            $this->createDeleteTokenApiCallHandler(),
            $this->createWorldlineWriter(),
            $this->getQueryContainer(),
            $this->getCustomerFacade(),
        );
    }

    /**
     * @return \ValanticSpryker\Zed\Worldline\Business\ApiCallHandler\ApiCallHandlerInterface
     */
    private function createDeleteTokenApiCallHandler(): ApiCallHandlerInterface
    {
        return new DeleteTokenApiCallHandler($this->createWorldlineClient(), $this->createTokenMapper(), $this->createWorldlineApiLogger());
    }
}
