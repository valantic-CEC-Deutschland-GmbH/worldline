# Integrating Worldline PSP into Spryker project
This file describes what needs to be done in order to integrate the Worldline PSP into a Spryker project.

## Origin
The implementation was part of a project for one of valantic's customers and was extracted after the decision was made not to integrate the Worldline PSP in the Spryker shop but using the customers own ERP system and an existing custom build payment server.

To make sure we can integrate the PSP into Spryker again at a later stage the code was extracted into this package to make sure the efforts that went into the implementation were not wasted.

## Overview of modules
### ZED
This chapter describes Worldline-specific or -modified ZED-modules.

#### Worldline
##### Name
Worldline

##### Dependencies
ingenico-epayments/connect-sdk-php (Managed by composer)



##### Purpose
* General wrapper for native Worldline SDK calling their REST API endpoints

  * Supported API calls: See WorldlineFacadeInterface.php

* Implement OMS conditions and commands to advance state machine and/or execute requests to  Worldline

* Process events enqueued in the WorldlineWebhook-module

* Special mentions

  *   Business:
      * Mappers for mapping Spryker transfer objects to Worldline API-call DTOs
 
      * ApiCallLoggers: Prepended to each API-call to log relevant API-call data like payload to database (for support reasons)

  * Communication:

    * has a CheckoutDoSaveOrderPlugin (native Spryker) to a) add order reference to the payment DB entry and b) create according payment detail DB entries 
    * has a PaymentMethodFilterPlugin (native Spryker) to remove Worldline payment methods configured in Spryker but not provided by connected Worldline environment 
    * has a WorldlineApiLoggerPluginInterface (custom) for integration of aforementioned APICallLoggers 
    * has a WorldlineQueueMessageProcessorPluginInterface-implementation to process events enqueued in the WorldlineWebhook module 
  * OMS 
    * Condition:
      * Conditions for advancing state machine state; can be divided into categories for:
        * HostedCheckout status 
        * Payment status 
      * Command:
        * Commands to execute Worldline requests; can be divided into categories:
          * HostedCheckout (creation, status request)
          * Payment (status request)
          * Capture (creation)
          * Cancel (creation)
  * Persistence 
    * see Data model

#### WorldlineWebhook
##### Name
WorldlineWebhook

##### Dependencies
Webhook module for processing the webhook events (saved in the worldline_webhook.events queue).

##### Purpose
* Provide an endpoint for Worldline's webhook events (see Webhooks documentation )
* Give fast response to endpoint calls and enqueue events to a queue to be processed later on

##### Special mentions
* Design decision: Implements the required REST endpoint in ZED instead of in GLUE.  Reasons:
  * Easier usage of queues from within ZED than from GLUE
  * Similar to integration was made for FirstSpirit CMS (= when CMS calls Spyker via REST)
* Register the QueueMessageProcessorPlugin in WorldlineWebhookDependencyProvider.getWebhookQueueProcessorPlugin 
* Uses Plugin concept to handle different kinds of calls to the endpoint; currently two plugins registered 
  * WorldlineWebhookQueueWriterEventListenerPlugin:  Writes the posted webhooks events into a queue 
  * WorldlineWebhookGetRequestEventListenerPlugin: Answers verification get requests with the proper get request header value in the payload.

### GLUE
This chapter describes Worldline-specific or -modified GLUE-modules

#### PaymentTokensRestApi
##### Name
PaymentTokensRestApi

##### Purpose
* Provides an endpoint to get a list of available payment tokens for a specific customer so the customer can check what credit cards he/she has already saved and maybe delete the 
* Provides an endpoint to delete a specific token for the logged in customer

##### Special mentions
* Accessible under the customers endpoint (e.g. /customers/DE08990001/payment-tokens)
* Http verbs available: GET and DELETE

#### PaymentsRestApi
##### Name
PaymentsRestApi

##### Purpose
* Stores config regarding mandatory fields (Sprkyer’s paymentMethods)

#### CheckoutRestApi
##### Name
CheckoutRestApi

##### Purpose
* Stores config regarding mandatory fields (checkout)


### Shared
This chapter describes Worldline-specific modules specified as Shared.

#### Worldline
##### Name
Worldline

