# your package

[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%208.0-8892BF.svg)](https://php.net/)
[![coverage report](https://gitlab.nxs360.com/packages/php/spryker/worldline/badges/master/pipeline.svg)](https://gitlab.nxs360.com/packages/php/spryker/worldline/-/pipelines?page=1&scope=all&ref=master)
[![coverage report](https://gitlab.nxs360.com/packages/php/spryker/worldline/badges/master/coverage.svg)](https://packages.gitlab-pages.nxs360.com/php/spryker/worldline)

# Description
 - Adds the integration of the worldline PSP provider platform Global Collect. See https://epayments-api.developer-ingenico.com/s2sapi/v1/en_US/php/concepts.html?paymentPlatform=GLOBALCOLLECT

# Install
- https://gitlab.nxs360.com/groups/packages/php/spryker/-/packages

# Reference implementation
- https://gitlab.nxs360.com/team-lr/glue-api

# HowTos Cli

PHP Container: `docker run -it --rm --name my-running-script -v "$PWD":/data spryker/php:latest bash`

Run Tests: `codecept run --env standalone`

Fixer: `vendor/bin/phpcbf --standard=phpcs.xml --report=full src/ValanticSpryker/`

Disable opcache: `mv /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini /usr/local/etc/php/conf.d/docker-php-ext-opcache.iniold`

XDEBUG:
- `ip addr | grep '192.'`
- `$docker-php-ext-enable xdebug`
- configure phpstorm (add 127.0.0.1 phpstorm server with name valantic)
- `$PHP_IDE_CONFIG=serverName=valantic php -dxdebug.mode=debug -dxdebug.client_host=192.168.87.39 -dxdebug.start_with_request=yes ./vendor/bin/codecept run --env standalone`

- Run Tests with coverage: `XDEBUG_MODE=coverage vendor/bin/codecept run --env standalone --coverage --coverage-xml --coverage-html`

# HowTo Setup new Repo
 - create new project (https://gitlab.nxs360.com/projects/new?namespace_id=461#blank_project)
   - visibility -> Internal
 - push in repo boilerplate copied of worldline (https://gitlab.nxs360.com/packages/php/spryker/worldline)
 - search for string `worldline` and add your descriptions
 - add your custom code / copy in your code / rename namespace to ValanticSpryker

# use nodejs
 - docker run -it --rm --name my-running-script -v "$PWD":/data node:18 bash

# Howto integrate into your project?
## Prerequisites
* Spryker Core	202212.0 
* Cart	202212.0 
* Product	202212.0 
* Payments	202212.0 
* Order Management	202212.0

## Steps to integrate

### install the required modules using composer

    composer require valantic-spryker/worldline --update-with-dependencies
   

Verification:

| Module     | Expected Directory                |
|------------|-----------------------------------|
 |  Worldline | vendor/valantic-spryker/worldline |

### Set up configuration

#### Project configuration
extend your Project config with the following configuration

##### config/Shared/config_default.php

###### 1. Add Queue configuration for webhook events

in config/shared/config_default.php

      use ValanticSpryker\Shared\Worldline\WorldlineConstants;
      use ValanticSpryker\Zed\Worldline\WorldlineConfig;
      
      $config[QueueConstants::QUEUE_ADAPTER_CONFIGURATION] = [
        .
        .
        WorldlineWebhookConstants::WORLDLINE_WEBHOOK_EVENT_QUEUE_NAME => [
            QueueConfig::CONFIG_QUEUE_ADAPTER => RabbitMqAdapter::class,
            QueueConfig::CONFIG_MAX_WORKER_NUMBER => 1,
        ],
        .
        .
      ];

in Pyz/Zed/Queue/QueueConfig.php

    /**
     * @return array
     */
    protected function getQueueReceiverOptions(): array
    {
        return [
            .
            .
            WorldlineWebhookConstants::WORLDLINE_WEBHOOK_EVENT_QUEUE_NAME => [
                static::RABBITMQ => $this->getRabbitMqQueueConsumerOptions(),
            ],
            .
            .
        ];
    }

###### 2. Add OMS Mappings (Example)
    
in config/shared/config_default.php      

      // ----------------------------------------------------------------------------
      // ------------------------------ OMS -----------------------------------------
      // ----------------------------------------------------------------------------

      
      $config[SalesConstants::PAYMENT_METHOD_STATEMACHINE_MAPPING] = [
         WorldlineConfig::PAYMENT_METHOD_CREDIT_CARD_VISA => 'DelayedPayment01',
         WorldlineConfig::PAYMENT_METHOD_CREDIT_CARD_MASTER_CARD => 'DelayedPayment01',
         WorldlineConfig::PAYMENT_METHOD_CREDIT_CARD_AMERICAN_EXPRESS => 'DelayedPayment01',
         WorldlineConfig::PAYMENT_METHOD_PAYPAL => 'Payment01',
         CheckoutRestApiConfig::LR_INTERNAL_PAYMENT_METHOD_CASH_ON_DELIVERY => 'NoPayment01',
         CheckoutRestApiConfig::LR_INTERNAL_PAYMENT_METHOD_INVOICE => 'NoPayment01',
      ];

in config/shared/common/config_oms-development

    $config[SalesConstants::PAYMENT_METHOD_STATEMACHINE_MAPPING] = array_replace(
        $config[SalesConstants::PAYMENT_METHOD_STATEMACHINE_MAPPING],
        [
            DummyPaymentConfig::PAYMENT_METHOD_INVOICE => 'DummyPayment01',
            DummyPaymentConfig::PAYMENT_METHOD_CREDIT_CARD => 'DummyPayment01',
            DummyMarketplacePaymentConfig::PAYMENT_METHOD_DUMMY_MARKETPLACE_PAYMENT_INVOICE => 'MarketplacePayment01',
            NopaymentConfig::PAYMENT_PROVIDER_NAME => 'Nopayment01',
            GiftCardConfig::PROVIDER_NAME => 'DummyPayment01',
            WorldlineConfig::PAYMENT_METHOD_CREDIT_CARD_VISA => 'DelayedPayment01',
            WorldlineConfig::PAYMENT_METHOD_CREDIT_CARD_MASTER_CARD => 'DelayedPayment01',
            WorldlineConfig::PAYMENT_METHOD_CREDIT_CARD_AMERICAN_EXPRESS => 'DelayedPayment01',
            WorldlineConfig::PAYMENT_METHOD_PAYPAL => 'DelayedPayment01',
        ],
    );

      
###### 3. Configure Worldline Connector


      // ----------------------------------------------------------------------------
      // ----------------------- Worldline Connector --------------------------------
      // ----------------------------------------------------------------------------
      $config[WorldlineConstants::WORLDLINE_API_KEY_ID] = getenv('WORLDLINE_API_KEY_ID') ?: '';
      $config[WorldlineConstants::WORLDLINE_API_SECRET] = getenv('WORLDLINE_API_SECRET') ?: '';
      $config[WorldlineConstants::WORLDLINE_API_ENDPOINT] = getenv('WORLDLINE_API_ENDPOINT') ?: '';
      $config[WorldlineConstants::WORLDLINE_API_INTEGRATOR] = 'valantic CEC Deutschland GmbH';
      $config[WorldlineConstants::WORLDLINE_API_MERCHANT_ID] = getenv('WORLDLINE_API_MERCHANT_ID') ?: ''; 
      $config[WorldlineConstants::WORLDLINE_API_COUNTRY_ISO2CODE] = 'DE'; // needs to be configured for each store
      $config[WorldlineConstants::WORLDLINE_WEBHOOK_KEY] = getenv('WORLDLINE_WEBHOOK_KEY') ?: '';
      $config[WorldlineConstants::WORLDLINE_WEBHOOK_SECRET] = getenv('WORLDLINE_WEBHOOK_SECRET') ?: '';
      
      $config[WorldlineConstants::WORLDLINE_SECRETS_KEY_STORE] = [
         $config[WorldlineConstants::WORLDLINE_API_KEY_ID] => $config[WorldlineConstants::WORLDLINE_API_SECRET],
         $config[WorldlineConstants::WORLDLINE_WEBHOOK_KEY] => $config[WorldlineConstants::WORLDLINE_WEBHOOK_SECRET],
      ];
      
      $config[WorldlineConstants::CONNECT_TIMEOUT] = 5; // in seconds
      $config[WorldlineConstants::READ_TIMEOUT] = 10; // in seconds
      $config[WorldlineConstants::WORLDLINE_TIMEZONE] = 'Europe/Paris';
      
      $config[WorldlineConstants::WORLDLINE_IS_DEVELOPMENT_WITHOUT_WORLDLINE_BACKEND] = false;
      $config[WorldlineConstants::WORLDLINE_MAX_HOURS_TO_WAIT_BEFORE_CAPTURE_TIMES_OUT] = 24;
      $config[WorldlineConstants::WORLDLINE_LIMIT_OF_DELETED_TOKENS_TO_REMOVE_AT_A_TIME] = 5;
      
      $config[WorldlineWebhookConstants::WORLDLINE_WEBHOOK_ENABLED] = true;
      $config[WorldlineWebhookConstants::WORLDLINE_WEBHOOK_DEBUG_ENABLED] = false;

###### 4. Register Checkout Plugins

In \Pyz\Zed\Checkout\CheckoutDependencyProvider:
    
    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return array<\Spryker\Zed\Checkout\Dependency\Plugin\CheckoutSaveOrderInterface>|array<\Spryker\Zed\CheckoutExtension\Dependency\Plugin\CheckoutDoSaveOrderInterface>
     */
    protected function getCheckoutOrderSavers(Container $container): array
    {
        return [
            new CustomerOrderSavePlugin(),
            /*
             * Plugins
             * `OrderSaverPlugin`,
             * `OrderTotalsSaverPlugin`,
             * `SalesOrderShipmentSavePlugin`,
             * `OrderItemsSaverPlugin`,
             * must be enabled in the strict order.
             */
            .
            .
            .
            new WorldlineCheckoutDoSaveOrderPlugin(),
        ];
    }
    .
    .
    .
    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return array<\Spryker\Zed\CheckoutExtension\Dependency\Plugin\CheckoutPostSaveInterface>
     */
    protected function getCheckoutPostHooks(Container $container): array
    {
        return [
            .
            .
            .
            new WorldlineCheckoutPostSavePlugin(),
            .
            .
            .
        ];
    }
In \Pyz\Zed\CheckoutRestApi

    /**
    * @return array<\Spryker\Zed\CheckoutRestApiExtension\Dependency\Plugin\QuoteMapperPluginInterface>
    */
    protected function getQuoteMapperPlugins(): array
    {
        return [
            .
            .
            new WorldlinePaymentQuoteMapperPlugin(),
            .
            .
        ];
    }

###### 5. Register Order Plugins

In \Pyz\Zed\Sales\SalesDependencyProvider

    /**
     * @return array<\Spryker\Zed\SalesExtension\Dependency\Plugin\OrderExpanderPluginInterface>
     */
    protected function getOrderHydrationPlugins(): array
    {
        return [
            .
            .
            new HostedCheckoutStatusItemStateUpdaterOrderExpanderPlugin(),
            .
            .
        ];
    }

###### 6. Register Payment Plugins

In \Pyz\Zed\Payment\PaymentDependencyProvider

    /**
     * @return array<\Spryker\Zed\PaymentExtension\Dependency\Plugin\PaymentMethodFilterPluginInterface>
     */
    protected function getPaymentMethodFilterPlugins(): array
    {
        return [
            .
            .
            new WorldlinePaymentMethodFilterPlugin(),
        ];
    }

###### 7. Register WorldlineWebhooks Plugins

In \Pyz\Zed\EventDispatcher\EventDispatcherDependencyProvider

    /**
     * @return array<\Spryker\Shared\EventDispatcherExtension\Dependency\Plugin\EventDispatcherPluginInterface>
     */
    protected function getBackendApiEventDispatcherPlugins(): array
    {
        return [
            .
            .
            new WorldlineWebhookEventDispatcherPlugin(),
            .
            .
        ];
    }

And in \Pyz\Zed\Router\RouterDependencyProvider

    /**
     * @return array<\Spryker\Zed\RouterExtension\Dependency\Plugin\RouterPluginInterface>
     */
    protected function getBackendApiRouterPlugins(): array
    {
        return [
            .
            .
            new WorldlineWebhookRouterPlugin(),
        ];
    }

###### 8. Register Queue Processor Plugin

In \Pyz\Zed\Queue\QueueDependencyProvider

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return array<\Spryker\Zed\Queue\Dependency\Plugin\QueueMessageProcessorPluginInterface>
     */
    protected function getProcessorMessagePlugins(Container $container): array
    {
        return [
            .
            .
            WorldlineWebhookConstants::WORLDLINE_WEBHOOK_EVENT_QUEUE_NAME => new WorldlineWebhookEventQueueMessageProcessorPlugin(),
            .
            .
        ];
    }

###### 9. Add Worldline payment methods

In \Pyz\Glue\CheckoutRestApi\CheckoutRestApiConfig

    private const WORLDLINE_PAYMENT_PROVIDER_NAME = 'Worldline';

    /**
     * @uses \Pyz\Shared\Worldline\WorldlineConfig::PAYMENT_METHOD_NAME_CREDIT_CARD_VISA
     *
     * @var string
     */
    public const WORLDLINE_PAYMENT_METHOD_NAME_CREDIT_CARD_VISA = 'Visa';

    /**
     * @uses \Pyz\Shared\Worldline\WorldlineConfig::PAYMENT_METHOD_NAME_CREDIT_CARD_MASTER_CARD
     *
     * @var string
     */
    public const WORLDLINE_PAYMENT_METHOD_NAME_CREDIT_CARD_MASTER_CARD = 'Master Card';

    /**
     * @uses \Pyz\Shared\Worldline\WorldlineConfig::PAYMENT_METHOD_NAME_CREDIT_CARD_AMERICAN_EXPRESS
     *
     * @var string
     */
    public const WORLDLINE_PAYMENT_METHOD_NAME_CREDIT_CARD_AMERICAN_EXPRESS = 'American Express';

    /**
     * @uses \Pyz\Shared\Worldline\WorldlineConfig::PAYMENT_METHOD_NAME_PAYPAL
     *
     * @var string
     */
    public const WORLDLINE_PAYMENT_METHOD_NAME_PAYPAL = 'paypal';

    /**
     * @uses \Pyz\Shared\Worldline\WorldlineConfig::::PAYMENT_METHOD_CREDIT_CARD_VISA
     *
     * @var string
     */
    public const WORLDLINE_PAYMENT_METHOD_CREDIT_CARD_VISA = 'worldlineCreditCardVisa';

    /**
     * @uses \Pyz\Shared\Worldline\WorldlineConfig::::PAYMENT_METHOD_CREDIT_CARD_MASTER_CARD
     *
     * @var string
     */
    public const WORLDLINE_PAYMENT_METHOD_CREDIT_CARD_MASTER_CARD = 'worldlineCreditCardMasterCard';

    /**
     * @uses \Pyz\Shared\Worldline\WorldlineConfig::::PAYMENT_METHOD_CREDIT_CARD_AMERICAN_EXPRESS
     *
     * @var string
     */
    public const WORLDLINE_PAYMENT_METHOD_CREDIT_CARD_AMERICAN_EXPRESS = 'worldlineCreditCardAmericanExpress';

    /**
     * @uses \Pyz\Shared\Worldline\WorldlineConfig::::PAYMENT_METHOD_PAYPAL
     *
     * @var string
     */
    public const WORLDLINE_PAYMENT_METHOD_PAYPAL = 'worldlinePaypal';

    /**
     * @return array<array<string>>
     */
    public function getPaymentProviderMethodToStateMachineMapping(): array
    {
        return [
            .
            .
            self::WORLDLINE_PAYMENT_PROVIDER_NAME => [
                static::WORLDLINE_PAYMENT_METHOD_NAME_CREDIT_CARD_VISA => static::WORLDLINE_PAYMENT_METHOD_CREDIT_CARD_VISA,
                static::WORLDLINE_PAYMENT_METHOD_NAME_CREDIT_CARD_MASTER_CARD => static::WORLDLINE_PAYMENT_METHOD_CREDIT_CARD_MASTER_CARD,
                static::WORLDLINE_PAYMENT_METHOD_NAME_CREDIT_CARD_AMERICAN_EXPRESS => static::WORLDLINE_PAYMENT_METHOD_CREDIT_CARD_AMERICAN_EXPRESS,
                static::WORLDLINE_PAYMENT_METHOD_NAME_PAYPAL => static::WORLDLINE_PAYMENT_METHOD_PAYPAL,
            ],
            .
            .
        ];
    }

Add required fields in \Pyz\Glue\CheckoutRestApi

    /**
     * @var array<string, array<string>>
     */
    protected const PAYMENT_METHOD_REQUIRED_FIELDS = [
        'dummyPaymentInvoice' => ['dummyPaymentInvoice.dateOfBirth'],
        'dummyPaymentCreditCard' => [
            'dummyPaymentCreditCard.cardType',
            'dummyPaymentCreditCard.cardNumber',
            'dummyPaymentCreditCard.nameOnCard',
            'dummyPaymentCreditCard.cardExpiresMonth',
            'dummyPaymentCreditCard.cardExpiresYear',
            'dummyPaymentCreditCard.cardSecurityCode',
        ],
        WorldlineConfig::PAYMENT_METHOD_CREDIT_CARD_VISA => [
            'paymentHostedCheckout.returnUrl',
            'paymentHostedCheckout.customerIpAddress',
            'paymentHostedCheckout.customerSelectedLocale',
            'paymentHostedCheckout.customerTimezone',
            'paymentHostedCheckout.customerUserAgent',
            'paymentHostedCheckout.customerColorDepth',
            'paymentHostedCheckout.customerJavaEnabled',
            'paymentHostedCheckout.customerScreenHeight',
            'paymentHostedCheckout.customerScreenWidth',
            'paymentHostedCheckout.customerBrowserLocale',
            'paymentHostedCheckout.customerTimezoneOffset',
        ],
        WorldlineConfig::PAYMENT_METHOD_CREDIT_CARD_MASTER_CARD => [
            'paymentHostedCheckout.returnUrl',
            'paymentHostedCheckout.customerIpAddress',
            'paymentHostedCheckout.customerSelectedLocale',
            'paymentHostedCheckout.customerTimezone',
            'paymentHostedCheckout.customerUserAgent',
            'paymentHostedCheckout.customerColorDepth',
            'paymentHostedCheckout.customerJavaEnabled',
            'paymentHostedCheckout.customerScreenHeight',
            'paymentHostedCheckout.customerScreenWidth',
            'paymentHostedCheckout.customerBrowserLocale',
            'paymentHostedCheckout.customerTimezoneOffset',
        ],
        WorldlineConfig::PAYMENT_METHOD_CREDIT_CARD_AMERICAN_EXPRESS => [
            'paymentHostedCheckout.returnUrl',
            'paymentHostedCheckout.customerIpAddress',
            'paymentHostedCheckout.customerSelectedLocale',
            'paymentHostedCheckout.customerTimezone',
            'paymentHostedCheckout.customerUserAgent',
            'paymentHostedCheckout.customerColorDepth',
            'paymentHostedCheckout.customerJavaEnabled',
            'paymentHostedCheckout.customerScreenHeight',
            'paymentHostedCheckout.customerScreenWidth',
            'paymentHostedCheckout.customerBrowserLocale',
            'paymentHostedCheckout.customerTimezoneOffset',
        ],
        WorldlineConfig::PAYMENT_METHOD_PAYPAL => [
            'paymentHostedCheckout.returnUrl',
            'paymentHostedCheckout.customerIpAddress',
            'paymentHostedCheckout.customerSelectedLocale',
            'paymentHostedCheckout.customerTimezone',
            'paymentHostedCheckout.customerUserAgent',
            'paymentHostedCheckout.customerColorDepth',
            'paymentHostedCheckout.customerJavaEnabled',
            'paymentHostedCheckout.customerScreenHeight',
            'paymentHostedCheckout.customerScreenWidth',
            'paymentHostedCheckout.customerBrowserLocale',
            'paymentHostedCheckout.customerTimezoneOffset',
        ],
    ];


In \Pyz\Glue\PaymentRestApi\PaymentRestApiConfig

Set the priority order of payment methods (example)

    /**
     * @var array<string, int>
     */
    protected const PAYMENT_METHOD_PRIORITY = [
        WorldlineConfig::PAYMENT_METHOD_CREDIT_CARD_VISA => 1,
        WorldlineConfig::PAYMENT_METHOD_CREDIT_CARD_MASTER_CARD => 2,
        WorldlineConfig::PAYMENT_METHOD_CREDIT_CARD_AMERICAN_EXPRESS => 3,
        WorldlineConfig::PAYMENT_METHOD_PAYPAL => 4,
        DummyPaymentConfig::PAYMENT_METHOD_INVOICE => 5,
        DummyPaymentConfig::PAYMENT_METHOD_CREDIT_CARD => 6,
    ];

Add the required fields for Worldline payment methods, too

    /**
     * @var array<string, array>
     */
    protected const PAYMENT_METHOD_REQUIRED_FIELDS = [
        .
        .
        WorldlineConfig::PROVIDER_NAME => [
            WorldlineConfig::PAYMENT_METHOD_CREDIT_CARD_VISA => [
                'paymentHostedCheckout.returnUrl',
                'paymentHostedCheckout.customerIpAddress',
                'paymentHostedCheckout.customerSelectedLocale',
                'paymentHostedCheckout.customerTimezone',
                'paymentHostedCheckout.customerUserAgent',
                'paymentHostedCheckout.customerColorDepth',
                'paymentHostedCheckout.customerJavaEnabled',
                'paymentHostedCheckout.customerScreenHeight',
                'paymentHostedCheckout.customerScreenWidth',
                'paymentHostedCheckout.customerBrowserLocale',
                'paymentHostedCheckout.customerTimezoneOffset',
            ],
            WorldlineConfig::PAYMENT_METHOD_NAME_CREDIT_CARD_VISA => [
                'paymentHostedCheckout.returnUrl',
                'paymentHostedCheckout.customerIpAddress',
                'paymentHostedCheckout.customerSelectedLocale',
                'paymentHostedCheckout.customerTimezone',
                'paymentHostedCheckout.customerUserAgent',
                'paymentHostedCheckout.customerColorDepth',
                'paymentHostedCheckout.customerJavaEnabled',
                'paymentHostedCheckout.customerScreenHeight',
                'paymentHostedCheckout.customerScreenWidth',
                'paymentHostedCheckout.customerBrowserLocale',
                'paymentHostedCheckout.customerTimezoneOffset',
            ],
            WorldlineConfig::PAYMENT_METHOD_CREDIT_CARD_MASTER_CARD => [
                'paymentHostedCheckout.returnUrl',
                'paymentHostedCheckout.customerIpAddress',
                'paymentHostedCheckout.customerSelectedLocale',
                'paymentHostedCheckout.customerTimezone',
                'paymentHostedCheckout.customerUserAgent',
                'paymentHostedCheckout.customerColorDepth',
                'paymentHostedCheckout.customerJavaEnabled',
                'paymentHostedCheckout.customerScreenHeight',
                'paymentHostedCheckout.customerScreenWidth',
                'paymentHostedCheckout.customerBrowserLocale',
                'paymentHostedCheckout.customerTimezoneOffset',
            ],
            WorldlineConfig::PAYMENT_METHOD_NAME_CREDIT_CARD_MASTER_CARD => [
                'paymentHostedCheckout.returnUrl',
                'paymentHostedCheckout.customerIpAddress',
                'paymentHostedCheckout.customerSelectedLocale',
                'paymentHostedCheckout.customerTimezone',
                'paymentHostedCheckout.customerUserAgent',
                'paymentHostedCheckout.customerColorDepth',
                'paymentHostedCheckout.customerJavaEnabled',
                'paymentHostedCheckout.customerScreenHeight',
                'paymentHostedCheckout.customerScreenWidth',
                'paymentHostedCheckout.customerBrowserLocale',
                'paymentHostedCheckout.customerTimezoneOffset',
            ],
            WorldlineConfig::PAYMENT_METHOD_CREDIT_CARD_AMERICAN_EXPRESS => [
                'paymentHostedCheckout.returnUrl',
                'paymentHostedCheckout.customerIpAddress',
                'paymentHostedCheckout.customerSelectedLocale',
                'paymentHostedCheckout.customerTimezone',
                'paymentHostedCheckout.customerUserAgent',
                'paymentHostedCheckout.customerColorDepth',
                'paymentHostedCheckout.customerJavaEnabled',
                'paymentHostedCheckout.customerScreenHeight',
                'paymentHostedCheckout.customerScreenWidth',
                'paymentHostedCheckout.customerBrowserLocale',
                'paymentHostedCheckout.customerTimezoneOffset',
            ],
            WorldlineConfig::PAYMENT_METHOD_NAME_CREDIT_CARD_AMERICAN_EXPRESS => [
                'paymentHostedCheckout.returnUrl',
                'paymentHostedCheckout.customerIpAddress',
                'paymentHostedCheckout.customerSelectedLocale',
                'paymentHostedCheckout.customerTimezone',
                'paymentHostedCheckout.customerUserAgent',
                'paymentHostedCheckout.customerColorDepth',
                'paymentHostedCheckout.customerJavaEnabled',
                'paymentHostedCheckout.customerScreenHeight',
                'paymentHostedCheckout.customerScreenWidth',
                'paymentHostedCheckout.customerBrowserLocale',
                'paymentHostedCheckout.customerTimezoneOffset',
            ],
            WorldlineConfig::PAYMENT_METHOD_PAYPAL => [
                'paymentHostedCheckout.returnUrl',
                'paymentHostedCheckout.customerIpAddress',
                'paymentHostedCheckout.customerSelectedLocale',
                'paymentHostedCheckout.customerTimezone',
                'paymentHostedCheckout.customerUserAgent',
                'paymentHostedCheckout.customerColorDepth',
                'paymentHostedCheckout.customerJavaEnabled',
                'paymentHostedCheckout.customerScreenHeight',
                'paymentHostedCheckout.customerScreenWidth',
                'paymentHostedCheckout.customerBrowserLocale',
                'paymentHostedCheckout.customerTimezoneOffset',
            ],
        ],
    ];

###### 10. Add house keeping job to jenkins cofiguration

In jenkins.php add

    // ----------------------------------------------------------------------------
    // ---------------------- Worldline Housekeeping ------------------------------
    // ----------------------------------------------------------------------------
    
    $jobs[] = [
        'name' => 'delete-worldline-tokens-marked-for-deletion',
        'command' => '$PHP_BIN vendor/bin/console worldline:token:remove-deleted',
        'schedule' => '*/2 * 1-3 * *',
        'enable' => true,
        'stores' => ['DE'],
    ];

###### 11. integrate token table in customer view in backoffice

Add WorldlineQueryContainer to CustomerDependencyProvider

In CustomerDependencyProvider add constants and method addWorldlineQueryContainer

    public const FACADE_PAYMENT = 'FACADE_PAYMENT';

    public const QUERY_CONTAINER_WORLDLINE = 'QUERY_CONTAINER_WORLDLINE';

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    private function addWorldlineQueryContainer(Container $container): Container
    {
        $container->set(
            static::QUERY_CONTAINER_WORLDLINE,
            fn (Container $container): WorldlineQueryContainerInterface => $container->getLocator()->worldline()->queryContainer(),
        );

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
     private function addPaymentFacade(Container $container): Container
     {
        $container->set(
            static::FACADE_PAYMENT,
            fn (Container $container): PaymentFacadeInterface => $container->getLocator()->payment()->facade(),
        );

          return $container;
      }

And extend provideCommunicationLayerDependencies

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    public function provideCommunicationLayerDependencies(Container $container): Container
    {
        $container = parent::provideCommunicationLayerDependencies($container);
        .        
        .
        $container = $this->addPaymentFacade($container);
        $container = $this->addWorldlineQueryContainer($container);

        return $container;
    }

In CustomerCommunicationFactory add createTokenTable, getPaymentFacade and getWorldlineQueryContainer

    /**
     * @param int $idCustomer
     *
     * @return \Pyz\Zed\Customer\Communication\Table\TokenTable
     */
    public function createTokenTable(int $idCustomer): TokenTable
    {
        return new TokenTable($this->getPaymentFacade(), $idCustomer, $this->getWorldlineQueryContainer());
    }

    /**
     * @return \Pyz\Zed\Worldline\Persistence\WorldlineQueryContainerInterface
     */
    private function getWorldlineQueryContainer(): WorldlineQueryContainerInterface
    {
        return $this->getProvidedDependency(CustomerDependencyProvider::QUERY_CONTAINER_WORLDLINE);
    }

    /**
    * @return \Pyz\Zed\Payment\Business\PaymentFacadeInterface
    */
    private function getPaymentFacade(): PaymentFacadeInterface
    {
        return $this->getProvidedDependency(CustomerDependencyProvider::FACADE_PAYMENT);
    }

Add the creation of the token table to Customer/Communication/Controller/ViewController 

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|array
     */
    public function indexAction(Request $request): RedirectResponse|array
    {
        $viewResponse = parent::indexAction($request);
        if ($viewResponse instanceof RedirectResponse) {
            return $viewResponse;
        }

        if (is_array($viewResponse)) {
            $idCustomer = $this->castId($request->get(CustomerConstants::PARAM_ID_CUSTOMER));
            $tokenTable = $this->getFactory()->createTokenTable($idCustomer);
            $viewResponse['tokenTable'] = $tokenTable->render();
        }

        return $viewResponse;
    }

and Customer/Presentation/View/index.twig

    .
    .
    {% embed '@Gui/Partials/widget.twig' with { widget_title: 'Payment Tokens' } %}
        {% block widget_content %}
            {{ tokenTable | raw }}
        {% endblock %}
    {% endembed %}

###### 12. Register WorldlineTokenConsole in \Pyz\Zed\Console\ConsoleDependencyProvider

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return array<\Symfony\Component\Console\Command\Command>
     */
    protected function getConsoleCommands(Container $container): array
    {
        $commands = [
            .
            .
            new WorldlineTokenConsole(),
        ];

###### 13. Add oms commands and conditions to \Pyz\Zed\Oms\OmsDependencyProvider

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function extendCommandPlugins(Container $container): Container
    {
        $container->extend(self::COMMAND_PLUGINS, function (CommandCollectionInterface $commandCollection) {
            .
            .

            // ----- Worldline
            $commandCollection->add(new CreateHostedCheckoutCommandPlugin(), 'Worldline/HostedCheckoutCreate');
            $commandCollection->add(new GetHostedCheckoutStatusCommandPlugin(), 'Worldline/HostedCheckoutStatus');
            $commandCollection->add(new GetPaymentStatusCommandPlugin(), 'Worldline/PaymentStatus');
            $commandCollection->add(new PlaceholderCommandPlugin(), 'Worldline/Cancel');
            $commandCollection->add(new ApprovePaymentCommandPlugin(), 'Worldline/Capture');
            $commandCollection->add(new PlaceholderCommandPlugin(), 'Worldline/Refund');

            .
            .

            return $commandCollection;
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function extendConditionPlugins(Container $container): Container
    {
        $container->extend(self::CONDITION_PLUGINS, function (ConditionCollectionInterface $conditionCollection) {
            .
            .

            // ----- Worldline
            $conditionCollection->add(new WorldlineIsHostedCheckoutCreatedConditionPlugin(), 'Worldline/IsHostedCheckoutCreated');
            $conditionCollection->add(new WorldlineIsHostedCheckoutFailedConditionPlugin(), 'Worldline/IsHostedCheckoutFailed');
            $conditionCollection->add(new WorldlineIsHostedCheckoutPaymentCreatedConditionPlugin(), 'Worldline/IsHostedCheckoutStatusReceived');
            $conditionCollection->add(new WorldlineIsHostedCheckoutStatusCancelledConditionPlugin(), 'Worldline/IsHostedCheckoutStatusCancelled');
            $conditionCollection->add(new WorldlineIsHostedCheckoutTimedOutConditionPlugin(), 'Worldline/IsHostedCheckoutTimedOut');
            $conditionCollection->add(new WorldlineIsPaymentGuaranteedConditionPlugin(), 'Worldline/IsPaymentGuaranteed');
            $conditionCollection->add(new WorldlineIsPaymentCancelledConditionPlugin(), 'Worldline/IsPaymentCancelled');
            $conditionCollection->add(new WorldlineIsPaymentRejectedConditionPlugin(), 'Worldline/IsPaymentRejected');
            $conditionCollection->add(new FalseConditionPlugin(), 'Worldline/IsCapturablePaymentCancelled');
            $conditionCollection->add(new FalseConditionPlugin(), 'Worldline/IsCancellationReceived');
            $conditionCollection->add(new WorldlineIsCaptureTimedOutConditionPlugin(), 'Worldline/IsCaptureTimedOut');
            $conditionCollection->add(new WorldlineIsCapturedConditionPlugin(), 'Worldline/IsCaptured');
            $conditionCollection->add(new WorldlineIsCaptureRejectedConditionPlugin(), 'Worldline/IsCaptureRejected');
            $conditionCollection->add(new FalseConditionPlugin(), 'Worldline/IsRefunded');
            $conditionCollection->add(new FalseConditionPlugin(), 'Worldline/IsRefundFailed');

            .
            .

            return $conditionCollection;
        });

        return $container;
    }

#### Payment Process

##### Hosted Checkout

Add 

### Setup database schema

