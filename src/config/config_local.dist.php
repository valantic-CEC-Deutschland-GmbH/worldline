<?php

declare(strict_types = 1);

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

// ----------------------------------------------------------------------------
// ------------------------------ OMS -----------------------------------------
// ----------------------------------------------------------------------------

$config[OmsConstants::PROCESS_LOCATION] = [
    OmsConfig::DEFAULT_PROCESS_LOCATION,
];
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

// ----------------------------------------------------------------------------
// ----------------------- Worldline Connector --------------------------------
// ----------------------------------------------------------------------------
$config[WorldlineConstants::WORLDLINE_API_KEY_ID] = getenv('WORLDLINE_API_KEY_ID') ?: '';
$config[WorldlineConstants::WORLDLINE_API_SECRET] = getenv('WORLDLINE_API_SECRET') ?: '';
$config[WorldlineConstants::WORLDLINE_API_ENDPOINT] = getenv('WORLDLINE_API_ENDPOINT') ?: '';
$config[WorldlineConstants::WORLDLINE_API_INTEGRATOR] = 'valantic CEC Deutschland GmbH';
$config[WorldlineConstants::WORLDLINE_API_MERCHANT_ID] = getenv('WORLDLINE_API_MERCHANT_ID') ?: ''; // Test merchant ID (Germany) 6511
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