##### Purpose
* For the Spryker-typical specification of auto-generated transfer-objects 
* Stores variable and property names;
for examples of the latter see [Configuration](##Configuration)


## Overview of data model
Data Model for Payments with Worldline.

Currently we are planning to support only credit card and paypal payments via Worldline and those as so called hosted checkouts. To create a hosted checkout and to support the hosted checkout process one needs specific information that needs to be stored in the data base.

Tables

|Field Name                                            |Data Type        |Table                                        |Description                                                                                                                                                                                                                                                                                                                                                |
|------------------------------------------------------|-----------------|---------------------------------------------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
|General Information (payment)                         |                 |                                             |                                                                                                                                                                                                                                                                                                                                                           |
|id_payment_worldline                                  |int              |vsy_payment_worldline                        |Primary key                                                                                                                                                                                                                                                                                                                                                |
|fk_sales_order                                        |int              |vsy_payment_worldline                        |Foreign key to the Spryker order associated with this Worldline payment                                                                                                                                                                                                                                                                                    |
|payment_id                                            |varchar(255)     |vsy_payment_worldline                        |The payment id as provided by Worldline                                                                                                                                                                                                                                                                                                                    |
|type                                                  |varchar(255)     |vsy_payment_worldline                        |The payment_method used in the current payment                                                                                                                                                                                                                                                                                                             |
|payment_method                                        |varchar(255)     |vsy_payment_worldline                        |The type of payment currently always hosted checkout                                                                                                                                                                                                                                                                                                       |
|merchant_reference                                    |varchar(255)     |vsy_payment_worldline                        |The order reference that is passed to Worldline as the merchantReference                                                                                                                                                                                                                                                                                   |
|created_at                                            |timestamp        |vsy_payment_worldline                        |Timestamp (creation time)                                                                                                                                                                                                                                                                                                                                  |
|updated_at                                            |timestamp        |vsy_payment_worldline                        |Timestamp (last update time)                                                                                                                                                                                                                                                                                                                               |
|Hosted Checkout Specific                              |                 |                                             |                                                                                                                                                                                                                                                                                                                                                           |
|fk_payment_worldline                                  | int             | vsy_payment_worldline_hosted_checkout       |Foreign key to the Worldline payment entry                                                                                                                                                                                                                                                                                                                 |
|hosted_checkout_id                                    | varchar(255)    | vsy_payment_worldline_hosted_checkout       |Worldline’s id für this hosted checkout                                                                                                                                                                                                                                                                                                                    |
|returnmac                                             | varchar(255)    | vsy_payment_worldline_hosted_checkout       |A checksum the hosted checkout will provide when returning back to the client given return-url                                                                                                                                                                                                                                                             |
|partial_redirect_url                                  | varchar(2000)   | vsy_payment_worldline_hosted_checkout       |Partial URL to the Worldline-generated hosted checkout page                                                                                                                                                                                                                                                                                                |
|return_url                                            | varchar(2048)   | vsy_payment_worldline_hosted_checkout       |URL given by the client; the hosted checkout page will return to this URL                                                                                                                                                                                                                                                                                  |
|customer_ip_address                                   | varchar(255)    | vsy_payment_worldline_hosted_checkout       |Customer device information                                                                                                                                                                                                                                                                                                                                |
|customer_selected_locale                              | varchar(255)    | vsy_payment_worldline_hosted_checkout       |Customer device information                                                                                                                                                                                                                                                                                                                                |
|customer_timezone                                     | varchar(255)    | vsy_payment_worldline_hosted_checkout       |Customer device information                                                                                                                                                                                                                                                                                                                                |
|customer_user_agent                                   | varchar(255)    | vsy_payment_worldline_hosted_checkout       |Customer device information                                                                                                                                                                                                                                                                                                                                |
|customer_color_depth                                  | int             | vsy_payment_worldline_hosted_checkout       |Customer device information                                                                                                                                                                                                                                                                                                                                |
|customer_java_enabled                                 | tinyint(1)      | vsy_payment_worldline_hosted_checkout       |Customer device information                                                                                                                                                                                                                                                                                                                                |
|customer_screen_height                                | varchar(6)      | vsy_payment_worldline_hosted_checkout       |Customer device information                                                                                                                                                                                                                                                                                                                                |
|customer_screen_width                                 | varchar(6)      | vsy_payment_worldline_hosted_checkout       |Customer device information                                                                                                                                                                                                                                                                                                                                |
|customer_browser_locale                               | varchar(255)    | vsy_payment_worldline_hosted_checkout       |Customer device information                                                                                                                                                                                                                                                                                                                                |
|customer_timezone_offset                              | int             | vsy_payment_worldline_hosted_checkout       |Customer device information                                                                                                                                                                                                                                                                                                                                |
|created_at                                            | timestamp       | vsy_payment_worldline_hosted_checkout       |Timestamp (this data set’s creation time)                                                                                                                                                                                                                                                                                                                  |
|updated_at                                            | timestamp       | vsy_payment_worldline_hosted_checkout       |Timestamp (this data set’s last update time)                                                                                                                                                                                                                                                                                                               |
|Payment Worldline Transaction Status Log              |                 |                                             |                                                                                                                                                                                                                                                                                                                                                           |
|id_payment_worldline_transaction_status_log           |int              |vsy_payment_worldline_transaction_status_log |Primary key                                                                                                                                                                                                                                                                                                                                                |
|fk_payment_worldline                                  |int              |vsy_payment_worldline_transaction_status_log |Foreign key to the Worldline payment entry                                                                                                                                                                                                                                                                                                                 |
|fk_worldline_api_log                                  |int              |vsy_payment_worldline_transaction_status_log |Foreign key for a corresponding API log entry if status change was reported by API call response                                                                                                                                                                                                                                                           |
|fk_worldline_rest_log                                 |int              |vsy_payment_worldline_transaction_status_log |Foreign key for a corresponding rest log entry if status change was reported by webhook event                                                                                                                                                                                                                                                              |
|transaction_type                                      |varchar(255)     |vsy_payment_worldline_transaction_status_log |Transaction type either “payment”, “refund” or “token”                                                                                                                                                                                                                                                                                                     |
|status                                                |varchar(255)     |vsy_payment_worldline_transaction_status_log |Status reported; usually same values as used by Worldline e. g. CREATED, REJECTED, REDIRECTED, PAYMENT_CREATED, PENDING_APPROVAL. But there are some status values which do not have a Worldline equivalent, but are chosen by us e. g. GET_HOSTED_CHECKOUT_STATUS_FAILED, GET_PAYMENT_FAILED, etc.                                                        |
|status_category                                       |varchar(255)     |vsy_payment_worldline_transaction_status_log |Worldline-category of status,  e.g. UNSUCCESSFUL, PENDING_MERCHANT, COMPLETED, etc.                                                                                                                                                                                                                                                                        |
|status_code                                           |int              |vsy_payment_worldline_transaction_status_log |Worldline’s legacy status code that can hold important information for special cases, e. g. 150 = Timeout, payment failed and will not recover.                                                                                                                                                                                                            |
|status_code_change_date_time                          |varchar          |vsy_payment_worldline_transaction_status_log |The date time when the status changed, so one knows which is the most current entry even if events and API calls might not be registered in their original order                                                                                                                                                                                           |
|authorized                                            |tinyint(1) (bool)|vsy_payment_worldline_transaction_status_log |Flag that states if payment has been authorized (= authorization exists)                                                                                                                                                                                                                                                                                   |
|cancellable                                           |tinyint(1) (bool)|vsy_payment_worldline_transaction_status_log |Flag that states if payment (= authorization) is cancellable                                                                                                                                                                                                                                                                                               |
|refundable                                            |tinyint(1) (bool)|vsy_payment_worldline_transaction_status_log |Flag that states if payment is refundable                                                                                                                                                                                                                                                                                                                  |
|amount                                                |int              |vsy_payment_worldline_transaction_status_log |Amount of money to be paid (in cents)                                                                                                                                                                                                                                                                                                                      |
|created_at                                            | timestamp       | vsy_payment_worldline_transaction_status_log|Timestamp (this data set’s creation time)                                                                                                                                                                                                                                                                                                                  |
|updated_at                                            | timestamp       | vsy_payment_worldline_transaction_status_log|Timestamp (this data set’s last update time)                                                                                                                                                                                                                                                                                                               |
|3D Secure Result Data (vsy_worldline_3d_secure_result)|                 |                                             |                                                                                                                                                                                                                                                                                                                                                           |
|id_3d_secure_result                                   |int              |vsy_worldline_3d_secure_result               |The primary key of the table                                                                                                                                                                                                                                                                                                                               |
|acs_transaction_id                                    |varchar(255)     |vsy_worldline_3d_secure_result               |Identifier of the authenticated transaction at the ACS/Issuer as returned for the payment by worldline                                                                                                                                                                                                                                                     |
|method                                                |varchar(255)     |vsy_worldline_3d_secure_result               |Method of authentication used for this transaction. Possible values (According to Worldline API v1.0 Reference, Jan. 2023): frictionless = The authentication went without a challenge challenged = Cardholder was challenged avs-verified = The authentication was verified by AVS other = Another issuer method was used to authenticate this transaction|
|utctimestamp                                          |varchar(255)     |vsy_worldline_3d_secure_result               |Timestamp in UTC (YYYYMMDDHHmm) of the 3-D Secure authentication of this transaction. Column name is not utc_timestamp because that is the name of a function in the DB which would create problems.                                                                                                                                                       |
|created_at                                            |timestamp        |vsy_worldline_3d_secure_result               |Automatically on creation of the data set generated timestamp .                                                                                                                                                                                                                                                                                            |
|updated_at                                            |timestamp        |vsy_worldline_3d_secure_result               |Automatically on updates of the data set generated timestamp.                                                                                                                                                                                                                                                                                              |
|Worldline Payment Token                               |                 |                                             |                                                                                                                                                                                                                                                                                                                                                           |
|id_token                                              |int              |vsy_worldline_token                          |primary key of the table                                                                                                                                                                                                                                                                                                                                   |
|fk_customer                                           |int              |vsy_worldline_token                          |Foreign key of the customer table to link the token to a specific customer                                                                                                                                                                                                                                                                                 |
|fk_initial_three_d_secure_result                      |int              |vsy_worldline_token                          |Foreign key of the related initial 3d Secure result data set                                                                                                                                                                                                                                                                                               |
|payment_method_key                                    |varchar(255)     |vsy_worldline_token                          |key of the payment method used in payment                                                                                                                                                                                                                                                                                                                  |
|token                                                 |varchar(255)     |vsy_worldline_token                          |the external token id given by Worldline                                                                                                                                                                                                                                                                                                                   |
|expiry_month                                          |varchar(4)       |vsy_worldline_token                          |Month (Format: YYMM) after which the card will expire                                                                                                                                                                                                                                                                                                      |
|obfuscated_card_number                                |varchar(19)      |vsy_worldline_token                          |As the name says the obfuscated card number leaving only 4 readable digits at the end.                                                                                                                                                                                                                                                                     |
|holder_name                                           |varchar(255)     |vsy_worldline_token                          |The name of the card holder                                                                                                                                                                                                                                                                                                                                |
|initial_scheme_transaction_id                         |varchar(255)     |vsy_worldline_token                          |The unique scheme transactionId of the initial transaction that was performed with SCA. As returned by Worldline.                                                                                                                                                                                                                                          |
|expired_at                                            |timestamp        |vsy_worldline_token                          |The timestamp of when the token was marked as expired.                                                                                                                                                                                                                                                                                                     |
|deleted_at                                            |timestamp        |vsy_worldline_token                          |The timestamp of when the token was marked as deleted.                                                                                                                                                                                                                                                                                                     |
|created_at                                            |timestamp        |vsy_worldline_token                          |Automatically on creation of the data set generated timestamp .                                                                                                                                                                                                                                                                                            |
|updated_at                                            |timestamp        |vsy_worldline_token                          |Automatically on updates of the data set generated timestamp.                                                                                                                                                                                                                                                                                              |

Logging of Api Calls and Webhook events

|Field Name                    |Data Type    |Table                         |Description                                                                                                              |
|------------------------------|-------------|------------------------------|-------------------------------------------------------------------------------------------------------------------------|
|Api Calls                     |             |                              |                                                                                                                         |
|vsy_worldline_api_call_log    |             |                              |                                                                                                                         |
|id_worldline_api_call_log     |int          |vsy_worldline_api_call_log    |Primary key                                                                                                              |
|url                           |varchar(2048)|vsy_worldline_api_call_log    |URL of the API enpoint called at Worldline                                                                               |
|request_id                    |varchar(255) |vsy_worldline_api_call_log    |SDK internal request id for mapping response to request                                                                  |
|request_body                  |text         |vsy_worldline_api_call_log    |Entire request body                                                                                                      |
|response_body                 |text         |vsy_worldline_api_call_log    |Entire response body                                                                                                     |
|error_code                    |varchar(255) |vsy_worldline_api_call_log    |Error code from the first error encountered in the API-response                                                          |
|error_message                 |text         |vsy_worldline_api_call_log    |Error message from the first error encountered in the API-response                                                       |
|vsy_worldline_api_log         |             |                              |                                                                                                                         |
|id_worldline_api_log          |int          |vsy_worldline_api_log         |Primary key                                                                                                              |
|fk_payment_worldline          |int          |vsy_worldline_api_log         |Foreign key to the Worldline payment entry                                                                               |
|api_method                    |varchar(255) |vsy_worldline_api_log         |The name of the API method called in snake case in upper case                                                            |
|merchant_id                   |varchar(255) |vsy_worldline_api_log         |The merchant identifier used for the API call (like 6511 for Germany)                                                    |
|api_key                       |varchar(255) |vsy_worldline_api_log         |API-key used to communicate with Worldline during the API-call                                                           |
|api_endpoint                  |varchar(255) |vsy_worldline_api_log         |The API endpoint called, allows one to see if preprod or prod was called                                                 |
|order_reference               |varchar(255) |vsy_worldline_api_log         |The order reference of the order for which the API was called                                                            |
|payment_id                    |varchar(255) |vsy_worldline_api_log         |Worldline's unique identifier for the ongoing payment                                                                    |
|error_id                      |varchar(255) |vsy_worldline_api_log         |Error-id from the first error encountered in the API-response                                                            |
|error_code                    |varchar(255) |vsy_worldline_api_log         |Error code from the first error encountered in the API-response                                                          |
|error_property_name           |varchar(255) |vsy_worldline_api_log         |Denotes the faulty property; taken from the first error encountered in the API-response                                  |
|error_message                 |varchar(255) |vsy_worldline_api_log         |Message from the first error encountered in the API-response                                                             |
|http_status_code              |int          |vsy_worldline_api_log         |The http status code returned (in case of an error, else 200)                                                            |
|psp_status_code               |int          |vsy_worldline_api_log         |currently unused                                                                                                         |
|created_at                    |timestamp    |vsy_worldline_api_log         |Creation timestamp for the data set                                                                                      |
|updated_at                    |timestamp    |vsy_worldline_api_log         |Timestamp for the latest update to this data set                                                                         |
|Webhook Events                |             |                              |                                                                                                                         |
|vsy_worldline_rest_receive_log|             |                              |                                                                                                                         |
|id_worldline_rest_receive_log |int          |vsy_worldline_rest_receive_log|Primary key                                                                                                              |
|url                           |varchar(255) |vsy_worldline_rest_receive_log|currently unused                                                                                                         |
|event_id                      |varchar(255) |vsy_worldline_rest_receive_log|Uuid of the worldline webhook event Used to make sure we don’t have to handle the same event multiple times              |
|event_body                    |text         |vsy_worldline_rest_receive_log|The body of the POST request                                                                                             |
|created_at                    |timestamp    |vsy_worldline_rest_receive_log|Creation timestamp for this data set                                                                                     |
|updated_at                    |timestamp    |vsy_worldline_rest_receive_log|Timestamp for the latest update to this data set                                                                         |
|vsy_worldline_rest_log        |             |                              |                                                                                                                         |
|id_worldline_rest_log         |int          |vsy_worldline_rest_log        |Primary key                                                                                                              |
|fk_payment_worldline          |int          |vsy_worldline_rest_log        |Foreign key to the Worldline payment entry                                                                               |
|rest_endpoint                 |varchar(255) |vsy_worldline_rest_log        |currently unused                                                                                                         |
|merchant_id                   |varchar(255) |vsy_worldline_rest_log        |The merchant identifier used for the API call (like 6511 for Germany)                                                    |
|event_api_version             |varchar(255) |vsy_worldline_rest_log        |API version for the event                                                                                                |
|event_creation_date           |varchar(255) |vsy_worldline_rest_log        |Time of creation of the event as Worldline                                                                               |
|event_id                      |varchar(255) |vsy_worldline_rest_log        |Uuid of the worldline webhook event                                                                                      |
|event_type                    |varchar(255) |vsy_worldline_rest_log        |Transaction type and status separated by a decimal point and in lower snake case (e. g. payment.created or token.deleted)|
|error_code                    |varchar(255) |vsy_worldline_rest_log        |First error_code in the event body                                                                                       |
|error_message                 |varchar(255) |vsy_worldline_rest_log        |First error message found in event body                                                                                  |
|fk_worldline_rest_receive_log |int          |vsy_worldline_rest_log        |Foreign key to the Worldline rest receive log entry                                                                      |
|created_at                    |timestamp    |vsy_worldline_rest_log        |Creation timestamp for this data set                                                                                     |
|updated_at                    |timestamp    |vsy_worldline_rest_log        |Timestamp for the latest update to this data set                                                                         |


## Configuration

### Spryker-based
For configuring features and properties of Spryker regarding Worldline payments.

#### Properties
Worldline configuration properties are stored in Spryker’s $config-array. It is populated in [PROJECT_ROOT]\config\shared\config_default.php; for example $config[WorldlineConstants::WORLDLINE_API_ENDPOINT] = 'https://world.preprod.api-ingenico.com'

Note:

* property-names are stored in WorldlineConstants which is part of ValanticSpryker\Shared\Worldline 
* config_default.php can be overridden by other files (for example config_default-docker.dev.php for the DEV-environment).

##### Available configurations for Worldline PSP integration:

      $config[WorldlineConstants::WORLDLINE_API_KEY_ID]
    $config[WorldlineConstants::WORLDLINE_API_SECRET]
    $config[WorldlineConstants::WORLDLINE_API_ENDPOINT]
    $config[WorldlineConstants::WORLDLINE_API_INTEGRATOR]
    $config[WorldlineConstants::WORLDLINE_API_LR_MERCHANT_ID]
    $config[WorldlineConstants::WORLDLINE_API_COUNTRY_ISO2CODE]
    $config[WorldlineConstants::WORLDLINE_WEBHOOK_KEY]
    $config[WorldlineConstants::WORLDLINE_WEBHOOK_SECRET]
    $config[WorldlineConstants::WORLDLINE_SECRETS_KEY_STORE]
    $config[WorldlineConstants::CONNECT_TIMEOUT_GET_HOSTED_CHECKOUT_STATUS]
    $config[WorldlineConstants::READ_TIMEOUT_GET_HOSTED_CHECKOUT_STATUS]
    $config[WorldlineConstants::WORLDLINE_TIMEZONE]
    $config[WorldlineConstants::WORLDLINE_IS_DEVELOPMENT_WITHOUT_WORLDLINE_BACKEND]
    $config[WorldlineConstants::WORLDLINE_MAX_HOURS_TO_WAIT_BEFORE_CAPTURE_TIMES_OUT]
    $config[WorldlineConstants::WORLDLINE_LIMIT_OF_DELETED_TOKENS_TO_REMOVE_AT_A_TIME]
    $config[WorldlineWebhookConstants::WORLDLINE_WEBHOOK_ENABLED]
    $config[WorldlineWebhookConstants::WORLDLINE_WEBHOOK_DEBUG_ENABLED]

#### State machine (OMS)
Spryker uses XML-files to configure events, transitions and commands for a state machine. Those files are stored under [PROJECT_ROOT]\config\ZED\oms:

* Base-state machine (at the time of writing Jan. 2023): DelayedPayment01.xml 
* Sub-state machines: Everything under [PROJECT_ROOT]\config\ZED\oms\WorldlinePaymentSubprocess



Scheduled tasks (jobs)
Scheduled tasks are configurable in the file [PROJECT_ROOT]/config/Zed/cronjobs/jenkins.php

##### Example:

Task to delete tokens should run on the first three days of a month every 2 minutes:

    $jobs[] = [
        'name' => 'delete-worldline-tokens-that-have-been-marked-for-deletion',
        'command' => '$PHP_BIN vendor/bin/console worldline:token:remove-deleted',
        'schedule' => '*/2 * 1-3 * *',
        'enable' => true,
        'stores' => ['DE'],
    ];

The schedule is a default Unix CRON expression (here: “*/2” stands for every 2 minutes every hour; “* 1-3 * *” is for the first 3 days in a months every month any day of the week)

### Worldline-based
For configuring features and properties on Worldline’s side.

#### Online-configurable
One can use the online tool “Worldline Configuration Cockpit” to configure certain settings. This is mainly:

Webhook endpoints and what events they shall receive

Keys and secrets (for API calls and webhook events)

Look and Feel (customization of the hosted checkout UI)


| Environment | URL to Configuration Cockpit                  |
|-------------|-----------------------------------------------|
| DEV/STG | https://preprod.account.ingenico.com/dashboard |
| PROD | https://account.ingenico.com/dashboard |


### Worldline-exclusive
Certain settings (like delayed settlement) can only be configured by Worldline themselves. For this, one has to create a support request via email to merchantservices@worldline.com

