<?php

declare(strict_types = 1);

namespace ValanticSpryker\Shared\Worldline;

interface WorldlineConstants
{
    /**
     * @var string
     */
    public const WORLDLINE_API_KEY_ID = 'WORLDLINE_API_KEY_ID'; // the API key (can be obtained from the Configuration Center)

    /**
     * @var string
     */
    public const WORLDLINE_API_SECRET = 'WORLDLINE_API_SECRET'; // the API secret (can be obtained from the Configuration Center)

    /**
     * @var string
     */
    public const WORLDLINE_API_ENDPOINT = 'WORLDLINE_API_ENDPOINT'; //  the API endpoint URI including scheme. See API endpoints for the possible values

    /**
     * @var string
     */
    public const WORLDLINE_API_INTEGRATOR = 'WORLDLINE_API_INTEGRATOR'; // the name of the integrator, e.g. your company name

    /**
     * @var string
     */
    public const WORLDLINE_API_MERCHANT_ID = 'WORLDLINE_API_MERCHANT_ID';

    /**
     * @var string
     */
    public const WORLDLINE_API_COUNTRY_ISO2CODE = 'WORLDLINE_API_COUNTRY_ISO2CODE'; // the country for which the payment products need tobe available

    /**
     * @var string
     */
    public const WORLDLINE_API_METHOD_CREATE_HOSTED_CHECKOUT = 'CREATE_HOSTED_CHECKOUT';

    /**
     * @var string
     */
    public const STATUS_HOSTED_CHECKOUT_CREATED = 'HOSTED_CHECKOUT_CREATED';

    /**
     * @var string
     */
    public const STATUS_HOSTED_CHECKOUT_FAILED = 'HOSTED_CHECKOUT_FAILED';

    /**
     * @var string
     */
    public const STATUS_HOSTED_CHECKOUT_STATUS_PENDING = 'IN_PROGRESS';

    /**
     * @var string
     */
    public const STATUS_HOSTED_CHECKOUT_STATUS_PAYMENT_CREATED = 'PAYMENT_CREATED';

    /**
     * @var string
     */
    public const STATUS_HOSTED_CHECKOUT_STATUS_PAYMENT_CANCELLED_CLIENT_NOT_ELIGIBLE = 'CLIENT_NOT_ELIGIBLE_FOR_SELECTED_PAYMENT_PRODUCT';

    /**
     * @var string
     */
    public const STATUS_HOSTED_CHECKOUT_STATUS_PAYMENT_CANCELLED_BY_CONSUMER = 'CANCELLED_BY_CONSUMER';

    /**
     * @var string
     */
    public const STATUS_PAYMENT_PENDING_APPROVAL = 'PENDING_APPROVAL';

    /**
     * @var string
     */
    public const STATUS_CANCELLED = 'CANCELLED';

    /**
     * @var string
     */
    public const STATUS_REDIRECTED = 'REDIRECTED';

    /**
     * @var string
     */
    public const STATUS_CREATED = 'CREATED';

    /**
     * @var string
     */
    public const STATUS_REJECTED = 'REJECTED';

    /**
     * @var string
     */
    public const STATUS_PENDING_PAYMENT = 'PENDING_PAYMENT';

    /**
     * @var string
     */
    public const STATUS_PENDING_CAPTURE = 'PENDING_CAPTURE';

    /**
     * @var string
     */
    public const STATUS_CAPTURED = 'CAPTURED';

    /**
     * @var string
     */
    public const STATUS_CAPTURE_REQUESTED = 'CAPTURE_REQUESTED';

    /**
     * @var string
     */
    public const STATUS_PAID = 'PAID';

    /**
     * @var string
     */
    public const STATUS_REJECTED_CAPTURE = 'REJECTED_CAPTURE';

    /**
     * @var string
     */
    public const STATUS_CATEGORY_UNSUCCESSFUL = 'UNSUCCESSFUL';

    /**
     * @var string
     */
    public const STATUS_CATEGORY_PENDING_MERCHANT = 'PENDING_MERCHANT';

