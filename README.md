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

      use ValanticSpryker\Shared\Worldline\WorldlineConstants;
      use ValanticSpryker\Zed\Worldline\WorldlineConfig;
      
      $config[QueueConstants::QUEUE_ADAPTER_CONFIGURATION] = [
         EventConstants::EVENT_QUEUE => [
             QueueConfig::CONFIG_QUEUE_ADAPTER => RabbitMqAdapter::class,
            QueueConfig::CONFIG_MAX_WORKER_NUMBER => 5,
         ],
         WorldlineWebhookConstants::WORLDLINE_WEBHOOK_EVENT_QUEUE_NAME => [
            QueueConfig::CONFIG_QUEUE_ADAPTER => RabbitMqAdapter::class,
            QueueConfig::CONFIG_MAX_WORKER_NUMBER => 1,
         ],
      ];

###### 2. Add OMS Mappings (Example)
      
      // ----------------------------------------------------------------------------
      // ------------------------------ OMS -----------------------------------------
      // ----------------------------------------------------------------------------

      $config[OmsConstants::ACTIVE_PROCESSES] = [
         'DelayedPayment01',
         'Payment01',
         'NoPayment01',
      ];
      
      $config[SalesConstants::PAYMENT_METHOD_STATEMACHINE_MAPPING] = [
         WorldlineConfig::PAYMENT_METHOD_CREDIT_CARD_VISA => 'DelayedPayment01',
         WorldlineConfig::PAYMENT_METHOD_CREDIT_CARD_MASTER_CARD => 'DelayedPayment01',
         WorldlineConfig::PAYMENT_METHOD_CREDIT_CARD_AMERICAN_EXPRESS => 'DelayedPayment01',
         WorldlineConfig::PAYMENT_METHOD_PAYPAL => 'Payment01',
         CheckoutRestApiConfig::LR_INTERNAL_PAYMENT_METHOD_CASH_ON_DELIVERY => 'NoPayment01',
         CheckoutRestApiConfig::LR_INTERNAL_PAYMENT_METHOD_INVOICE => 'NoPayment01',
      ];
      
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

###### 8. Register Queue Procesor Plugin

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

#### Payment Process

##### Hosted Checkout

Add 

### Setup database schema