    /**
     * @var string
     */
    public const STATUS_CATEGORY_PENDING_CONNECT_OR_3RD_PARTY = 'PENDING_CONNECT_OR_3RD_PARTY';

    /**
     * @var string
     */
    public const STATUS_CATEGORY_COMPLETED = 'COMPLETED';

    /**
     * @var string
     */
    public const WORLDLINE_API_METHOD_GET_PAYMENT_PRODUCTS = 'GET_PAYMENT_PRODUCTS';

    /**
     * @var string
     */
    public const WORLDLINE_API_METHOD_GET_HOSTED_CHECKOUT_STATUS = 'GET_HOSTED_CHECKOUT_STATUS';

    /**
     * @var string
     */
    public const WORLDLINE_API_METHOD_GET_PAYMENT = 'GET_PAYMENT';

    /**
     * @var string
     */
    public const WORLDLINE_API_METHOD_APPROVE_PAYMENT = 'APPROVE_PAYMENT';

    /**
     * @var string
     */
    public const HOSTED_CHECKOUT_COMPLETION_STATUS_PENDING = 'PENDING';

    /**
     * @var string
     */
    public const HOSTED_CHECKOUT_COMPLETION_STATUS_UNSUCCESSFUL = 'UNSUCCESSFUL';

    /**
     * @var string
     */
    public const HOSTED_CHECKOUT_COMPLETION_STATUS_SUCCESSFUL = 'SUCCESSFUL';

    /**
     * @var string
     */
    public const CONNECT_TIMEOUT = 'CONNECT_TIMEOUT_GET_HOSTED_CHECKOUT_STATUS';

    /**
     * @var string
     */
    public const READ_TIMEOUT = 'READ_TIMEOUT_GET_HOSTED_CHECKOUT_STATUS';

    /**
     * @var string
     */
    public const WORLDLINE_SECRETS_KEY_STORE = 'WORLDLINE_SECRETS_KEY_STORE';

    /**
     * @var string
     */
    public const WORLDLINE_WEBHOOK_KEY = 'WORLDLINE_WEBHOOK_KEY';

    /**
     * @var string
     */
    public const WORLDLINE_WEBHOOK_SECRET = 'WORLDLINE_WEBHOOK_SECRET';

    /**
     * @var string
     */
    public const WORLDLINE_IS_DEVELOPMENT_WITHOUT_WORLDLINE_BACKEND = 'WORLDLINE_IS_DEVELOPMENT_WITHOUT_WORLDLINE_BACKEND';

    /**
     * @var string
     */
    public const WORLDLINE_PAYMENT_TYPE_HOSTED_CHECKOUT = 'HOSTED_CHECKOUT';

    /**
     * @var string
     */
    public const TRANSACTION_STATUS_GET_HOSTED_CHECKOUT_STATUS_FAILED = 'GET_HOSTED_CHECKOUT_STATUS_FAILED';

    /**
     * @var string
     */
    public const TRANSACTION_STATUS_GET_PAYMENT_STATUS_FAILED = 'GET_PAYMENT_STATUS_FAILED';

    /**
     * @var string
     */
    public const TRANSACTION_STATUS_APPROVE_PAYMENT_STATUS_FAILED = 'APPROVE_PAYMENT_FAILED';

    /**
     * @var string
     */
    public const WORLDLINE_TIMEZONE = 'WORLDLINE_TIMEZONE';

    /**
     * @var string
     */
    public const WORLDLINE_MAX_HOURS_TO_WAIT_BEFORE_CAPTURE_TIMES_OUT = 'WORLDLINE_MAX_HOURS_TO_WAIT_BEFORE_CAPTURE_TIMES_OUT';

    /**
     * @var string
     */
    public const WORLDLINE_LIMIT_OF_DELETED_TOKENS_TO_REMOVE_AT_A_TIME = 'WORLDLINE_LIMIT_OF_DELETED_TOKENS_TO_REMOVE_AT_A_TIME';

    /**
     * @var string
     */
    public const WORLDLINE_API_METHOD_DELETE_TOKEN = 'DELETE_TOKEN';
}
